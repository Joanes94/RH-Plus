<?php

namespace App\Http\Controllers;

use App\Models\ConfigRh;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfigDdisController extends Controller
{
    public function __construct(private DocumentService $doc) {}

    /**
     * Affiche la page de configuration spécifique à la DDIS.
     */
    public function index()
    {
        $user = auth()->user();

        $config = [
            'ddis_nom'            => ConfigRh::get('ddis_nom', $user->nom_complet, $user),
            'ddis_titre'          => ConfigRh::get('ddis_titre', 'Pour la Direction Diocésaine de la Santé (DDIS)', $user),
            'ddis_directeur_nom'  => ConfigRh::get('ddis_directeur_nom', 'Abbé Paul HESSOU', $user),
            'ddis_signature_path' => ConfigRh::get('ddis_signature_path', $user->signature_path, $user),
        ];

        return view('config_ddis.index', compact('config'));
    }

    /**
     * Enregistre les identifiants et la signature de la DDIS.
     */
    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'ddis_nom'           => 'required|string|max:150',
            'ddis_titre'         => 'required|string|max:200',
            'ddis_directeur_nom' => 'nullable|string|max:150',
        ], [
            'ddis_nom.required'   => 'Le nom du valideur DDIS est obligatoire.',
            'ddis_titre.required' => 'Le titre / entité DDIS est obligatoire.',
        ]);

        $user = auth()->user();

        ConfigRh::set('ddis_nom', $data['ddis_nom'], $user);
        ConfigRh::set('ddis_titre', $data['ddis_titre'], $user);
        if (!empty($data['ddis_directeur_nom'])) {
            ConfigRh::set('ddis_directeur_nom', $data['ddis_directeur_nom'], $user);
        }

        // Upload de l'image de signature DDIS
        if ($request->hasFile('signature')) {
            $request->validate(['signature' => 'image|max:2048|mimes:png,jpg,jpeg']);

            $oldPath = ConfigRh::get('ddis_signature_path', null, $user);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->doc->sauvegarderSignature($request->file('signature'));
            ConfigRh::set('ddis_signature_path', $path, $user);
            $user->update(['signature_path' => $path]);
        }

        return redirect()->route('config-ddis.index')
            ->with('success', 'Configuration DDIS enregistrée avec succès. Ces identifiants et votre signature seront utilisés lors de la validation des avancements d\'échelon et bonifications.');
    }

    /**
     * Enregistre la signature dessinée depuis le pad de dessin pour la DDIS.
     */
    public function saveSignaturePad(Request $request)
    {
        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $dataUri = $request->signature_data;

        if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $dataUri)) {
            return response()->json(['error' => 'Format d\'image invalide.'], 422);
        }

        $base64  = preg_replace('/^data:image\/\w+;base64,/', '', $dataUri);
        $decoded = base64_decode($base64);

        if (!$decoded) {
            return response()->json(['error' => 'Décodage impossible.'], 422);
        }

        $user = auth()->user();

        $oldPath = ConfigRh::get('ddis_signature_path', null, $user);
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = 'signatures/' . uniqid('sig_ddis_pad_') . '.png';
        Storage::disk('public')->put($path, $decoded);

        ConfigRh::set('ddis_signature_path', $path, $user);
        $user->update(['signature_path' => $path]);

        return response()->json([
            'success' => true,
            'path'    => $path,
        ]);
    }
}
