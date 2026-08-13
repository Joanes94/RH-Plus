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
            'montant_mensuel' => 'required|numeric|min:0',
            'mois_restants'   => 'nullable|integer|min:1',
        ]);

        // Pour les moins_percu, montant_total et mois_restants peuvent être nuls (remboursement récurrent sans limite ou temporaire)
        if ($validated['type'] !== 'moins_percu') {
            if (empty($validated['montant_total']) || empty($validated['mois_restants'])) {
                return back()->with('error', 'Le montant total et la durée en mois sont obligatoires pour les avances et frais médicaux.')->withInput();
            }
        }

        PayAdjustment::create([
            'personnel_id'    => $validated['personnel_id'],
            'type'            => $validated['type'],
            'libelle'         => $validated['libelle'],
            'montant_total'   => $validated['montant_total'] ?: null,
            'montant_mensuel' => $validated['montant_mensuel'],
            'mois_restants'   => $validated['mois_restants'] ?: null,
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
