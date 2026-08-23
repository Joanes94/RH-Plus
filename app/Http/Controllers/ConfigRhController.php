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
        $annee  = request('annee', date('Y'));
        $feries = JourFerie::where('annee', $annee)->orderBy('date')->get();
        $user   = auth()->user();

        $config = [
            'drh_nom'             => ConfigRh::get('drh_nom', '', $user),
            'drh_titre'           => ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines', $user),
            'organisation'        => ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines', $user),
            'ville'               => ConfigRh::get('ville', 'Cotonou', $user),
            'signature_path'      => ConfigRh::get('drh_signature_path', null, $user),
            'ddis_nom'            => ConfigRh::get('ddis_nom', ConfigRh::get('drh_nom', '', $user), $user),
            'ddis_titre'          => ConfigRh::get('ddis_titre', 'Directeur Diocésain de la Santé', $user),
            'ddis_directeur_nom'  => ConfigRh::get('ddis_directeur_nom', 'Abbé Paul HESSOU', $user),
            'ddis_signature_path' => ConfigRh::get('ddis_signature_path', ConfigRh::get('drh_signature_path', null, $user), $user),
        ];

        $joursFixesSuggeres = JourFerie::joursFixesBenin((int)$annee);

        return view('config_rh.index', compact('config', 'feries', 'annee', 'joursFixesSuggeres'));
    }

    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'drh_nom'            => 'required|string|max:150',
            'drh_titre'          => 'required|string|max:200',
            'organisation'       => 'nullable|string|max:200',
            'ville'              => 'nullable|string|max:100',
            'ddis_nom'           => 'nullable|string|max:150',
            'ddis_titre'         => 'nullable|string|max:200',
            'ddis_directeur_nom' => 'nullable|string|max:150',
        ]);

        $user = auth()->user();

        foreach ($data as $cle => $valeur) {
            ConfigRh::set($cle, $valeur, $user);
        }

        // Si l'utilisateur est DDIS/Global, synchroniser aussi la clé ddis_nom / ddis_titre
        if ($user->isDDIS() || $user->isGlobal()) {
            ConfigRh::set('ddis_nom', $data['drh_nom'], $user);
            ConfigRh::set('ddis_titre', $data['drh_titre'], $user);
            if (!empty($request->ddis_directeur_nom)) {
                ConfigRh::set('ddis_directeur_nom', $request->ddis_directeur_nom, $user);
            }
        }

        if (isset($data['drh_titre'])) {
            $user->update(['titre_officiel' => $data['drh_titre']]);
        }

        // Upload image de signature
        if ($request->hasFile('signature')) {
            $request->validate(['signature' => 'image|max:2048|mimes:png,jpg,jpeg']);

            $oldPath = ConfigRh::get('drh_signature_path', null, $user);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->doc->sauvegarderSignature($request->file('signature'));
            ConfigRh::set('drh_signature_path', $path, $user);
            ConfigRh::set('ddis_signature_path', $path, $user);
            $user->update(['signature_path' => $path]);
        }

        return redirect()->route('config-rh.index')
            ->with('success', 'Configuration enregistrée avec succès.');
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

        $user = auth()->user();

        // Supprimer l'ancienne signature de CET utilisateur uniquement
        $oldPath = ConfigRh::get('drh_signature_path', null, $user);
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Sauvegarder en PNG
        $path = 'signatures/' . uniqid('sig_pad_') . '.png';
        Storage::disk('public')->put($path, $decoded);

        ConfigRh::set('drh_signature_path', $path, $user);
        ConfigRh::set('ddis_signature_path', $path, $user);
        $user->update(['signature_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Signature enregistrée avec succès.',
            'path'    => $path,
        ]);
    }

    public function storeFerie(Request $request)
    {
        if (!auth()->user()->isCRH()) {
            abort(403, 'Seul le Conseiller RH (CRH) est autorisé à ajouter des jours fériés.');
        }

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
        if (!auth()->user()->isCRH()) {
            abort(403, 'Seul le Conseiller RH (CRH) est autorisé à supprimer des jours fériés.');
        }

        $annee = $jourFerie->annee;
        $jourFerie->delete();
        return redirect()->route('config-rh.index', ['annee' => $annee])
            ->with('success', 'Jour férié supprimé.');
    }

    public function importFixesBenin(Request $request)
    {
        if (!auth()->user()->isCRH()) {
            abort(403, 'Seul le Conseiller RH (CRH) est autorisé à importer des jours fériés.');
        }

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
