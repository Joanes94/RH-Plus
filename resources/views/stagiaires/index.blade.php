@extends('layouts.app')
@section('title', 'Stagiaires')
@section('page-title', 'Gestion des stagiaires')

@section('content')

{{-- Stats --}}
<div class="kpi-grid mb-24">
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div class="kpi-body">
            <div class="kpi-value">{{ $stats['total'] }}</div>
            <div class="kpi-label">Total stagiaires</div>
        </div>
        @if($stats['total'] > 0)
        <div class="kpi-trend up">
            {{ $stats['hommes'] }}H · {{ $stats['femmes'] }}F
        </div>
        @endif
    </div>
    <div class="kpi-card kpi-green">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['en_cours'] }}</div><div class="kpi-label">En cours</div></div>
    </div>
    <div class="kpi-card kpi-purple">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['termines'] }}</div><div class="kpi-label">Terminés</div></div>
    </div>
</div>

{{-- Barre actions --}}
<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        <div class="input-wrapper search-input">
            <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher un stagiaire…">
        </div>
        <div class="input-wrapper select-wrapper filter-select">
            <select name="service">
                <option value="">Tous les services</option>
                @foreach($services as $s)
                    <option value="{{ $s }}" {{ ($filters['service'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="input-wrapper select-wrapper filter-select" style="min-width:130px">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="en_cours"  {{ ($filters['statut'] ?? '') === 'en_cours'  ? 'selected' : '' }}>En cours</option>
                <option value="termine"   {{ ($filters['statut'] ?? '') === 'termine'   ? 'selected' : '' }}>Terminé</option>
                <option value="abandonne" {{ ($filters['statut'] ?? '') === 'abandonne' ? 'selected' : '' }}>Abandonné</option>
            </select>
        </div>
        <button type="submit" class="btn-ghost btn-sm">Filtrer</button>
        @if(array_filter($filters))
            <a href="{{ route('stagiaires.index') }}" class="btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('stagiaires.demandes.index') }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Demandes
        </a>
        <a href="{{ route('stagiaires.create') }}" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter un stagiaire
        </a>
    </div>
</div>

{{-- Tableau --}}
<div class="dash-card p-0">
    @if($stagiaires->isEmpty())
        <div class="empty-state">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <p>Aucun stagiaire trouvé.</p>
            <a href="{{ route('stagiaires.create') }}" class="btn-primary btn-sm">Ajouter le premier stagiaire</a>
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Stagiaire</th>
                <th>Niveau / École</th>
                <th>Service</th>
                <th>Période de stage</th>
                <th>Durée</th>
                <th>Type</th>
                <th>Statut</th>
                <th class="th-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stagiaires as $s)
            <tr>
                <td>
                    <div class="agent-cell">
                        @if($s->photo_url)
                            <img src="{{ $s->photo_url }}" alt="" class="agent-photo">
                        @else
                            <div class="agent-avatar av-sm">{{ $s->initiales }}</div>
                        @endif
                        <div>
                            <div class="agent-name">{{ $s->nom_complet }}</div>
                            <div class="agent-meta">{{ $s->sexe === 'F' ? 'F' : 'H' }}
                                @if($s->email) · {{ $s->email }} @endif
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-size:.83rem">{{ $s->niveau_etude ?: '—' }}</div>
                    <div style="font-size:.75rem;color:var(--col-text-2)">{{ $s->ecole_formation ?: '' }}</div>
                </td>
                <td style="font-size:.82rem">{{ $s->service ?: '—' }}</td>
                <td>
                    @if($s->date_debut_stage && $s->date_fin_stage)
                    <span class="date-range" style="font-size:.8rem">
                        {{ $s->date_debut_stage->format('d/m/Y') }}
                        <span class="date-arrow">→</span>
                        {{ $s->date_fin_stage->format('d/m/Y') }}
                    </span>
                    @else —
                    @endif
                </td>
                <td style="font-size:.82rem;font-weight:600">{{ $s->duree_stage ?: '—' }}</td>
                <td style="font-size:.8rem">{{ $s->type_stage ?: '—' }}</td>
                <td>
                    <span class="status-badge status-{{ $s->statut_color }}">{{ $s->statut_label }}</span>
                </td>
                <td class="td-actions">
                    <a href="{{ route('stagiaires.show', $s) }}" class="icon-btn" title="Voir">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <a href="{{ route('stagiaires.edit', $s) }}" class="icon-btn" title="Modifier">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('stagiaires.destroy', $s) }}" class="d-inline"
                          onsubmit="return confirm('Archiver {{ $s->nom_complet }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn icon-btn-danger" title="Archiver">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @if($stagiaires->hasPages())
    <div class="pagination-bar">
        <span class="pag-info">{{ $stagiaires->firstItem() }}–{{ $stagiaires->lastItem() }} sur {{ $stagiaires->total() }}</span>
        {{ $stagiaires->links('vendor.pagination.simple') }}
    </div>
    @endif
    @endif
</div>

@endsection
