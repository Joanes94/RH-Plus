<?php

namespace App\Http\Controllers;

use App\Models\PayAdjustment;
use App\Models\Personnel;
use App\Models\Centre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayAdjustmentController extends Controller
{
    /**
     * Liste des échéanciers / ajustements actifs ou archivés.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $centres = Centre::where('actif', true)->orderBy('ordre')->get();

        if ($user->isGlobal()) {
            $centreId = $request->get('centre_id') ?: ($centres->first()?->id ?? 0);
        } else {
            $centreId = $user->centre_id;
        }

        $selectedCentre = Centre::find($centreId);
        if (!$selectedCentre) {
            abort(404, 'Centre non trouvé.');
        }

        // Récupérer le personnel de ce centre pour les formulaires
        $personnels = Personnel::where('centre_id', $selectedCentre->id)
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get();

        $adjustments = PayAdjustment::with('personnel')
            ->whereHas('personnel', fn($q) => $q->where('centre_id', $selectedCentre->id))
            ->orderByDesc('created_at')
            ->get();

        return view('pay_adjustments.index', compact('adjustments', 'personnels', 'selectedCentre', 'centres', 'user'));
    }

    /**
     * Enregistre un nouvel échéancier ou plan de prélèvement.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isReadOnly()) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'personnel_id'    => 'required|exists:personnels,id',
            'type'            => 'required|in:avance_salaire,frais_medicaux,moins_percu',
            'libelle'         => 'required|string|max:150',
            'montant_total'   => 'nullable|numeric|min:0',
            'montant_mensuel' => 'nullable|numeric|min:0',
            'mois_restants'   => 'nullable|integer|min:1',
            // Écheancier personnalisé
            'echeances'       => 'nullable|array|min:1',
            'echeances.*.mois'    => 'required_with:echeances|string|regex:/^\d{4}-\d{2}$/',
            'echeances.*.montant' => 'required_with:echeances|numeric|min:0',
        ]);

        $echeances = null;
        $montantMensuel = $validated['montant_mensuel'] ?? 0;
        $moisRestants = $validated['mois_restants'] ?? null;
        $moisDebut = null;

        // Si un écheancier personnalisé est soumis (mode avance/frais médicaux)
        if (!empty($validated['echeances']) && $validated['type'] !== 'moins_percu') {
            $echeances = $validated['echeances'];

            // Trier par mois croissant
            usort($echeances, fn($a, $b) => strcmp($a['mois'], $b['mois']));

            // Vérifier que tous les mois sont >= mois courant
            $moisCourant = date('Y-m');
            foreach ($echeances as $ligne) {
                if ($ligne['mois'] < $moisCourant) {
                    return back()->with('error', 'Impossible de saisir un mois passé dans l\'échéancier.')->withInput();
                }
            }

            // Calculer montant_total si non fourni
            $totalEcheancier = array_sum(array_column($echeances, 'montant'));
            $montantTotal = $validated['montant_total'] ?? $totalEcheancier;

            // Nombre de mois = nombre de lignes
            $moisRestants = count($echeances);
            $moisDebut = $echeances[0]['mois'] ?? $moisCourant;

            // Montant mensuel = premier mois (pour compatibilité)
            $montantMensuel = $echeances[0]['montant'] ?? 0;

        } else {
            // Mode classique (montant fixe)
            if ($validated['type'] !== 'moins_percu') {
                if (empty($validated['montant_total']) || empty($validated['montant_mensuel'])) {
                    return back()->with('error', 'Le montant total et le montant mensuel sont obligatoires.')->withInput();
                }
            }
            $montantTotal = $validated['montant_total'] ?? null;
        }

        PayAdjustment::create([
            'personnel_id'    => $validated['personnel_id'],
            'type'            => $validated['type'],
            'libelle'         => $validated['libelle'],
            'montant_total'   => $montantTotal ?? null,
            'montant_mensuel' => $montantMensuel,
            'mois_restants'   => $moisRestants,
            'echeances'       => $echeances,
            'mois_debut'      => $moisDebut,
            'statut'          => 'actif',
            'created_by'      => Auth::id(),
        ]);

        return back()->with('success', 'Plan d\'ajustement salarial enregistré avec succès.');
    }

    /**
     * Supprime un plan d'ajustement.
     */
    public function destroy(PayAdjustment $payAdjustment)
    {
        $user = Auth::user();
        if ($user->isReadOnly()) {
            abort(403, 'Action non autorisée.');
        }

        $payAdjustment->delete();

        return back()->with('success', 'Plan d\'ajustement salarial supprimé avec succès.');
    }

    /**
     * Active/Désactive ou force le statut d'un plan d'ajustement.
     */
    public function toggleStatus(PayAdjustment $payAdjustment)
    {
        $user = Auth::user();
        if ($user->isReadOnly()) {
            abort(403, 'Action non autorisée.');
        }

        $newStatus = $payAdjustment->statut === 'actif' ? 'termine' : 'actif';
        $payAdjustment->update(['statut' => $newStatus]);

        return back()->with('success', 'Statut du plan mis à jour avec succès.');
    }
}
