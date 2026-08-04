{{-- resources/views/evaluations/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Nouvelle évaluation')
@section('page-title', 'Nouvelle évaluation')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('stagiaires.show', $stagiaire) }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour au stagiaire
    </a>
</div>

@if($errors->any())
<div class="alert alert-error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
    {{ $errors->count() }} erreur(s) à corriger.
</div>
@endif

<form method="POST" action="{{ route('evaluations.store') }}" class="evaluation-form">
    @csrf
    <input type="hidden" name="stagiaire_id" value="{{ $stagiaire->id }}">

    <div class="dash-card">
        <div class="card-header">
            <h3>Fiche d'évaluation - {{ $stagiaire->nom_complet }}</h3>
        </div>

        <div class="form-grid">
            {{-- Qualités --}}
            <div class="form-group fg-2">
                <label>I- Qualités</label>
                <div class="input-wrapper">
                    <textarea name="qualites" rows="5" class="textarea-std">{{ old('qualites') }}</textarea>
                </div>
                @error('qualites') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- Défauts --}}
            <div class="form-group fg-2">
                <label>II- Défauts</label>
                <div class="input-wrapper">
                    <textarea name="defauts" rows="5" class="textarea-std">{{ old('defauts') }}</textarea>
                </div>
                @error('defauts') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- Maîtrise de la pratique --}}
            <div class="form-group fg-2">
                <label>III- Maîtrise de la pratique</label>
                <div class="input-wrapper">
                    <textarea name="maitrise_pratique" rows="5" class="textarea-std">{{ old('maitrise_pratique') }}</textarea>
                </div>
                @error('maitrise_pratique') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- Appréciation personnelle --}}
            <div class="form-group fg-2">
                <label>IV- Appréciation personnelle</label>
                <div class="input-wrapper">
                    <textarea name="appreciation_personnelle" rows="5" class="textarea-std">{{ old('appreciation_personnelle') }}</textarea>
                </div>
                @error('appreciation_personnelle') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <a href="{{ route('stagiaires.show', $stagiaire) }}" class="btn-ghost">Annuler</a>
        <button type="submit" name="action" value="brouillon" class="btn-ghost">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Sauvegarder le brouillon
        </button>
        <button type="submit" name="action" value="soumettre" class="btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Soumettre au DRH
        </button>
    </div>
</div>
</form>

<style>
    .evaluation-form .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .evaluation-form .fg-2 {
        grid-column: span 2;
    }

    .textarea-std {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid var(--col-border-lg);
        border-radius: var(--radius);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        resize: vertical;
        min-height: 100px;
        background: var(--col-bg);
        transition: border-color 0.2s;
    }

    .textarea-std:focus {
        border-color: var(--col-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    @media (max-width: 768px) {
        .evaluation-form .form-grid {
            grid-template-columns: 1fr;
        }
        .evaluation-form .fg-2 {
            grid-column: span 1;
        }
    }
</style>
@endsection