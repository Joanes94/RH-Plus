@extends('layouts.app')
@section('title', 'Tableau de bord ' . auth()->user()->role_label)
@section('page-title', 'Vue d\'ensemble RH' . (isset($centre) ? ' — ' . $centre->nom : ''))

@section('content')

@if(auth()->user()->isGlobal() && isset($centres))
<div class="centre-selector">
    <form method="GET" action="{{ route('dashboard.centre') }}" class="selector-form">
        <label for="centre_id">Centre :</label>
        <select name="centre_id" id="centre_id" onchange="this.form.submit()" class="form-control form-control-sm">
            <option value="">— Tous les centres —</option>
            @foreach($centres as $c)
            <option value="{{ $c->id }}" {{ ($centre && $centre->id == $c->id) ? 'selected' : '' }}>{{ $c->nom }}</option>
            @endforeach
        </select>
    </form>
</div>
@endif

{{-- KPI Cards DRH --}}
<div class="kpi-grid">
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-value">142</div>
            <div class="kpi-label">Effectif total</div>
        </div>
        <div class="kpi-trend up">↑ 5.2% vs N-1</div>
    </div>

    <div class="kpi-card kpi-green">
        <div class="kpi-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-value">87,4M</div>
            <div class="kpi-label">Masse salariale (FCFA)</div>
        </div>
        <div class="kpi-trend warn">+2.1% ce mois</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-value">3.8%</div>
            <div class="kpi-label">Taux d'absentéisme</div>
        </div>
        <div class="kpi-trend warn">↑ 0.4 pts</div>
    </div>

    <div class="kpi-card kpi-purple">
        <div class="kpi-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div class="kpi-body">
            <div class="kpi-value">94.2%</div>
            <div class="kpi-label">Taux de rétention</div>
        </div>
        <div class="kpi-trend up">Excellent</div>
    </div>
</div>

{{-- Charts row --}}
<div class="dash-two-col">

    {{-- Répartition par département --}}
    <div class="dash-card">
        <div class="card-header">
            <h3>Effectifs par département</h3>
        </div>
        <div class="dept-chart">
            @php
            $depts = [
                ['name' => 'Opérations',      'count' => 38, 'pct' => 27, 'color' => '#2563eb'],
                ['name' => 'Finance',          'count' => 22, 'pct' => 15, 'color' => '#7c3aed'],
                ['name' => 'Ressources Hum.',  'count' => 12, 'pct' => 8,  'color' => '#059669'],
                ['name' => 'Commercial',       'count' => 31, 'pct' => 22, 'color' => '#d97706'],
                ['name' => 'Informatique',     'count' => 24, 'pct' => 17, 'color' => '#dc2626'],
                ['name' => 'Direction',        'count' => 15, 'pct' => 11, 'color' => '#0891b2'],
            ];
            @endphp
            @foreach($depts as $d)
            <div class="dept-row">
                <div class="dept-name">{{ $d['name'] }}</div>
                <div class="dept-bar-wrap">
                    <div class="dept-bar" style="width: {{ $d['pct'] }}%; background: {{ $d['color'] }}"></div>
                </div>
                <div class="dept-count">{{ $d['count'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Colonne droite DRH --}}
    <div class="dash-col-right">

        {{-- Recrutements en cours --}}
        <div class="dash-card">
            <div class="card-header">
                <h3>Recrutements ouverts</h3>
                <span class="badge badge-blue">6 postes</span>
            </div>
            <div class="recruit-list">
                @php
                $recruits = [
                    ['poste' => 'Développeur Backend', 'dept' => 'Informatique', 'candidats' => 14, 'status' => 'active'],
                    ['poste' => 'Chargé de comptabilité', 'dept' => 'Finance', 'candidats' => 7, 'status' => 'active'],
                    ['poste' => 'Commercial terrain', 'dept' => 'Commercial', 'candidats' => 21, 'status' => 'review'],
                    ['poste' => 'Assistante de direction', 'dept' => 'Direction', 'candidats' => 9, 'status' => 'active'],
                ];
                @endphp
                @foreach($recruits as $r)
                <div class="recruit-item">
                    <div class="recruit-info">
                        <span class="recruit-poste">{{ $r['poste'] }}</span>
                        <span class="recruit-dept">{{ $r['dept'] }}</span>
                    </div>
                    <div class="recruit-right">
                        <span class="recruit-count">{{ $r['candidats'] }} candidats</span>
                        <span class="status-badge status-{{ $r['status'] === 'review' ? 'warn' : 'approved' }}">
                            {{ $r['status'] === 'review' ? 'En cours' : 'Actif' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Alertes RH --}}
        <div class="dash-card card-mini">
            <div class="card-header">
                <h3>Alertes RH</h3>
                <span class="badge badge-danger">3</span>
            </div>
            <div class="alert-rh-list">
                <div class="alert-rh-item alert-rh-red">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>2 contrats expirent dans 15 jours</span>
                </div>
                <div class="alert-rh-item alert-rh-amber">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>Formation obligatoire non effectuée — 8 agents</span>
                </div>
                <div class="alert-rh-item alert-rh-amber">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>Solde congés critique — 5 employés</span>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Répartition Hommes / Femmes --}}
<div class="dash-card">
    <div class="card-header">
        <h3>Parité Hommes / Femmes par département</h3>
    </div>
    <div class="parite-grid">
        @php
        $parite = [
            ['dept' => 'Opérations',    'h' => 62, 'f' => 38],
            ['dept' => 'Finance',       'h' => 45, 'f' => 55],
            ['dept' => 'RH',            'h' => 25, 'f' => 75],
            ['dept' => 'Commercial',    'h' => 58, 'f' => 42],
            ['dept' => 'Informatique',  'h' => 75, 'f' => 25],
            ['dept' => 'Direction',     'h' => 53, 'f' => 47],
        ];
        @endphp
        @foreach($parite as $p)
        <div class="parite-item">
            <div class="parite-label">{{ $p['dept'] }}</div>
            <div class="parite-bar">
                <div class="parite-h" style="width: {{ $p['h'] }}%">{{ $p['h'] }}%</div>
                <div class="parite-f" style="width: {{ $p['f'] }}%">{{ $p['f'] }}%</div>
            </div>
            <div class="parite-legend">
                <span class="leg-h">H {{ $p['h'] }}%</span>
                <span class="leg-f">F {{ $p['f'] }}%</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection

@push('styles')
<style>
.centre-selector { margin-bottom: 1.25rem; }
.selector-form { display: flex; align-items: center; gap: 0.75rem; }
.selector-form label { font-size: 0.85rem; font-weight: 600; color: #374151; white-space: nowrap; }
.selector-form select { min-width: 250px; }
</style>
@endpush
