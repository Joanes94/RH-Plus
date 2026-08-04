@extends('layouts.app')
@section('title', 'Historique')
@section('page-title', 'Mon historique de décisions')

@section('content')

<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        <div class="input-wrapper select-wrapper filter-select">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="soumis"   {{ request('statut') === 'soumis'   ? 'selected' : '' }}>En attente</option>
                <option value="approuve" {{ request('statut') === 'approuve' ? 'selected' : '' }}>Approuvées</option>
                <option value="rejete"   {{ request('statut') === 'rejete'   ? 'selected' : '' }}>Rejetées</option>
            </select>
        </div>
        <div class="input-wrapper" style="min-width:150px">
            <input type="month" name="mois" value="{{ request('mois') }}">
        </div>
        <button type="submit" class="btn-ghost btn-sm">Filtrer</button>
        @if(request()->hasAny(['statut','mois']))
            <a href="{{ route('drh.historique') }}" class="btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>
    <div style="margin-left:auto;font-size:.82rem;color:var(--col-text-2)">
        {{ count($historique) }} entrée(s)
    </div>
</div>

<div class="dash-card p-0">
    @if(empty($historique))
        <div class="empty-state">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
            <p>Aucune entrée dans l'historique.</p>
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Type</th>
                <th>Détail</th>
                <th>Statut</th>
                <th>Date action</th>
                <th class="th-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historique as $item)
            <tr>
                <td>
                    <div class="agent-cell">
                        <div class="agent-avatar av-sm">{{ strtoupper(substr($item['agent'], 0, 2)) }}</div>
                        <div>
                            <div class="agent-name">{{ $item['agent'] }}</div>
                            <div class="agent-meta">{{ $item['service'] ?: '—' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="hist-type-badge hist-{{ strtolower(str_replace(' ', '_', $item['type_doc'])) }}">
                        {{ $item['type_doc'] }}
                    </span>
                </td>
                <td style="font-size:.82rem;color:var(--col-text-2);max-width:200px">
                    <span class="text-truncate-cell" title="{{ $item['type_sub'] }}">{{ $item['type_sub'] }}</span>
                </td>
                <td>
                    <span class="status-badge status-{{ $item['statut_color'] }}">
                        {{ $item['statut_label'] }}
                    </span>
                </td>
                <td style="font-size:.8rem;color:var(--col-text-2);white-space:nowrap">
                    @if($item['date'])
                        {{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y H:i') }}
                    @else —
                    @endif
                </td>
                <td class="td-actions">
                    <a href="{{ $item['route'] }}" class="icon-btn" title="Voir la demande">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    @if($item['doc_route'])
                    <a href="{{ $item['doc_route'] }}" class="icon-btn" title="Document officiel" target="_blank">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

@endsection
