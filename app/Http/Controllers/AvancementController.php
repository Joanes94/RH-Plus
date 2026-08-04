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
        $avancement->load('personnel.contrats');
        $contrat = $avancement->contrat ?? $avancement->personnel->contrat_actif;

        // Signature électronique du DRH (même logique que pour les congés/absences).
        $signPath = ConfigRh::get('drh_signature_path');
        $signUrl  = null;
        if ($signPath) {
            $full = Storage::disk('public')->path($signPath);
            if (file_exists($full)) {
                $mime    = mime_content_type($full) ?: 'image/png';
                $signUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
            }
        }

        $data = [
            'avancement'   => $avancement,
            'personnel'    => $avancement->personnel,
            'contrat'      => $contrat,
            'organisation' => ConfigRh::get('organisation', "Direction Diocésaine de la Santé"),
            'ville'        => ConfigRh::get('ville', 'Cotonou'),
            'drh_nom'      => ConfigRh::get('avancement_drh_nom', 'Abbé Wilfried KOUTOUKLOUI'),
            'directeur_diocesain_nom' => ConfigRh::get('avancement_directeur_diocesain_nom', 'Abbé Paul HESSOU'),
            'signature_url' => $signUrl,
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

        $query = Avancement::with('personnel.centre')->orderByDesc('date_effet');

        // Filtrer par type (bonification/echelon)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtrer par statut (soumis/approuve/rejete)
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            // Par défaut, afficher les demandes à valider
            $query->where('statut', 'soumis');
        }

        $avancements = $query->paginate(20);

        return view('avancements.index', compact('avancements'));
    }

    /** Approuver une bonification (DDIS uniquement). */
    public function approuver(Avancement $avancement, AvancementService $service)
    {
        $this->authorizeDDIS();

        $service->approuverBonification($avancement, auth()->user());

        return back()->with('success', "La bonification pour {$avancement->personnel->nom_complet} a été validée et appliquée.");
    }

    /** Rejeter une bonification (DDIS uniquement). */
    public function rejeter(Avancement $avancement, AvancementService $service)
    {
        $this->authorizeDDIS();

        $service->rejeterBonification($avancement, auth()->user());

        return back()->with('success', "La bonification pour {$avancement->personnel->nom_complet} a été rejetée.");
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
            abort(403, "Seul le DDIS peut valider les bonifications.");
        }
    }
}