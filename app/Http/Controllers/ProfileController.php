<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nom'            => 'required|string|max:100',
            'prenoms'        => 'required|string|max:150',
            'sexe'           => 'required|in:M,F',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'telephone'      => 'nullable|string|max:20',
            'titre_officiel' => 'nullable|string|max:200',
            'photo'          => 'nullable|image|max:2048|mimes:jpeg,png,jpg',
            'signature'      => 'nullable|image|max:2048|mimes:jpeg,png,jpg',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('photos/users', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($user->signature_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->signature_path);
            }
            $validated['signature_path'] = $request->file('signature')->store('signatures/users', 'public');
        }

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profil et informations mis à jour avec succès.');
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.confirmed'        => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('profile.show')->with('success', 'Mot de passe modifié avec succès.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Votre compte a été supprimé.');
    }
}
