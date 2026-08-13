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
            'ifu'              => 'nullable|string|max:50',
            'numero_cnss'      => 'nullable|string|max:50',
            'adresse'          => 'nullable|string|max:300',
            'telephone'        => 'nullable|string|max:30',
            'a_drh_dedie'      => 'boolean',
            'entete_texte'     => 'nullable|string',
            'pied_page_texte'  => 'nullable|string',
            'reference_suffix' => 'nullable|string|max:100',
            'logo'             => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
            'entete_image'     => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos/centres', 'public');
        }

        if ($request->hasFile('entete_image')) {
            $validated['entete_image_path'] = $request->file('entete_image')->store('entetes/centres', 'public');
        }

        Centre::create($validated);

        return back()->with('success', 'Centre créé avec succès.');
    }

    public function update(Request $request, Centre $centre)
    {
        $validated = $request->validate([
            'nom'              => 'required|string|max:200',
            'email'            => 'nullable|email|max:150',
            'ifu'              => 'nullable|string|max:50',
            'numero_cnss'      => 'nullable|string|max:50',
            'adresse'          => 'nullable|string|max:300',
            'telephone'        => 'nullable|string|max:30',
            'a_drh_dedie'      => 'boolean',
            'entete_texte'     => 'nullable|string',
            'pied_page_texte'  => 'nullable|string',
            'reference_suffix' => 'nullable|string|max:100',
            'logo'             => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
            'entete_image'     => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
        ]);

        if ($request->hasFile('logo')) {
            if ($centre->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($centre->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos/centres', 'public');
        }

        if ($request->hasFile('entete_image')) {
            if ($centre->entete_image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($centre->entete_image_path);
            }
            $validated['entete_image_path'] = $request->file('entete_image')->store('entetes/centres', 'public');
        }

        $centre->update($validated);

        return back()->with('success', 'Centre mis à jour avec succès.');
    }

    public function toggleStatus(Centre $centre)
    {
        $centre->update(['actif' => !$centre->actif]);
        $status = $centre->actif ? 'activé' : 'désactivé';
        return back()->with('success', "Le centre « {$centre->nom} » a été {$status}.");
    }

    public function move(Request $request, Centre $centre, string $direction)
    {
        $allCentres = Centre::orderByRaw('ordre IS NULL, ordre ASC, nom ASC')->get();
        $currentIndex = $allCentres->search(fn($c) => $c->id === $centre->id);

        if ($currentIndex === false) {
            return back();
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if ($targetIndex >= 0 && $targetIndex < $allCentres->count()) {
            $adjacentCentre = $allCentres[$targetIndex];

            // Inverser les ordres
            $tempOrder = $centre->ordre ?: ($currentIndex + 1);
            $adjacentOrder = $adjacentCentre->ordre ?: ($targetIndex + 1);

            if ($tempOrder == $adjacentOrder) {
                $tempOrder = $currentIndex + 1;
                $adjacentOrder = $targetIndex + 1;
            }

            $centre->update(['ordre' => $adjacentOrder]);
            $adjacentCentre->update(['ordre' => $tempOrder]);
        }

        return back()->with('success', "L'ordre du centre « {$centre->nom} » a été mis à jour.");
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
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 200);
        }

        return response()->json(['ok' => true]);
    }
}
