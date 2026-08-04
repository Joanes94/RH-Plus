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
        ])->orderBy('nom')->get();

        return view('centres.index', compact('centres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:200',
            'code'      => 'required|string|max:30|unique:centres,code',
            'email'     => 'nullable|email|max:150',
            'adresse'   => 'nullable|string|max:300',
            'telephone' => 'nullable|string|max:30',
            'a_drh_dedie' => 'boolean',
        ]);

        Centre::create($validated);

        return back()->with('success', 'Centre créé avec succès.');
    }

    public function update(Request $request, Centre $centre)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:200',
            'email'     => 'nullable|email|max:150',
            'adresse'   => 'nullable|string|max:300',
            'telephone' => 'nullable|string|max:30',
            'a_drh_dedie' => 'boolean',
        ]);

        $centre->update($validated);

        return back()->with('success', 'Centre mis à jour avec succès.');
    }
}
