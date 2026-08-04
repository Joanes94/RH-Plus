<?php

namespace App\Http\Controllers;

use App\Models\ConfigRh;
use App\Models\JourFerie;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfigRhController extends Controller
{
    public function __construct(private DocumentService $doc) {}

    public function index()
    {
        $annee   = request('annee', date('Y'));
        $feries  = JourFerie::where('annee', $annee)->orderBy('date')->get();
        $config  = [
            'drh_nom'         => ConfigRh::get('drh_nom', ''),
            'drh_titre'       => ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines'),
            'organisation'    => ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'           => ConfigRh::get('ville', 'Cotonou'),
            'signature_path'  => ConfigRh::get('drh_signature_path'),
        ];

        $joursFixesSuggeres = JourFerie::joursFixesBenin((int)$annee);

        return view('config_rh.index', compact('config', 'feries', 'annee', 'joursFixesSuggeres'));
    }

    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'drh_nom'      => 'required|string|max:150',
            'drh_titre'    => 'required|string|max:200',
            'organisation' => 'nullable|string|max:200',
            'ville'        => 'nullable|string|max:100',
        ]);

        foreach ($data as $cle => $valeur) {
            ConfigRh::set($cle, $valeur);
        }

        // Upload image de signature
        if ($request->hasFile('signature')) {
            $request->validate(['signature' => 'image|max:2048|mimes:png,jpg,jpeg']);

            // Supprimer l'ancienne signature si elle existe
            $oldPath = ConfigRh::get('drh_signature_path');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->doc->sauvegarderSignature($request->file('signature'));
            ConfigRh::set('drh_signature_path', $path);
        }

        return redirect()->route('config-rh.index')
            ->with('success', 'Configuration enregistrée.');
    }

    /**
     * Enregistre une signature dessinée depuis le pad (data URI base64 → fichier PNG).
     */
    public function saveSignaturePad(Request $request)
    {
        $request->validate([
            'signature_data' => 'required|string', // data:image/png;base64,...
        ]);

        $dataUri = $request->signature_data;

        // Valider que c'est bien une image base64
        if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $dataUri)) {
            return response()->json(['error' => 'Format invalide.'], 422);
        }

        // Extraire le binaire
        $base64  = preg_replace('/^data:image\/\w+;base64,/', '', $dataUri);
        $decoded = base64_decode($base64);

        if (!$decoded) {
            return response()->json(['error' => 'Décodage impossible.'], 422);
        }

        // Supprimer l'ancienne signature
        $oldPath = ConfigRh::get('drh_signature_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Sauvegarder en PNG
        $filename = 'signatures/signature_drh_' . time() . '.png';
        Storage::disk('public')->put($filename, $decoded);

        ConfigRh::set('drh_signature_path', $filename);

        return response()->json([
            'success' => true,
            'message' => 'Signature enregistrée avec succès.',
            'path'    => $filename,
        ]);
    }

    public function storeFerie(Request $request)
    {
        $data = $request->validate([
            'date'    => 'required|date',
            'libelle' => 'required|string|max:150',
            'type'    => 'required|in:fixe,mobile',
            'annee'   => 'required|integer',
        ]);

        JourFerie::updateOrCreate(
            ['date' => $data['date']],
            ['libelle' => $data['libelle'], 'type' => $data['type'], 'annee' => $data['annee']]
        );

        return redirect()->route('config-rh.index', ['annee' => $data['annee']])
            ->with('success', 'Jour férié ajouté.');
    }

    public function destroyFerie(JourFerie $jourFerie)
    {
        $annee = $jourFerie->annee;
        $jourFerie->delete();
        return redirect()->route('config-rh.index', ['annee' => $annee])
            ->with('success', 'Jour férié supprimé.');
    }

    public function importFixesBenin(Request $request)
    {
        $annee = $request->input('annee', date('Y'));
        $jours = JourFerie::joursFixesBenin((int)$annee);
        $count = 0;

        foreach ($jours as $j) {
            if (!JourFerie::where('date', $j['date'])->exists()) {
                JourFerie::create(array_merge($j, ['annee' => $annee]));
                $count++;
            }
        }

        return redirect()->route('config-rh.index', ['annee' => $annee])
            ->with('success', "$count jour(s) férié(s) importé(s) pour $annee.");
    }
}
