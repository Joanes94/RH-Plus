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
            return back()->with('success', "Vérification faite : aucun avancement n'est dû aujourd'hui pour {$personnel->nom_complet}. (Rappel : le prochain avancement d'échelon se calcule à partir de la date de début de contrat, ou de la date « Échelon en vigueur depuis le » si elle est renseignée.)");
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
        $signUrl   = null;
        $signataireNom = null;
        $signataireTitre = null;

        if ($avancement->statut === 'valide') {
            $docService = new \App\Services\DocumentService();

            // Signature DDIS spécifique du valideur
            $signPath = ConfigRh::get('ddis_signature_path', null, $validePar)
                ?: ($validePar?->signature_path ?: ConfigRh::get('ddis_signature_path', null));
            $signUrl  = $docService->imageToBase64($signPath);

            // Nom du valideur DDIS
            $signataireNom = ConfigRh::get('ddis_nom', null, $validePar)
                ?: ($validePar?->nom_complet ?: ConfigRh::get('ddis_nom', 'Signataire DDIS'));

            // Titre DDIS
            $signataireTitre = ConfigRh::get('ddis_titre', null, $validePar)
                ?: ConfigRh::get('ddis_titre', 'Pour la Direction Diocésaine de la Santé (DDIS)');
        }

        $data = [
            'avancement'        => $avancement,
            'personnel'         => $avancement->personnel,
            'centre'            => $avancement->personnel?->centre,
            'contrat'           => $contrat,
            'organisation'      => 'Direction Diocésaine de la Santé',
            'ville'             => ConfigRh::get('ville', 'Cotonou', $validePar),
            'signataire_nom'    => $signataireNom,
            'signataire_titre'  => $signataireTitre,
            'drh_nom'           => $signataireNom,
            'directeur_diocesain_nom' => ConfigRh::get('ddis_directeur_nom', 'Abbé Paul HESSOU', $validePar),
            'signature_url'     => $signUrl,
            'valide_par_ddis'   => $validePar,
        ];

        if ($avancement->type === 'bonification') {
            $vue = $request->query('doc') === 'directeur' ? 'avancements.document_bonification_directeur' : 'avancements.document_bonification_employe';
            return view($vue, $data);
        }

        return view('avancements.document_echelon', $data);
    }

    /** Liste des avancements (soumis/en attente de validation par DDIS). */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        // Calcul des totaux KPI globaux
        $countSoumis = Avancement::where('statut', 'soumis')->count();
        $countValide = Avancement::where('statut', 'valide')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count();
        $countRejete = Avancement::where('statut', 'rejete')->count();

        $query = Avancement::with('personnel.centre')->orderByDesc('date_effet');

        // Filtrer par centre
        if ($request->filled('centre_id')) {
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

        // Filtrer par statut (soumis/valide/rejete)
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            // Par défaut, afficher les demandes à valider
            $query->where('statut', 'soumis');
        }

        $avancements = $query->paginate(20)->withQueryString();
        $centres     = \App\Models\Centre::where('actif', true)->orderBy('ordre')->get();

        return view('avancements.index', compact('avancements', 'centres', 'countSoumis', 'countValide', 'countRejete'));
    }

    /** Approuver un avancement d'échelon ou une bonification (DDIS uniquement). */
    public function approuver(Avancement $avancement, AvancementService $service)
    {
        $this->authorizeDDIS();

        if ($avancement->type === 'bonification') {
            $service->approuverBonification($avancement, auth()->user());
        } else {
            $service->approuverEchelon($avancement, auth()->user());
        }

        $libelle = $avancement->type === 'bonification' ? 'La bonification' : "L'avancement d'échelon";
        return back()->with('success', "{$libelle} pour {$avancement->personnel->nom_complet} a été validé(e) et appliqué(e) officiellement par la DDIS.");
    }

    /** Rejeter un avancement d'échelon ou une bonification (DDIS uniquement). */
    public function rejeter(Avancement $avancement, AvancementService $service)
    {
        $this->authorizeDDIS();

        if ($avancement->type === 'bonification') {
            $service->rejeterBonification($avancement, auth()->user());
        } else {
            $service->rejeterEchelon($avancement, auth()->user());
        }

        $libelle = $avancement->type === 'bonification' ? 'La bonification' : "L'avancement d'échelon";
        return back()->with('success', "{$libelle} pour {$avancement->personnel->nom_complet} a été rejeté(e).");
    }

    private function authorizeAccess()
    {
        $user = auth()->user();
        if (!$user->isGlobal()) {
            abort(403, "Accès interdit.");
        }
    }

    private function authorizeDDIS()
    {
        $user = auth()->user();
        if (!$user->isDDIS()) {
            abort(403, "Seul le DDIS peut valider les avancements et bonifications.");
        }
    }
}