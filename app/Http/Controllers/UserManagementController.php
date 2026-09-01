<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\User;
use App\Models\UserHistorique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('centre', 'historiques')->orderBy('nom');

        if ($request->filled('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(20);
        $centres = Centre::actifs()->orderBy('nom')->get();

        return view('users.index', compact('users', 'centres'));
    }

    public function create()
    {
        $centres = Centre::actifs()->orderBy('nom')->get();
        return view('users.create', compact('centres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenoms'   => 'required|string|max:255',
            'sexe'      => 'required|in:M,F',
            'email'     => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'role'      => 'required|in:crh,ddis,ddrh,drh_centre,assistant_rh,directeur_centre',
            'centre_id' => 'nullable|exists:centres,id',
            'photo'     => 'nullable|image|max:2048|mimes:jpeg,png,jpg',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        // Les rôles locaux doivent avoir un centre
        if (!in_array($validated['role'], ['crh', 'ddis', 'ddrh']) && empty($validated['centre_id'])) {
            return back()->withErrors(['centre_id' => 'Un centre doit être sélectionné pour ce rôle.'])->withInput();
        }

        // Les rôles globaux ne doivent PAS avoir de centre
        if (in_array($validated['role'], ['crh', 'ddis', 'ddrh'])) {
            $validated['centre_id'] = null;
        }

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('photos/users', 'public');
        }

        $validated['password'] = $validated['password']; // Will be hashed by cast

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $centres = Centre::actifs()->orderBy('nom')->get();
        return view('users.edit', compact('user', 'centres'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenoms'   => 'required|string|max:255',
            'sexe'      => 'required|in:M,F',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'role'      => 'required|in:crh,ddis,ddrh,drh_centre,assistant_rh,directeur_centre',
            'centre_id' => 'nullable|exists:centres,id',
            'photo'     => 'nullable|image|max:2048|mimes:jpeg,png,jpg',
            'password'  => 'nullable|string|min:6|confirmed',
        ]);

        if (!in_array($validated['role'], ['crh', 'ddis', 'ddrh']) && empty($validated['centre_id'])) {
            return back()->withErrors(['centre_id' => 'Un centre doit être sélectionné pour ce rôle.'])->withInput();
        }

        if (in_array($validated['role'], ['crh', 'ddis', 'ddrh'])) {
            $validated['centre_id'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($user->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('photos/users', 'public');
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }


    public function showTransferer(User $user)
    {
        $user->load('centre');
        $centres = Centre::actifs()->orderBy('nom')->get();
        return view('users.transferer', compact('user', 'centres'));
    }

    public function transferer(Request $request, User $user)
    {
        $request->validate([
            'nouveau_centre_id' => 'required|exists:centres,id',
            'date_transfert'    => 'required|date',
            'motif'             => 'nullable|string',
        ]);

        if ((int)$request->nouveau_centre_id === (int)$user->centre_id) {
            return back()->withErrors(['nouveau_centre_id' => 'L\'utilisateur est déjà rattaché à ce centre.'])->withInput();
        }

        $ancienCentre = $user->centre;
        $nouveauCentre = Centre::findOrFail($request->nouveau_centre_id);

        UserHistorique::create([
            'user_id'            => $user->id,
            'ancien_centre_id'   => $user->centre_id,
            'nouveau_centre_id'  => $nouveauCentre->id,
            'ancien_centre_nom'  => $ancienCentre?->nom,
            'nouveau_centre_nom' => $nouveauCentre->nom,
            'role'               => $user->role,
            'date_transfert'     => $request->date_transfert,
            'motif'              => $request->motif,
            'transfere_par'      => auth()->id(),
        ]);

        $user->update(['centre_id' => $nouveauCentre->id]);

        return redirect()->route('users.index')
            ->with('success', "Passation de service effectuée. {$user->nom_complet} est désormais rattaché(e) au centre {$nouveauCentre->nom}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}
