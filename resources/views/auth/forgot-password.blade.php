@extends('layouts.auth')
@section('title', 'Mot de passe oublié')

@section('content')
<div class="auth-form-header">
    <h1>Mot de passe oublié</h1>
    <p>Renseignez votre email pour recevoir un lien de réinitialisation</p>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    {{-- Mode dev : afficher le lien directement --}}
    @if(session('dev_reset_url'))
        <div style="margin-top:10px;padding:12px 14px;background:#f5f4f0;border:1px solid #d4d0c8;border-radius:8px;font-size:.8rem">
            <strong style="color:#1a5c45">Lien de réinitialisation (mode développement) :</strong><br>
            <a href="{{ session('dev_reset_url') }}" style="color:#1a5c45;word-break:break-all;font-size:.78rem">
                {{ session('dev_reset_url') }}
            </a>
        </div>
    @endif
@endif

@if($errors->any())
    <div class="alert alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('password.send') }}" class="auth-form">
    @csrf

    <div class="form-group">
        <label for="email">Adresse email</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="vous@exemple.com" autofocus
                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
        </div>
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <button type="submit" class="btn-primary btn-full">
        Envoyer le lien
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
    </button>
</form>

<p class="auth-switch">
    <a href="{{ route('login') }}">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la connexion
    </a>
</p>
@endsection
