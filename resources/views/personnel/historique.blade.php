@extends('layouts.app')

@section('title', 'Historique — ' . $personnel->nom_complet)
@section('page-title', 'Historique du personnel')

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('personnel.show', $personnel) }}" class="btn-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Retour à la fiche
        </a>
        <h1 class="page-heading" style="margin-top: 0.5rem;">{{ $personnel->nom_complet }}</h1>
        <p class="page-subtitle">Historique complet des mouvements</p>
    </div>
</div>

<div class="timeline-container">
    @forelse($historiques as $historique)
    <div class="timeline-item">
        <div class="timeline-marker {{ $historique->type_evenement }}">
            @switch($historique->type_evenement)
                @case('embauche')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    @break
                @case('transfert')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                    @break
                @case('avancement')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
                    @break
                @case('desactivation')
                @case('fin_contrat')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    @break
                @case('passage_cdi')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    @break
                @case('reactivation')
                @case('renouvellement')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    @break
                @default
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="4"/></svg>
            @endswitch
        </div>

        <div class="timeline-content">
            <div class="timeline-header">
                <span class="timeline-type type-{{ $historique->type_evenement }}">{{ $historique->type_label }}</span>
                <span class="timeline-period">{{ $historique->periode }}</span>
            </div>

            <div class="timeline-details">
                <div class="detail-grid">
                    @if($historique->centre_nom)
                    <div class="detail-item">
                        <span class="detail-label">Centre / ISD</span>
                        <span class="detail-value">{{ $historique->centre_nom }}</span>
                    </div>
                    @endif
                    @if($historique->type_contrat)
                    <div class="detail-item">
                        <span class="detail-label">Type de contrat</span>
                        <span class="detail-value">{{ $historique->type_contrat }}</span>
                    </div>
                    @endif
                    @if($historique->categorie_echelon)
                    <div class="detail-item">
                        <span class="detail-label">Catégorie / Échelon</span>
                        <span class="detail-value">{{ $historique->categorie_echelon }}</span>
                    </div>
                    @endif
                    @if($historique->salaire_base)
                    <div class="detail-item">
                        <span class="detail-label">Salaire de base</span>
                        <span class="detail-value">{{ number_format($historique->salaire_base, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                    @if($historique->corporation)
                    <div class="detail-item">
                        <span class="detail-label">Corporation / Fonction</span>
                        <span class="detail-value">{{ $historique->corporation }}</span>
                    </div>
                    @endif
                    @if($historique->service)
                    <div class="detail-item">
                        <span class="detail-label">Service</span>
                        <span class="detail-value">{{ $historique->service }}</span>
                    </div>
                    @endif
                </div>

                @if($historique->commentaire)
                <div class="timeline-comment">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    {{ $historique->commentaire }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
        <p>Aucun historique enregistré pour ce personnel.</p>
    </div>
    @endforelse
</div>

@push('styles')
<style>
.timeline-container { max-width: 800px; position: relative; padding-left: 2.5rem; }
.timeline-container::before {
    content: '';
    position: absolute;
    left: 15px; top: 0; bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, var(--col-primary, #1a5c45) 0%, #e5e7eb 100%);
}

.timeline-item { position: relative; margin-bottom: 1.5rem; }

.timeline-marker {
    position: absolute;
    left: -2.5rem; top: 0.5rem;
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    z-index: 1;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.timeline-marker.embauche      { background: #10b981; }
.timeline-marker.transfert     { background: #3b82f6; }
.timeline-marker.avancement    { background: #8b5cf6; }
.timeline-marker.passage_cdi   { background: #06b6d4; }
.timeline-marker.desactivation,
.timeline-marker.fin_contrat   { background: #ef4444; }
.timeline-marker.reactivation,
.timeline-marker.renouvellement { background: #f59e0b; }

.timeline-content {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.timeline-content:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }

.timeline-header {
    padding: 0.75rem 1rem;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid #f5f5f5;
}
.timeline-type { font-weight: 600; font-size: 0.85rem; padding: 3px 10px; border-radius: 6px; }
.type-embauche      { background: rgba(16,185,129,0.1); color: #047857; }
.type-transfert     { background: rgba(59,130,246,0.1); color: #1d4ed8; }
.type-avancement    { background: rgba(139,92,246,0.1); color: #6d28d9; }
.type-passage_cdi   { background: rgba(6,182,212,0.1); color: #0e7490; }
.type-desactivation,
.type-fin_contrat   { background: rgba(239,68,68,0.1); color: #b91c1c; }
.type-reactivation,
.type-renouvellement { background: rgba(245,158,11,0.1); color: #b45309; }

.timeline-period { font-size: 0.75rem; color: #6b7280; }

.timeline-details { padding: 1rem; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 1.5rem; }
.detail-label { display: block; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }
.detail-value { font-size: 0.85rem; font-weight: 500; color: #111827; }

.timeline-comment {
    margin-top: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: #f9fafb;
    border-radius: 8px;
    font-size: 0.8rem;
    color: #6b7280;
    display: flex; align-items: flex-start; gap: 0.5rem;
}

.empty-state { text-align: center; padding: 3rem; color: #9ca3af; }
.empty-state svg { margin-bottom: 0.5rem; opacity: 0.4; }

.btn-back { display: inline-flex; align-items: center; gap: 0.25rem; color: #6b7280; font-size: 0.85rem; text-decoration: none; }
.btn-back:hover { color: #111827; }
</style>
@endpush
@endsection
