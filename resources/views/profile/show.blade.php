@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')

<div class="profile-layout">
    {{-- Card info principale --}}
    <div class="dash-card profile-card">
        <div class="profile-hero">
            <div class="profile-avatar-lg">
                {{ strtoupper(substr($user->prenoms, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}
            </div>
            <div class="profile-hero-info">
                <h2>{{ $user->prenoms }} {{ strtoupper($user->nom) }}</h2>
                <span class="role-tag">{{ $user->role === 'drh' ? 'Directeur RH' : 'Assistant RH' }}</span>
                <span class="sexe-tag">{{ $user->sexe === 'M' ? 'Homme' : 'Femme' }}</span>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-row">
                <span class="detail-key">Email</span>
                <span class="detail-val">{{ $user->email }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Téléphone</span>
                <span class="detail-val">{{ $user->telephone ?: '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Membre depuis</span>
                <span class="detail-val">{{ $user->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="profile-actions">
            <a href="{{ route('profile.edit') }}" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Modifier mes informations
            </a>
        </div>
    </div>

    {{-- Modifier mot de passe --}}
    <div class="dash-card">
        <div class="card-header"><h3>Changer le mot de passe</h3></div>
        @if($errors->has('current_password'))
            <div class="alert alert-error">{{ $errors->first('current_password') }}</div>
        @endif
        <form method="POST" action="{{ route('profile.password') }}" class="profile-form">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Mot de passe actuel</label>
                <div class="input-wrapper">
                    <input type="password" name="current_password" id="curr_pw" placeholder="••••••••">
                    <button type="button" class="toggle-password" data-target="curr_pw">
                        <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>
            <div class="form-row-2col">
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="new_pw" placeholder="••••••••">
                        <button type="button" class="toggle-password" data-target="new_pw">
                            <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Confirmation</label>
                    <div class="input-wrapper">
                        <input type="password" name="password_confirmation" id="conf_pw" placeholder="••••••••">
                        <button type="button" class="toggle-password" data-target="conf_pw">
                            <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-primary">Mettre à jour le mot de passe</button>
        </form>
    </div>

    {{-- Zone danger --}}
    <div class="dash-card danger-zone">
        <div class="card-header"><h3 class="danger-title">Zone de danger</h3></div>
        <p class="danger-text">La suppression de votre compte est irréversible. Toutes vos données seront effacées définitivement.</p>
        <form method="POST" action="{{ route('profile.destroy') }}"
              onsubmit="return confirm('Êtes-vous sûr ? Cette action est irréversible.')">
            @csrf
            @method('DELETE')
            <div class="form-group" style="max-width:300px">
                <label>Confirmer avec votre mot de passe</label>
                <div class="input-wrapper">
                    <input type="password" name="password" placeholder="Votre mot de passe">
                </div>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="btn-danger">Supprimer mon compte</button>
        </form>
    </div>
</div>

@endsection
