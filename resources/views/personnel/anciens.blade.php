@extends('layouts.app')
@section('title', 'Anciens travailleurs')
@section('page-title', 'Anciens travailleurs')

@section('content')

<div class="kpi-grid kpi-grid-4 mb-24">
    <div class="kpi-card kpi-purple">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $total }}</div><div class="kpi-label">Anciens travailleurs</div></div>
    </div>
</div>

<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        <div class="input-wrapper search-input">
            <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher un ancien agent…">
        </div>

        <div class="input-wrapper select-wrapper filter-select">
            <select name="service">
                <option value="">Tous les services</option>
                @foreach($services as $s)
                    <option value="{{ $s }}" {{ ($filters['service'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div class="input-wrapper select-wrapper filter-select">
            <select name="motif">
                <option value="">Tous les motifs</option>
                @foreach($motifs as $key => $label)
                    <option value="{{ $key }}" {{ ($filters['motif'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-ghost btn-sm">Filtrer</button>
        @if(array_filter($filters))
            <a href="{{ route('personnel.anciens') }}" class="btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>

    <div class="toolbar-actions">
        <a href="{{ route('personnel.index') }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            Retour au personnel actif
        </a>
    </div>
</div>

<div class="dash-card p-0">
    @if($personnels->isEmpty())
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
            <p>Aucun ancien travailleur pour le moment.</p>
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Corporation</th>
                <th>Service</th>
                <th>Motif du départ</th>
                <th>Date de départ</th>
                <th>Statut</th>
                <th class="th-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personnels as $p)
            <tr>
                <td>
                    <div class="agent-cell">
                        @if($p->photo_url)
                            <img src="{{ $p->photo_url }}" alt="Photo de {{ $p->nom_complet }}" class="agent-photo">
                        @else
                            <div class="agent-avatar av-sm">{{ $p->initiales }}</div>
                        @endif
                        <div>
                            <div class="agent-name">{{ $p->nom_complet }}</div>
                            <div class="agent-meta">{{ $p->sexe === 'M' ? 'H' : 'F' }} · {{ $p->telephone ?: '—' }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="text-truncate-cell" title="{{ $p->corporation }}">{{ $p->corporation ?: '—' }}</span></td>
                <td><span class="text-truncate-cell" title="{{ $p->service }}">{{ $p->service ?: '—' }}</span></td>
                <td style="font-size:.8rem">{{ $p->motif_depart_label ?: '—' }}</td>
                <td>{{ $p->date_depart ? $p->date_depart->format('d/m/Y') : '—' }}</td>
                <td><span class="statut-pill statut-{{ $p->statut }}">{{ $p->statut_label }}</span></td>
                <td class="td-actions">
                    <a href="{{ route('personnel.show', $p) }}" class="icon-btn" title="Voir la fiche">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <form method="POST" action="{{ route('personnel.restaurer', $p) }}" class="d-inline"
                          onsubmit="return confirm('Restaurer {{ $p->nom_complet }} comme personnel actif ?')">
                        @csrf
                        <button type="submit" class="icon-btn" title="Restaurer">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    @if($personnels->hasPages())
    <div class="pagination-bar">
        <span class="pag-info">{{ $personnels->firstItem() }}–{{ $personnels->lastItem() }} sur {{ $personnels->total() }} anciens travailleurs</span>
        {{ $personnels->links('vendor.pagination.simple') }}
    </div>
    @endif
    @endif
</div>

@endsection
