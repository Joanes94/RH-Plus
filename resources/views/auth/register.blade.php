@extends('layouts.auth')
@section('title', 'Inscription')

@section('content')
<div class="auth-form-header">
    <h1>Créer un compte</h1>
    <p>Rejoignez votre équipe sur RH Plus</p>
</div>

@if($errors->any())
    <div class="alert alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        Veuillez corriger les erreurs ci-dessous.
    </div>
@endif

<form method="POST" action="{{ route('register.post') }}" class="auth-form">
    @csrf

    {{-- Nom & Prénoms --}}
    <div class="form-row-2col">
        <div class="form-group">
            <label for="nom">Nom <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="text" id="nom" name="nom" value="{{ old('nom') }}"
                       placeholder="DUPONT" class="{{ $errors->has('nom') ? 'is-invalid' : '' }}">
            </div>
            @error('nom') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="prenoms">Prénoms <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="text" id="prenoms" name="prenoms" value="{{ old('prenoms') }}"
                       placeholder="Jean-Paul" class="{{ $errors->has('prenoms') ? 'is-invalid' : '' }}">
            </div>
            @error('prenoms') <span class="field-error">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Sexe & Rôle --}}
    <div class="form-row-2col">
        <div class="form-group">
            <label for="sexe">Sexe <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                <select id="sexe" name="sexe" class="{{ $errors->has('sexe') ? 'is-invalid' : '' }}">
                    <option value="">-- Choisir --</option>
                    <option value="M" {{ old('sexe') === 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ old('sexe') === 'F' ? 'selected' : '' }}>Féminin</option>
                </select>
            </div>
            @error('sexe') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="role">Rôle <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                <select id="role" name="role" class="{{ $errors->has('role') ? 'is-invalid' : '' }}">
                    <option value="">-- Choisir --</option>
                    <option value="assistant_rh" {{ old('role') === 'assistant_rh' ? 'selected' : '' }}>Assistant RH</option>
                    <option value="drh" {{ old('role') === 'drh' ? 'selected' : '' }}>Directeur RH (DRH)</option>
                </select>
            </div>
            @error('role') <span class="field-error">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Email --}}
    <div class="form-group">
        <label for="email">Adresse email <span class="req">*</span></label>
        <div class="input-wrapper">
            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="vous@entreprise.com"
                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
        </div>
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    {{-- Téléphone --}}
    <div class="form-group">
        <label for="telephone">Téléphone</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.7 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9a16 16 0 0 0 6.29 6.29l.13-.14a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <input type="tel" id="telephone" name="telephone" value="{{ old('telephone') }}"
                   placeholder="+229 97 00 00 00">
        </div>
    </div>

    {{-- Mot de passe --}}
    <div class="form-row-2col">
        <div class="form-group">
            <label for="password">Mot de passe <span class="req">*</span></label>
            <div class="input-wrapper">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" autocomplete="new-password"
                       class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
                <button type="button" class="toggle-password" data-target="password" title="Afficher/Masquer">
                    <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            @error('password') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirmation <span class="req">*</span></label>
            <div class="input-wrapper">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="••••••••" autocomplete="new-password">
                <button type="button" class="toggle-password" data-target="password_confirmation" title="Afficher/Masquer">
                    <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-primary btn-full">
        Créer mon compte
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </button>
</form>

<p class="auth-switch">
    Déjà un compte ?
    <a href="{{ route('login') }}">Se connecter</a>
</p>
@endsection
