@extends('layouts.app')
@section('title', 'Demandes')
@section('page-title', 'Demandes & Attestations')

@section('content')

<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        <div class="input-wrapper search-input">
            <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un agent…">
        </div>
        <div class="input-wrapper select-wrapper filter-select">
            <select name="type_demande">
                <option value="">Tous les types</option>
                @foreach($catalogue as $groupe)
                    <optgroup label="{{ $groupe['label'] }}">
                        @foreach($groupe['types'] as $slug => $meta)
                            <option value="{{ $slug }}" {{ request('type_demande') === $slug ? 'selected' : '' }}>
                                {{ $meta['label'] }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
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
        @if(request()->hasAny(['search','type_demande','statut']))
            <a href="{{ route('demandes.index') }}" class="btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('demandes.create') }}" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvelle demande
        </a>
    </div>
</div>

<div class="dash-card p-0">
    @if($demandes->isEmpty())
        <div class="empty-state">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p>Aucune demande trouvée.</p>
            <a href="{{ route('demandes.create') }}" class="btn-primary btn-sm">Créer une demande</a>
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Type de demande</th>
                <th>Période / Date</th>
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
    @if($d->personnel->photo_url)
        <img src="{{ $d->personnel->photo_url }}" alt="{{ $d->personnel->nom_complet }}" class="agent-photo">
    @else
        <div class="agent-avatar av-sm">{{ $d->personnel->initiales }}</div>
    @endif
    <div>
        <div class="agent-name">{{ $d->personnel->nom_complet }}</div>
        <div class="agent-meta">{{ $d->personnel->service ?: '—' }}</div>
    </div>
</div>
                </td>
                <td>
                    <span class="demande-type-tag">{{ $d->type_label }}</span>
                </td>
                <td style="font-size:.82rem">
                    @if($d->date_debut && $d->date_fin)
                        <span class="date-range">
                            {{ $d->date_debut->format('d/m/Y') }}
                            <span class="date-arrow">→</span>
                            {{ $d->date_fin->format('d/m/Y') }}
                        </span>
                    @elseif($d->date_debut)
                        {{ $d->date_debut->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    <span class="status-badge status-{{ $d->statut_color }}">{{ $d->statut_label }}</span>
                </td>
                <td style="font-size:.8rem;color:var(--col-text-2)">
                    {{ $d->created_at->format('d/m/Y') }}
                </td>
                <td class="td-actions">
                    <a href="{{ route('demandes.show', $d) }}" class="icon-btn" title="Voir">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    @if($d->isEditable())
                    <a href="{{ route('demandes.edit', $d) }}" class="icon-btn" title="Modifier">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('demandes.destroy', $d) }}" class="d-inline"
                          onsubmit="return confirm('Supprimer cette demande ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn icon-btn-danger" title="Supprimer">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                    </form>
                    @endif
                    @if($d->statut === 'approuve')
                    <a href="{{ route('demandes.document', $d) }}" class="icon-btn" title="Document" target="_blank">
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
@endsection
