@extends('layouts.app')
@section('title', 'Congés')
@section('page-title', 'Demandes de congés')

@section('content')

<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        <div class="input-wrapper search-input">
            <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un agent…">
        </div>
        <div class="input-wrapper select-wrapper filter-select">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                <option value="soumis"    {{ request('statut') === 'soumis'    ? 'selected' : '' }}>En attente</option>
                <option value="approuve"  {{ request('statut') === 'approuve'  ? 'selected' : '' }}>Approuvé</option>
                <option value="rejete"    {{ request('statut') === 'rejete'    ? 'selected' : '' }}>Rejeté</option>
            </select>
        </div>
        <div class="input-wrapper select-wrapper filter-select" style="min-width:120px">
            <select name="annee">
                <option value="">Toutes les années</option>
                @foreach($annees as $a)
                    <option value="{{ $a }}" {{ request('annee') === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-ghost btn-sm">Filtrer</button>
        @if(request()->hasAny(['search','statut','annee']))
            <a href="{{ route('conges.index') }}" class="btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('conges.create') }}" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvelle demande
        </a>
    </div>
</div>

<div class="dash-card p-0">
    @if($conges->isEmpty())
        <div class="empty-state">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <p>Aucune demande de congé.</p>
            <a href="{{ route('conges.create') }}" class="btn-primary btn-sm">Créer la première demande</a>
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Type</th>
                <th>Période</th>
                <th>Jours</th>
                <th>Solde restant</th>
                <th>Année</th>
                <th>Statut</th>
                <th class="th-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($conges as $c)
            <tr>
                <td>
                    <div class="agent-cell">
    @if($c->personnel->photo_url)
        <img src="{{ $c->personnel->photo_url }}" alt="{{ $c->personnel->nom_complet }}" class="agent-photo">
    @else
        <div class="agent-avatar av-sm">{{ $c->personnel->initiales }}</div>
    @endif
    <div>
        <div class="agent-name">{{ $c->personnel->nom_complet }}</div>
        <div class="agent-meta">{{ $c->personnel->service ?: '—' }}</div>
    </div>
</div>
                </td>
                <td>
                    <span class="type-tag type-{{ $c->type_conge }}">
                        {{ $c->getTypeCongeLabel() }}
                    </span>
                </td>
                <td>
                    <span class="date-range">
                        {{ $c->date_debut->format('d/m/Y') }}
                        <span class="date-arrow">→</span>
                        {{ $c->date_fin->format('d/m/Y') }}
                    </span>
                </td>
                <td class="text-center fw-bold">{{ $c->nb_jours_demandes }}j</td>
                <td class="text-center">
                    <span class="solde-badge {{ $c->nb_jours_restants <= 0 ? 'solde-zero' : ($c->nb_jours_restants <= 5 ? 'solde-low' : 'solde-ok') }}">
                        {{ $c->nb_jours_restants }}j
                    </span>
                </td>
                <td class="mono-text">{{ $c->annee }}</td>
                <td>
                    <span class="status-badge status-{{ $c->getStatutColor() }}">
                        {{ $c->getStatutLabel() }}
                    </span>
                </td>
                <td class="td-actions">
                    <a href="{{ route('conges.show', $c) }}" class="icon-btn" title="Voir">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    @if($c->isEditable())
                    <a href="{{ route('conges.edit', $c) }}" class="icon-btn" title="Modifier">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    @endif
                    @if($c->statut === 'approuve')
                    <a href="{{ route('conges.document', $c) }}" class="icon-btn" title="Document" target="_blank">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @if($conges->hasPages())
    <div class="pagination-bar">
        <span class="pag-info">{{ $conges->firstItem() }}–{{ $conges->lastItem() }} sur {{ $conges->total() }}</span>
        {{ $conges->links('vendor.pagination.simple') }}
    </div>
    @endif
    @endif
</div>
@endsection