@extends('layouts.app')
@section('title', 'Modifier la demande')
@section('page-title', 'Modifier la demande')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('demandes.show', $demande) }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la demande
    </a>
</div>

@if($errors->any())
<div class="alert alert-error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
    {{ $errors->count() }} erreur(s) à corriger.
</div>
@endif

<div class="conge-form-layout">
<div class="conge-form-main">
<form method="POST" action="{{ route('demandes.update', $demande) }}" id="demandeForm">
    @csrf @method('PUT')
    <input type="hidden" name="type_demande" value="{{ $demande->type_demande }}">
    <input type="hidden" name="personnel_id" value="{{ $demande->personnel_id }}">

    {{-- Info agent + type (non modifiables) --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>{{ $demande->personnel->nom_complet }}</h3>
        </div>
        <p style="font-size:.83rem;color:var(--col-text-2)">{{ $demande->personnel->service ?: '—' }}</p>
        <div style="margin-top:10px">
            <span class="demande-type-tag">{{ $demande->type_label }}</span>
        </div>
    </div>

    @php $champs = \App\Models\Demande::typeChamps($demande->type_demande); @endphp

    {{-- Dates --}}
    @if(in_array('dates', $champs))
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-amber"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div>
            <h3>Période</h3>
        </div>
        <div class="form-grid">
            <div class="form-group fg-3">
                <label>Date de début</label>
                <div class="input-wrapper">
                    <input type="date" name="date_debut" id="dateDeb"
                           value="{{ old('date_debut', $demande->date_debut?->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="form-group fg-3">
                <label>Date de fin</label>
                <div class="input-wrapper">
                    <input type="date" name="date_fin" id="dateFin"
                           value="{{ old('date_fin', $demande->date_fin?->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Motif --}}
    @if(in_array('motif', $champs))
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-teal"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/></svg></div>
            <h3>Motif</h3>
        </div>
        <div class="form-group">
            <div class="input-wrapper">
                <textarea name="motif" rows="3"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('motif', $demande->motif) }}</textarea>
            </div>
        </div>
    </div>
    @endif

    {{-- Date accouchement --}}
    @if(in_array('date_accouchement', $champs))
    <div class="form-section">
        <div class="form-group fg-3">
            <label>Date d'accouchement prévue</label>
            <div class="input-wrapper">
                <input type="date" name="date_accouchement_prevu"
                       value="{{ old('date_accouchement_prevu', $demande->date_accouchement_prevu?->format('Y-m-d')) }}">
            </div>
        </div>
    </div>
    @endif

    {{-- Établissement --}}
    @if(in_array('etablissement', $champs))
    <div class="form-section">
        <div class="form-grid">
            <div class="form-group fg-4">
                <label>Établissement</label>
                <div class="input-wrapper">
                    <input type="text" name="etablissement_stage"
                           value="{{ old('etablissement_stage', $demande->etablissement_stage) }}">
                </div>
            </div>
            <div class="form-group fg-2">
                <label>Niveau d'étude</label>
                <div class="input-wrapper">
                    <input type="text" name="niveau_etude"
                           value="{{ old('niveau_etude', $demande->niveau_etude) }}">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Spécialité --}}
    @if(in_array('specialite', $champs))
    <div class="form-section">
        <div class="form-group fg-4">
            <label>Spécialité / Filière</label>
            <div class="input-wrapper">
                <input type="text" name="specialite"
                       value="{{ old('specialite', $demande->specialite) }}">
            </div>
        </div>
    </div>
    @endif

    {{-- Faits --}}
    @if(in_array('faits', $champs))
    <div class="form-section">
        <div class="form-group">
            <label>Faits reprochés</label>
            <div class="input-wrapper">
                <textarea name="faits_reproches" rows="4"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('faits_reproches', $demande->faits_reproches) }}</textarea>
            </div>
        </div>
        @if(in_array('date_faits', $champs))
        <div class="form-group fg-3" style="margin-top:12px">
            <label>Date des faits</label>
            <div class="input-wrapper">
                <input type="date" name="date_faits"
                       value="{{ old('date_faits', $demande->date_faits?->format('Y-m-d')) }}">
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="form-section">
        <div class="form-group">
            <label>Observations</label>
            <div class="input-wrapper">
                <textarea name="observations" rows="2"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('observations', $demande->observations) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <a href="{{ route('demandes.show', $demande) }}" class="btn-ghost">Annuler</a>
        <button type="submit" name="action" value="brouillon" class="btn-ghost">Sauvegarder</button>
        <button type="submit" name="action" value="soumettre" class="btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </div>
</form>
</div>
<div class="conge-form-aside">
    <div class="aside-card aside-info">
        <h4><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg> À noter</h4>
        <p>Le type de demande et l'agent ne peuvent pas être modifiés. Créez une nouvelle demande si nécessaire.</p>
    </div>
</div>
</div>
@endsection
