<?php

namespace App\Http\Controllers;

use App\Models\PaySlip;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaySlipController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Affiche le bulletin de paie.
     */
    public function show(PaySlip $paySlip)
    {
        $paySlip->load(['personnel.centre', 'payPeriod']);
        return view('pay_slips.show', compact('paySlip'));
    }

    /**
     * Met à jour les variables et recalcule le bulletin.
     */
    public function update(Request $request, PaySlip $paySlip)
    {
        $user = Auth::user();
        if ($user->isReadOnly() || $paySlip->payPeriod->statut !== 'ouvert') {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'jours_absence'          => 'nullable|integer|min:0|max:30',
            'jours_mise_a_pied'      => 'nullable|integer|min:0|max:30',
            'heures_supplementaires' => 'nullable|numeric|min:0',
            'heures_astreinte'       => 'nullable|numeric|min:0',
            
            'indemnite_logement'     => 'nullable|numeric|min:0',
            'indemnite_transport'    => 'nullable|numeric|min:0',
            'autre_indemnite'        => 'nullable|numeric|min:0',
            'ecart'                  => 'nullable|numeric',

            'prime_caisse'           => 'nullable|numeric|min:0',
            'prime_risque'           => 'nullable|numeric|min:0',
            'prime_responsabilite'   => 'nullable|numeric|min:0',
            'prime_garde'            => 'nullable|numeric|min:0',
            'autre_prime'            => 'nullable|numeric|min:0',

            'trop_percu_brut'        => 'nullable|numeric|min:0',

            'frais_medicaux'         => 'nullable|numeric|min:0',
            'avance_salaire'         => 'nullable|numeric|min:0',
            'trop_percu_net'         => 'nullable|numeric|min:0',
            'moins_percu_rembourse'  => 'nullable|numeric|min:0',

            'banque'                 => 'required|string|max:100',
            'mode_reglement'         => 'required|string|max:50',
            'numero_compte'          => 'nullable|string|max:100',
        ]);

        // Effectuer le recalcul des détails avec les nouvelles variables
        $personnel = $paySlip->personnel;
        $recalculated = $this->payrollService->calculerDetailsFiche(
            $personnel, 
            $paySlip->payPeriod->code, 
            $validated
        );

        $paySlip->update($recalculated);

        return redirect()->route('pay-periods.show', $paySlip->pay_period_id)
            ->with('success', 'Bulletin de paie de ' . $personnel->nom_complet . ' mis à jour et recalculé avec succès.');
    }

    /**
     * Rendu imprimable / PDF du Bulletin de paie individuel.
     */
    public function bulletin(PaySlip $paySlip)
    {
        $paySlip->load(['personnel.centre', 'payPeriod']);
        
        // Encoder les images en base64 pour éviter les blocages de rendu d'impression
        $centre = $paySlip->personnel->centre;
        $logoBase64 = null;
        $enteteBase64 = null;

        if ($centre) {
            if ($centre->logo_path) {
                $logoBase64 = $this->imageToBase64(public_path('storage/' . $centre->logo_path));
            }
            if ($centre->entete_image_path) {
                $enteteBase64 = $this->imageToBase64(public_path('storage/' . $centre->entete_image_path));
            }
        }

        // Logo Diocèse
        $dioceseLogoBase64 = $this->imageToBase64(public_path('images/diocese-logo.png'));
        if (!$dioceseLogoBase64) {
            $dioceseLogoBase64 = $this->imageToBase64(public_path('images/logo.png'));
        }

        return view('pay_slips.bulletin', compact('paySlip', 'logoBase64', 'enteteBase64', 'dioceseLogoBase64'));
    }

    /**
     * Rendu du reçu pour solde de tout compte.
     */
    public function soldeToutCompte(PaySlip $paySlip)
    {
        $paySlip->load(['personnel.centre', 'payPeriod']);
        
        $centre = $paySlip->personnel->centre;
        $logoBase64 = null;
        $enteteBase64 = null;

        if ($centre) {
            if ($centre->logo_path) {
                $logoBase64 = $this->imageToBase64(public_path('storage/' . $centre->logo_path));
            }
            if ($centre->entete_image_path) {
                $enteteBase64 = $this->imageToBase64(public_path('storage/' . $centre->entete_image_path));
            }
        }

        $dioceseLogoBase64 = $this->imageToBase64(public_path('images/diocese-logo.png'));
        if (!$dioceseLogoBase64) {
            $dioceseLogoBase64 = $this->imageToBase64(public_path('images/logo.png'));
        }

        return view('pay_slips.solde_tout_compte', compact('paySlip', 'logoBase64', 'enteteBase64', 'dioceseLogoBase64'));
    }

    /**
     * Convertit une image locale en chaîne base64.
     */
    private function imageToBase64(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}
