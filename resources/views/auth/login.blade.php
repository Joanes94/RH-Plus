@extends('layouts.auth')
@section('title', 'Connexion')

@section('content')
<div class="auth-form-header">
    <h1>Connexion</h1>
    <p>Accédez à votre espace RH Plus</p>
</div>

@if($errors->any())
    <div class="alert alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('login.post') }}" class="auth-form">
    @csrf

    <div class="form-group">
        <label for="email">Adresse email</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="vous@entreprise.com" autocomplete="email" autofocus
                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
        </div>
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" id="password" name="password"
                   placeholder="••••••••" autocomplete="current-password"
                   class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
            <button type="button" class="toggle-password" data-target="password" title="Afficher/Masquer">
                <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
        </div>
        @error('password') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="form-row-between">
        <label class="checkbox-label">
            <input type="checkbox" name="remember">
            <span>Se souvenir de moi</span>
        </label>
        <a href="{{ route('password.forgot') }}" style="font-size:.82rem;color:var(--col-primary)">
            Mot de passe oublié ?
        </a>
    </div>

    <button type="submit" class="btn-primary btn-full">
        Se connecter
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </button>
</form>

<p class="auth-switch">
    Veuillez contacter le Conseiller RH (CRH) de la DDIS pour obtenir un compte d'accès.
</p>
@endsection
