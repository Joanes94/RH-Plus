@extends('layouts.app')
@section('title', 'Passation de service')
@section('page-title', 'Passation de service / Transfert d\'utilisateur')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('users.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour aux utilisateurs
    </a>
</div>

@if($errors->any())
<div class="alert alert-error mb-20">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ $errors->first() }}
</div>
@endif

<div class="grid-2-cols" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    {{-- Formulaire de transfert --}}
    <div class="dash-card">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                Passation de service / Mutation
            </h3>
        </div>
        <div class="card-body p-20">
            <div class="user-summary-box mb-20" style="background:#f9fafb; padding:1rem; border-radius:10px; border:1px solid #e5e7eb;">
                <h4 style="margin:0 0 0.5rem 0; font-size:1rem; color:#111827;">{{ $user->nom_complet }}</h4>
                <div style="font-size:0.85rem; color:#4b5563; display:flex; flex-direction:column; gap:0.25rem;">
                    <span><strong>Email :</strong> {{ $user->email }}</span>
                    <span><strong>Rôle :</strong> {{ $user->role_label }}</span>
                    <span><strong>Centre actuel :</strong> <span class="badge badge-info">{{ $user->centre?->nom ?? 'Aucun (Global)' }}</span></span>
                </div>
            </div>

            <form method="POST" action="{{ route('users.transferer.store', $user) }}">
                @csrf
                <div class="form-group mb-15">
                    <label style="font-weight:600; font-size:0.88rem; display:block; margin-bottom:0.4rem;">Nouveau Centre d'affectation <span style="color:#ef4444;">*</span></label>
                    <div class="input-wrapper select-wrapper">
                        <select name="nouveau_centre_id" required style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid #d1d5db;">
                            <option value="">— Sélectionner le nouveau centre —</option>
                            @foreach($centres as $c)
                                <option value="{{ $c->id }}" {{ old('nouveau_centre_id') == $c->id || $user->centre_id == $c->id ? 'selected' : '' }} {{ $user->centre_id == $c->id ? 'disabled' : '' }}>
                                    {{ $c->nom }} ({{ $c->code }}) {{ $user->centre_id == $c->id ? '— (Centre Actuel)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group mb-15">
                    <label style="font-weight:600; font-size:0.88rem; display:block; margin-bottom:0.4rem;">Date de passation / Prise de fonction <span style="color:#ef4444;">*</span></label>
                    <div class="input-wrapper">
                        <input type="date" name="date_transfert" value="{{ old('date_transfert', date('Y-m-d')) }}" required style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid #d1d5db;">
                    </div>
                </div>

                <div class="form-group mb-20">
                    <label style="font-weight:600; font-size:0.88rem; display:block; margin-bottom:0.4rem;">Motif & Observations de la passation</label>
                    <div class="input-wrapper">
                        <textarea name="motif" rows="4" placeholder="Ex: Décision d'affectation N°... du Conseil d'Administration. Passation de service effectuée le..." style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid #d1d5db;">{{ old('motif') }}</textarea>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                    <a href="{{ route('users.index') }}" class="btn-ghost">Annuler</a>
                    <button type="submit" class="btn-primary" style="background:#1a5c45; color:#fff; padding:0.6rem 1.2rem; border-radius:8px; border:none; font-weight:600; cursor:pointer;">
                        Valider la passation de service
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Historique des passations de l'utilisateur --}}
    <div class="dash-card">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Historique des passations de cet utilisateur
            </h3>
        </div>
        <div class="card-body p-0">
            @if($user->historiques->isEmpty())
                <div style="padding:2rem; text-align:center; color:#9ca3af; font-size:0.88rem;">
                    Aucune passation de service enregistrée pour cet utilisateur.
                </div>
            @else
                <table class="table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#fafafa; font-size:0.75rem; text-transform:uppercase; color:#6b7280;">
                            <th style="padding:0.75rem 1rem; text-align:left;">Date</th>
                            <th style="padding:0.75rem 1rem; text-align:left;">Origine ➔ Destination</th>
                            <th style="padding:0.75rem 1rem; text-align:left;">Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->historiques as $h)
                        <tr style="border-top:1px solid #f3f4f6; font-size:0.85rem;">
                            <td style="padding:0.75rem 1rem; white-space:nowrap;">{{ $h->date_transfert->format('d/m/Y') }}</td>
                            <td style="padding:0.75rem 1rem;">
                                <span style="color:#6b7280;">{{ $h->ancien_centre_nom ?? 'N/A' }}</span>
                                ➔
                                <strong>{{ $h->nouveau_centre_nom }}</strong>
                            </td>
                            <td style="padding:0.75rem 1rem; color:#4b5563; font-size:0.8rem;">
                                {{ $h->motif ?? '—' }}
                                <div style="font-size:0.72rem; color:#9ca3af; margin-top:2px;">Par {{ $h->transferePar?->nom_complet ?? 'Système' }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
