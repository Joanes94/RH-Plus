<?php

namespace App\Http\Controllers;

use App\Models\Avancement;
use App\Models\ConfigRh;
use App\Models\Personnel;
use App\Services\AvancementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvancementController extends Controller
{
    /** Déclenche manuellement la vérification des avancements dus (tout le monde). */
    public function verifier(AvancementService $service)
    {
        $resultats = $service->traiterTout();
        $total = $resultats['echelons'] + $resultats['bonifications'];

        $message = $total === 0
            ? "Vérification effectuée : aucun avancement n'était dû aujourd'hui."
            : "Vérification effectuée : {$resultats['echelons']} avancement(s) d'échelon et {$resultats['bonifications']} bonification(s) appliqué(s).";

        return back()->with('success', $message);
    }

    /** Vérifie et applique (si dû) l'avancement/bonification d'un seul agent, à la demande. */
    public function verifierPersonnel(Personnel $personnel, AvancementService $service)
    {
        $personnel->load(['contrats' => fn ($q) => $q->orderByDesc('date_debut'), 'avancements']);
        $contrat = $personnel->contrat_actif;

        if (!$contrat || !$contrat->categorie || !$contrat->echelon) {
            return back()->with('error', "Impossible de vérifier : la catégorie et l'échelon ne sont pas renseignés sur le contrat actif de {$personnel->nom_complet}.");
        }

        $avancement = $service->traiterBonification($personnel, $contrat) ?? $service->traiterEchelon($personnel, $contrat);

        if (!$avancement) {
            return back()->with('success', "Vérification faite : aucun avancement n'est dû aujourd'hui pour {$personnel->nom_complet}. (Rappel : l'avancement d'échelon se calcule à partir de la date d'entrée dans l'ISD, puis tous les 24 mois.)");
        }

        $libelle = $avancement->type === 'bonification' ? 'Bonification (58 ans)' : "Avancement d'échelon";
        return back()->with('success', "{$libelle} appliqué(e) pour {$personnel->nom_complet}. La lettre est disponible dans la section Avancements ci-dessous.");
    }

    /**
     * Document imprimable pour un avancement.
     * - type "echelon"      : une seule lettre (DRH → agent)
     * - type "bonification" : deux lettres, sélectionnées via ?doc=employe|directeur
     */
    public function document(Request $request, Avancement $avancement)
    {
        $avancement->load(['personnel.centre', 'validePar']);
        $contrat = $avancement->contrat ?? $avancement->personnel?->contrat_actif;

        $validePar = $avancement->validePar;
        $docService = new \App\Services\DocumentService();

        if ($avancement->type === 'bonification') {
            // Document Bonification : Signature DDIS obligatoire
            $signPath = ConfigRh::get('ddis_signature_path', null, $validePar)
                ?: ($validePar?->signature_path ?: ConfigRh::get('ddis_signature_path', null));
            $signUrl  = $docService->imageToBase64($signPath);

            $signataireNom = ConfigRh::get('ddis_nom', null, $validePar)
                ?: ($validePar?->nom_complet ?: ConfigRh::get('ddis_nom', 'Signataire DDIS'));

            $signataireTitre = ConfigRh::get('ddis_titre', null, $validePar)
                ?: ConfigRh::get('ddis_titre', 'Pour la Direction Diocésaine de la Santé (DDIS)');

            $data = [
                'avancement'              => $avancement,
                'personnel'               => $avancement->personnel,
                'centre'                  => $avancement->personnel?->centre,
                'contrat'                 => $contrat,
                'organisation'            => 'Direction Diocésaine de la Santé',
                'ville'                   => ConfigRh::get('ville', 'Cotonou', $validePar),
                'signataire_nom'          => $signataireNom,
                'signataire_titre'        => $signataireTitre,
                'drh_nom'                 => $signataireNom,
                'directeur_diocesain_nom' => ConfigRh::get('ddis_directeur_nom', 'Abbé Paul HESSOU', $validePar),
                'signature_url'           => $signUrl,
                'valide_par_ddis'         => $validePar,
            ];

            $vue = $request->query('doc') === 'directeur' ? 'avancements.document_bonification_directeur' : 'avancements.document_bonification_employe';
            return view($vue, $data);
        }

        // Document Avancement d'Échelon : Validé au niveau du centre (DRH / Directeur du centre)
        $drhInfo = $docService->resolveDrhCentre($avancement->personnel, $validePar);

        $data = [
            'avancement'        => $avancement,
            'personnel'         => $avancement->personnel,
            'centre'            => $avancement->personnel?->centre,
            'contrat'           => $contrat,
            'organisation'      => 'Direction Diocésaine de la Santé',
            'ville'             => ConfigRh::get('ville', 'Cotonou', $validePar),
            'signataire_nom'    => $drhInfo['nom'],
            'signataire_titre'  => $drhInfo['titre'],
            'drh_nom'           => $drhInfo['nom'],
            'drh_titre'         => $drhInfo['titre'],
            'signature_url'     => $validePar?->signature_base64 ?: null,
            'valide_par'        => $validePar,
        ];

        return view('avancements.document_echelon', $data);
    }

    /** Liste des avancements & bonifications. */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Calcul des totaux KPI
        $countSoumis = Avancement::whereIn('statut', ['soumis', 'valide_crh'])->count();
        $countValide = Avancement::where('statut', 'valide')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count();
        $countRejete = Avancement::where('statut', 'rejete')->count();

        $query = Avancement::with(['personnel.centre', 'validePar'])->orderByDesc('date_effet');

        // Restreindre au centre si l'utilisateur est restreint
        if (!$user->isGlobal()) {
            $query->whereHas('personnel', function ($q) use ($user) {
                $q->where('centre_id', $user->centre_id);
            });
        } elseif ($request->filled('centre_id')) {
            $query->whereHas('personnel', function ($q) use ($request) {
                $q->where('centre_id', $request->centre_id);
            });
        }

        // Filtrer par mois (format YYYY-MM)
        if ($request->filled('mois')) {
            $parts = explode('-', $request->mois);
            if (count($parts) === 2) {
                $query->whereYear('date_effet', $parts[0])
                      ->whereMonth('date_effet', (int) $parts[1]);
            }
        }

        // Filtrer par type (bonification/echelon)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtrer par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            $query->whereIn('statut', ['soumis', 'valide_crh']);
        }

        $avancements = $query->paginate(20)->withQueryString();
        $centres     = \App\Models\Centre::where('actif', true)->orderBy('ordre')->get();

        return view('avancements.index', compact('avancements', 'centres', 'countSoumis', 'countValide', 'countRejete'));
    }

    /** Étape 1 : Pré-validation de la Bonification par le CRH. */
    public function validerCRH(Avancement $avancement)
    {
        $user = auth()->user();
        if (!$user->isCRH()) {
            abort(403, "Seul le CRH peut effectuer la pré-validation des bonifications.");
        }

        if ($avancement->type !== 'bonification' || $avancement->statut !== 'soumis') {
            return back()->with('error', "Cette demande ne peut pas être pré-validée par le CRH.");
        }

        $avancement->update([
            'statut' => 'valide_crh',
        ]);

        \App\Models\Notification::create([
            'centre_id'         => $avancement->personnel?->centre_id,
            'type'              => 'bonification',
            'titre'             => 'Bonification pré-validée par le CRH — ' . $avancement->personnel->nom_complet,
            'message'           => "La bonification pour {$avancement->personnel->nom_complet} a été pré-validée par le CRH et est maintenant en attente de la signature officielle du DDIS.",
            'personnel_id'      => $avancement->personnel_id,
            'avancement_id'     => $avancement->id,
            'date_notification' => now()->toDateString(),
        ]);

        return back()->with('success', "La bonification pour {$avancement->personnel->nom_complet} a été pré-validée par le CRH et transmise à la DDIS.");
    }

    /** Validation finale : Avancement d'Échelon (par DRH/Directeur du Centre uniquement) OU Bonification (par DDIS après validation CRH). */
    public function approuver(Avancement $avancement, AvancementService $service)
    {
        $user = auth()->user();

        if ($avancement->type === 'bonification') {
            if (!$user->isDDIS()) {
                abort(403, "Seule la DDIS peut imposer sa signature et valider officiellement une bonification.");
            }

            if ($avancement->statut !== 'valide_crh') {
                return back()->with('error', "Avant que la DDIS ne valide la bonification, il faut que le CRH valide d'abord.");
            }

            $service->approuverBonification($avancement, $user);
            return back()->with('success', "La bonification pour {$avancement->personnel->nom_complet} a été validée et signée officiellement par la DDIS.");
        }

        // Pour les avancements d'échelon : Validation par le DRH ou Directeur du Centre concerné, uniquement.
        $personnelCentreId = $avancement->personnel?->centre_id;

        $isDrhDuCentre = ($user->isDRH() || $user->isDirecteurCentre())
            && $user->centre_id === $personnelCentreId;

        if (!$isDrhDuCentre) {
            abort(403, "Seul le DRH ou le Directeur du Centre peut valider un avancement d'échelon.");
        }

        $service->approuverEchelon($avancement, $user);
        return back()->with('success', "L'avancement d'échelon pour {$avancement->personnel->nom_complet} a été validé au niveau du centre.");
    }


    /** Rejeter un avancement d'échelon ou une bonification. */
    public function rejeter(Avancement $avancement, AvancementService $service)
    {
        $user = auth()->user();
        if ($user->isReadOnly()) {
            abort(403, "Action non autorisée.");
        }

        if ($avancement->type === 'bonification') {
            if ($avancement->statut === 'soumis') {
                // Rejet au niveau du CRH, avant transmission à la DDIS.
                if (!$user->isCRH()) {
                    abort(403, "Seul le CRH peut rejeter une bonification à ce stade.");
                }
            } elseif ($avancement->statut === 'valide_crh') {
                // Rejet au niveau de la DDIS, une fois pré-validée par le CRH.
                if (!$user->isDDIS()) {
                    abort(403, "Seule la DDIS peut rejeter une bonification déjà pré-validée par le CRH.");
                }
            } else {
                return back()->with('error', "Cette bonification ne peut plus être rejetée à ce stade.");
            }

            $service->rejeterBonification($avancement, $user);
        } else {
            // Pour les avancements d'échelon : rejet réservé au DRH ou Directeur du Centre concerné.
            // Aucun accès global ici : même un utilisateur "global" doit être DRH/Directeur du centre en question.
            $personnelCentreId = $avancement->personnel?->centre_id;

            $isDrhDuCentre = ($user->isDRH() || $user->isDirecteurCentre())
                && $user->centre_id === $personnelCentreId;

            if (!$isDrhDuCentre) {
                abort(403, "Seul le DRH ou le Directeur du Centre peut rejeter un avancement d'échelon.");
            }
            $service->rejeterEchelon($avancement, $user);
        }

        $libelle = $avancement->type === 'bonification' ? 'La bonification' : "L'avancement d'échelon";
        return back()->with('success', "{$libelle} pour {$avancement->personnel->nom_complet} a été rejeté(e).");
    }
}