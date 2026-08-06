<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use Illuminate\Http\Request;

class CentreController extends Controller
{
    public function index()
    {
        $centres = Centre::withCount([
            'personnels as effectif_actif' => fn($q) => $q->whereNotIn('statut', ['ancien', 'retraite']),
        ])->orderByRaw('ordre IS NULL, ordre ASC, nom ASC')->get();

        return view('centres.index', compact('centres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'              => 'required|string|max:200',
            'code'             => 'required|string|max:30|unique:centres,code',
            'email'            => 'nullable|email|max:150',
            'adresse'          => 'nullable|string|max:300',
            'telephone'        => 'nullable|string|max:30',
            'a_drh_dedie'      => 'boolean',
            'entete_texte'     => 'nullable|string',
            'pied_page_texte'  => 'nullable|string',
            'reference_suffix' => 'nullable|string|max:100',
            'logo'             => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos/centres', 'public');
        }

        Centre::create($validated);

        return back()->with('success', 'Centre créé avec succès.');
    }

    public function update(Request $request, Centre $centre)
    {
        $validated = $request->validate([
            'nom'              => 'required|string|max:200',
            'email'            => 'nullable|email|max:150',
            'adresse'          => 'nullable|string|max:300',
            'telephone'        => 'nullable|string|max:30',
            'a_drh_dedie'      => 'boolean',
            'entete_texte'     => 'nullable|string',
            'pied_page_texte'  => 'nullable|string',
            'reference_suffix' => 'nullable|string|max:100',
            'logo'             => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('logo')) {
            if ($centre->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($centre->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos/centres', 'public');
        }

        $centre->update($validated);

        return back()->with('success', 'Centre mis à jour avec succès.');
    }

    /**
     * Enregistre le nouvel ordre des centres après drag & drop.
     * Envoyé via PATCH /centres/reorder avec { ordre: [id1, id2, ...] }.
     */
    public function reorder(Request $request)
    {
        $request->validate(['ordre' => 'required|array']);

        try {
            foreach ($request->ordre as $position => $id) {
                Centre::where('id', $id)->update(['ordre' => $position + 1]);
            }
        } catch (\Exception $e) {
            // La colonne `ordre` n'existe peut-être pas encore — on l'ignore silencieusement.
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 200);
        }

        return response()->json(['ok' => true]);
    }
}
