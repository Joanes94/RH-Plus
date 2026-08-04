@extends('layouts.app')
@section('title', 'Absences')
@section('page-title', 'Autorisations d\'absence')

@section('content')
<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        <div class="input-wrapper search-input">
            <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…">
        </div>
        <div class="input-wrapper select-wrapper filter-select">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="soumis"   {{ request('statut') === 'soumis'   ? 'selected' : '' }}>En attente</option>
                <option value="approuve" {{ request('statut') === 'approuve' ? 'selected' : '' }}>Approuvée</option>
                <option value="rejete"   {{ request('statut') === 'rejete'   ? 'selected' : '' }}>Rejetée</option>
            </select>
        </div>
        <button type="submit" class="btn-ghost btn-sm">Filtrer</button>
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('absences.create') }}" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvelle demande
        </a>
    </div>
</div>

<div class="dash-card p-0">
    @if($absences->isEmpty())
        <div class="empty-state">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <p>Aucune demande d'absence.</p>
            <a href="{{ route('absences.create') }}" class="btn-primary btn-sm">Créer une demande</a>
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Type d'absence</th>
                <th>Période</th>
                <th>Jours</th>
                <th>Déductible</th>
                <th>Statut</th>
                <th class="th-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absences as $a)
            <tr>
                <td>
                    <div class="agent-cell">
    @if($a->personnel->photo_url)
        <img src="{{ $a->personnel->photo_url }}" alt="{{ $a->personnel->nom_complet }}" class="agent-photo">
    @else
        <div class="agent-avatar av-sm">{{ $a->personnel->initiales }}</div>
    @endif
    <div>
        <div class="agent-name">{{ $a->personnel->nom_complet }}</div>
        <div class="agent-meta">{{ $a->personnel->service ?: '—' }}</div>
    </div>
</div>
                </td>
                <td style="font-size:.83rem; max-width:180px">{{ $a->getTypeLabel() }}</td>
                <td>
                    <span class="date-range">
                        {{ $a->date_debut->format('d/m/Y') }}
                        <span class="date-arrow">→</span>
                        {{ $a->date_fin->format('d/m/Y') }}
                    </span>
                </td>
                <td class="text-center fw-bold">{{ $a->nb_jours }}j</td>
                <td class="text-center">
                    @if($a->deductible)
                        <span class="badge badge-warn">Déductible</span>
                    @else
                        <span class="badge badge-green">Non déductible</span>
                    @endif
                </td>
                <td>
                    <span class="status-badge status-{{ $a->getStatutColor() }}">{{ $a->getStatutLabel() }}</span>
                </td>
                <td class="td-actions">
                    <a href="{{ route('absences.show', $a) }}" class="icon-btn" title="Voir">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    @if($a->isEditable())
                    <a href="{{ route('absences.edit', $a) }}" class="icon-btn" title="Modifier">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    @endif
                    @if($a->statut === 'approuve')
                    <a href="{{ route('absences.document', $a) }}" class="icon-btn" title="Document" target="_blank">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @if($absences->hasPages())
    <div class="pagination-bar">
        <span class="pag-info">{{ $absences->firstItem() }}–{{ $absences->lastItem() }} sur {{ $absences->total() }}</span>
        {{ $absences->links('vendor.pagination.simple') }}
    </div>
    @endif
    @endif
</div>
@endsection