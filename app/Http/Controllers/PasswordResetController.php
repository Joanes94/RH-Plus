<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // ── Formulaire "Mot de passe oublié" ──────────────────────────────────────
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ── Envoi du lien de réinitialisation ─────────────────────────────────────
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email'    => 'Adresse email invalide.',
            'email.exists'   => 'Aucun compte trouvé avec cette adresse email.',
        ]);

        // Générer un token sécurisé
        $token = Str::random(64);

        // Supprimer les anciens tokens pour cet email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Enregistrer le nouveau token
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        // Récupérer l'utilisateur pour personnaliser l'email
        $user = User::where('email', $request->email)->first();

        // Construire l'URL de réinitialisation
        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        // Envoyer l'email
        try {
            Mail::send('emails.reset-password', [
                'user'     => $user,
                'resetUrl' => $resetUrl,
                'expire'   => 60, // minutes
            ], function ($message) use ($request, $user) {
                $message->to($request->email, $user->nom_complet)
                        ->subject('Réinitialisation de votre mot de passe — RH Plus');
            });

            return back()->with('success',
                'Un lien de réinitialisation a été envoyé à ' . $request->email . '. Vérifiez votre boîte mail.'
            );

        } catch (\Exception $e) {
            // En développement (Laragon sans SMTP configuré), afficher le lien directement
            if (config('app.debug')) {
                return back()->with('dev_reset_url', $resetUrl)
                             ->with('success', '[MODE DEV] Email non envoyé — utilisez le lien ci-dessous :');
            }

            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email. Contactez l\'administrateur.']);
        }
    }

    // ── Formulaire de nouveau mot de passe ────────────────────────────────────
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    // ── Enregistrer le nouveau mot de passe ───────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.exists'          => 'Aucun compte trouvé avec cette adresse email.',
            'password.confirmed'    => 'Les mots de passe ne correspondent pas.',
            'password.min'          => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // Récupérer l'entrée dans la table
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Ce lien de réinitialisation est invalide.']);
        }

        // Vérifier que le token n'a pas expiré (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Ce lien a expiré. Veuillez faire une nouvelle demande.']);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Ce lien de réinitialisation est invalide.']);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }
}
