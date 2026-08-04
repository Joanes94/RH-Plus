<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ─── Inscription ────────────────────────────────────────────────────────────

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nom'                   => 'required|string|max:100',
            'prenoms'               => 'required|string|max:150',
            'sexe'                  => 'required|in:M,F',
            'email'                 => 'required|email|unique:users,email',
            'telephone'             => 'nullable|string|max:20',
            'role'                  => 'required|in:assistant_rh,drh',
            'password'              => ['required', 'confirmed', Password::min(8)],
        ], [
            'nom.required'          => 'Le nom est obligatoire.',
            'prenoms.required'      => 'Les prénoms sont obligatoires.',
            'sexe.required'         => 'Le sexe est obligatoire.',
            'email.required'        => 'L\'adresse email est obligatoire.',
            'email.unique'          => 'Cette adresse email est déjà utilisée.',
            'role.required'         => 'Le rôle est obligatoire.',
            'password.required'     => 'Le mot de passe est obligatoire.',
            'password.confirmed'    => 'Les mots de passe ne correspondent pas.',
            'password.min'          => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = User::create([
            'nom'       => $request->nom,
            'prenoms'   => $request->prenoms,
            'sexe'      => $request->sexe,
            'email'     => $request->email,
            'telephone' => $request->telephone,
            'role'      => $request->role,
            'password'  => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // ─── Connexion ──────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.email'       => 'Adresse email invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects. Vérifiez votre email et mot de passe.',
        ])->onlyInput('email');
    }

    // ─── Déconnexion ────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
