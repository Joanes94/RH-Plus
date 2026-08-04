@extends('layouts.app')
@section('title', 'Demandes — Stagiaires')
@section('page-title', 'Demandes des stagiaires')

@section('content')

<div class="page-header-bar">
    <a href="{{ route('stagiaires.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour aux stagiaires
    </a>
</div>

<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        <div class="input-wrapper search-input">
            <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un stagiaire…">
        </div>
        <div class="input-wrapper select-wrapper filter-select">
            <select name="type">
                <option value="">Tous les documents</option>
                <option value="autorisation" {{ request('type') === 'autorisation' ? 'selected' : '' }}>Autorisation de stage</option>
                <option value="attestation"  {{ request('type') === 'attestation'  ? 'selected' : '' }}>Attestation de stage</option>
                <option value="evaluation"   {{ request('type') === 'evaluation'   ? 'selected' : '' }}>Fiche d'évaluation</option>
            </select>
        </div>
        <div class="input-wrapper select-wrapper filter-select">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                <option value="soumis"    {{ request('statut') === 'soumis'    ? 'selected' : '' }}>En attente</option>
                <option value="approuve"  {{ request('statut') === 'approuve'  ? 'selected' : '' }}>Approuvée</option>
                <option value="rejete"    {{ request('statut') === 'rejete'    ? 'selected' : '' }}>Rejetée</option>
            </select>
        </div>
        <button type="submit" class="btn-ghost btn-sm">Filtrer</button>
        @if(request()->hasAny(['search','type','statut']))
            <a href="{{ route('stagiaires.demandes.index') }}" class="btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>
</div>

<div class="dash-card p-0">
    @if($demandes->isEmpty())
        <div class="empty-state">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p>Aucune demande trouvée.</p>
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Stagiaire</th>
                <th>Document</th>
                <th>Soumis par</th>
                <th>Statut</th>
                <th>Soumis le</th>
                <th class="th-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($demandes as $d)
            <tr>
                <td>
                    <div class="agent-cell">
    @if($d['stagiaire']->photo_url)
        <img src="{{ $d['stagiaire']->photo_url }}" alt="{{ $d['stagiaire']->nom_complet }}" class="agent-photo">
    @else
        <div class="agent-avatar av-sm">{{ $d['stagiaire']->initiales }}</div>
    @endif
    <div>
        <div class="agent-name">{{ $d['stagiaire']->nom_complet }}</div>
        <div class="agent-meta">{{ $d['stagiaire']->ecole_formation ?: ($d['stagiaire']->service ?: '—') }}</div>
    </div>
</div>
                </td>
                <td>
                    <span class="demande-type-tag">{{ $d['type_label'] }}</span>
                </td>
                <td style="font-size:.82rem;color:var(--col-text-2)">
                    {{ $d['cree_par']?->nom_complet ?? '—' }}
                </td>
                <td>
                    <span class="status-badge status-{{ $d['statut_color'] }}">{{ $d['statut_label'] }}</span>
                </td>
                <td style="font-size:.8rem;color:var(--col-text-2)">
                    {{ $d['date']?->format('d/m/Y') ?? '—' }}
                </td>
                <td class="td-actions">
                    <a href="{{ $d['route_show'] }}" class="icon-btn" title="Voir">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    @if($d['route_pdf'])
                    <a href="{{ $d['route_pdf'] }}" class="icon-btn" title="Document" target="_blank">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @if($demandes->hasPages())
    <div class="pagination-bar">
        <span class="pag-info">{{ $demandes->firstItem() }}–{{ $demandes->lastItem() }} sur {{ $demandes->total() }}</span>
        {{ $demandes->links('vendor.pagination.simple') }}
    </div>
    @endif
    @endif
</div>

@if(!auth()->user()->isDRH())
<p style="font-size:.78rem;color:var(--col-text-3);margin-top:12px">
    Seul le DRH peut approuver ou rejeter une demande. Vous pouvez suivre ici le statut de tout ce qui a été soumis.
</p>
@endif

@endsection
