@extends('layouts.app')

@section('title', 'Tableau de bord général')
@section('page-title', 'Tableau de bord général — DDIS')

@section('content')
<div class="dashboard-global">

    {{-- ── KPI Cards ──────────────────────────────────────────────────────── --}}
    <div class="kpi-row">
        <div class="kpi-card kpi-primary">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($totaux['total']) }}</span>
                <span class="kpi-label">Effectif total</span>
                <div class="kpi-sub">
                    <span class="kpi-tag kpi-tag-blue">{{ $totaux['hommes'] }} H</span>
                    <span class="kpi-tag kpi-tag-pink">{{ $totaux['femmes'] }} F</span>
                </div>
            </div>
        </div>

        <div class="kpi-card kpi-info">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $totaux['cdi'] }}</span>
                <span class="kpi-label">CDI</span>
            </div>
        </div>

        <div class="kpi-card kpi-warning">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $totaux['cdd'] }}</span>
                <span class="kpi-label">CDD</span>
            </div>
        </div>

        <div class="kpi-card kpi-accent">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $totaux['prestataires'] }}</span>
                <span class="kpi-label">Prestataires</span>
            </div>
        </div>

        <div class="kpi-card kpi-danger">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $nbCongesSoumis + $nbAbsencesSoumis + $nbDemandesSoumis }}</span>
                <span class="kpi-label">Demandes en attente</span>
                <div class="kpi-sub">
                    <span class="kpi-tag">{{ $nbCongesSoumis }} congés</span>
                    <span class="kpi-tag">{{ $nbAbsencesSoumis }} abs.</span>
                    <span class="kpi-tag">{{ $nbDemandesSoumis }} dem.</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tableau synthèse par centre ────────────────────────────────────── --}}
    <div class="card card-table">
        <div class="card-header">
            <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                Synthèse des effectifs par centre
            </h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-centres">
                    <thead>
                        <tr>
                            <th>Centre</th>
                            <th class="text-center">CDD</th>
                            <th class="text-center">CDI</th>
                            <th class="text-center">Prestataires</th>
                            <th class="text-center">Hommes</th>
                            <th class="text-center">Femmes</th>
                            <th class="text-center total-col">Total</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats as $centreId => $data)
                        <tr class="centre-row" onclick="window.location='{{ route('dashboard.centre', ['centre_id' => $centreId]) }}'">
                            <td class="centre-name">
                                <div class="centre-badge" style="--centre-color: {{ $loop->index % 2 === 0 ? 'var(--col-primary)' : 'var(--col-info)' }}">
                                    {{ substr($data['centre']->code, 0, 2) }}
                                </div>
                                <div>
                                    <span class="name">{{ $data['centre']->nom }}</span>
                                    <span class="email">{{ $data['centre']->email }}</span>
                                </div>
                            </td>
                            <td class="text-center"><span class="badge badge-cdd">{{ $data['cdd'] }}</span></td>
                            <td class="text-center"><span class="badge badge-cdi">{{ $data['cdi'] }}</span></td>
                            <td class="text-center"><span class="badge badge-prest">{{ $data['prestataires'] }}</span></td>
                            <td class="text-center"><span class="gender-m">{{ $data['hommes'] }}</span></td>
                            <td class="text-center"><span class="gender-f">{{ $data['femmes'] }}</span></td>
                            <td class="text-center total-col"><strong>{{ $data['total'] }}</strong></td>
                            <td class="text-center">
                                <a href="{{ route('dashboard.centre', ['centre_id' => $centreId]) }}" class="btn-view" title="Voir le centre">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="row-total">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-center"><strong>{{ $totaux['cdd'] }}</strong></td>
                            <td class="text-center"><strong>{{ $totaux['cdi'] }}</strong></td>
                            <td class="text-center"><strong>{{ $totaux['prestataires'] }}</strong></td>
                            <td class="text-center"><strong>{{ $totaux['hommes'] }}</strong></td>
                            <td class="text-center"><strong>{{ $totaux['femmes'] }}</strong></td>
                            <td class="text-center total-col"><strong>{{ $totaux['total'] }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Graphiques visuels ─────────────────────────────────────────────── --}}
    <div class="charts-row">
        {{-- Répartition par centre (barres) --}}
        <div class="card chart-card">
            <div class="card-header">
                <h3 class="card-title">Effectifs par centre</h3>
            </div>
            <div class="card-body">
                @foreach($stats as $centreId => $data)
                @if($data['total'] > 0)
                <div class="bar-item">
                    <div class="bar-label">{{ Str::limit($data['centre']->nom, 20) }}</div>
                    <div class="bar-track">
                        <div class="bar-fill bar-fill-primary" style="width: {{ $totaux['total'] > 0 ? round($data['total'] / $totaux['total'] * 100) : 0 }}%">
                            {{ $data['total'] }}
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Répartition H/F par centre --}}
        <div class="card chart-card">
            <div class="card-header">
                <h3 class="card-title">Parité Hommes / Femmes</h3>
            </div>
            <div class="card-body">
                @foreach($stats as $centreId => $data)
                @if($data['total'] > 0)
                <div class="parite-item">
                    <div class="parite-label">{{ Str::limit($data['centre']->nom, 18) }}</div>
                    <div class="parite-bar">
                        <div class="parite-h" style="width: {{ round($data['hommes'] / $data['total'] * 100) }}%" title="{{ $data['hommes'] }} hommes">
                            {{ round($data['hommes'] / $data['total'] * 100) }}%
                        </div>
                        <div class="parite-f" style="width: {{ round($data['femmes'] / $data['total'] * 100) }}%" title="{{ $data['femmes'] }} femmes">
                            {{ round($data['femmes'] / $data['total'] * 100) }}%
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
                <div class="parite-legend">
                    <span class="legend-h">■ Hommes</span>
                    <span class="legend-f">■ Femmes</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Alertes et actions rapides ─────────────────────────────────────── --}}
    <div class="alerts-row">
        {{-- Contrats expirant bientôt --}}
        @if($contratsExpirantBientot->count() > 0)
        <div class="card card-alert">
            <div class="card-header card-header-danger">
                <h3 class="card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Contrats expirant dans les 30 jours
                    <span class="count-badge">{{ $contratsExpirantBientot->count() }}</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert-list">
                    @foreach($contratsExpirantBientot->take(8) as $contrat)
                    <div class="alert-item">
                        <div class="alert-info">
                            <strong>{{ $contrat->personnel->nom_complet }}</strong>
                            <span class="alert-meta">{{ $contrat->type_contrat }} — {{ $contrat->personnel->centre?->nom ?? 'N/A' }}</span>
                        </div>
                        <div class="alert-date {{ $contrat->date_fin->diffInDays(now()) <= 7 ? 'urgent' : '' }}">
                            {{ $contrat->date_fin->format('d/m/Y') }}
                            <small>({{ $contrat->date_fin->diffInDays(now()) }}j)</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Retraites imminentes --}}
        @if($retraitesImminentes->count() > 0)
        <div class="card card-alert">
            <div class="card-header card-header-warning">
                <h3 class="card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Départs en retraite (3 prochains mois)
                    <span class="count-badge">{{ $retraitesImminentes->count() }}</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert-list">
                    @foreach($retraitesImminentes->take(8) as $personnel)
                    <div class="alert-item">
                        <div class="alert-info">
                            <strong>{{ $personnel->nom_complet }}</strong>
                            <span class="alert-meta">{{ $personnel->corporation }} — {{ $personnel->centre?->nom ?? 'N/A' }}</span>
                        </div>
                        <div class="alert-date">
                            60 ans le {{ $personnel->date_naissance->copy()->addYears(60)->format('d/m/Y') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

@push('styles')
<style>
/* ── Dashboard Global ───────────────────────────────────────────────── */
.dashboard-global { max-width: 1400px; }

/* KPI Row */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.kpi-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

.kpi-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.kpi-primary .kpi-icon { background: rgba(26,92,69,0.1); color: var(--col-primary, #1a5c45); }
.kpi-info .kpi-icon    { background: rgba(59,130,246,0.1); color: #3b82f6; }
.kpi-warning .kpi-icon { background: rgba(245,158,11,0.1); color: #f59e0b; }
.kpi-accent .kpi-icon  { background: rgba(139,92,246,0.1); color: #8b5cf6; }
.kpi-danger .kpi-icon  { background: rgba(239,68,68,0.1); color: #ef4444; }

.kpi-value { font-size: 1.75rem; font-weight: 700; line-height: 1; display: block; color: #111827; }
.kpi-label { font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem; display: block; }
.kpi-sub { display: flex; gap: 0.4rem; margin-top: 0.5rem; flex-wrap: wrap; }
.kpi-tag { font-size: 0.7rem; padding: 2px 8px; border-radius: 99px; background: #f3f4f6; color: #374151; }
.kpi-tag-blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
.kpi-tag-pink { background: rgba(236,72,153,0.1); color: #ec4899; }

/* Tableau centres */
.card-table { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 0.5rem; }
.card-title { font-size: 0.95rem; font-weight: 600; color: #111827; display: flex; align-items: center; gap: 0.5rem; margin: 0; }
.card-body { padding: 0; }

.table-centres { width: 100%; border-collapse: collapse; }
.table-centres th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; padding: 0.75rem 1rem; background: #fafafa; font-weight: 600; }
.table-centres td { padding: 0.75rem 1rem; border-top: 1px solid #f5f5f5; font-size: 0.875rem; }
.table-centres .total-col { background: rgba(26,92,69,0.04); }

.centre-row { cursor: pointer; transition: background 0.15s; }
.centre-row:hover { background: rgba(26,92,69,0.03); }

.centre-name { display: flex; align-items: center; gap: 0.75rem; }
.centre-badge {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--centre-color, var(--col-primary));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700;
    flex-shrink: 0;
}
.centre-name .name { display: block; font-weight: 500; color: #111827; }
.centre-name .email { display: block; font-size: 0.75rem; color: #9ca3af; }

.badge { padding: 3px 10px; border-radius: 99px; font-size: 0.78rem; font-weight: 600; }
.badge-cdd  { background: rgba(245,158,11,0.12); color: #b45309; }
.badge-cdi  { background: rgba(59,130,246,0.12); color: #1d4ed8; }
.badge-prest { background: rgba(139,92,246,0.12); color: #6d28d9; }

.gender-m { color: #3b82f6; font-weight: 500; }
.gender-f { color: #ec4899; font-weight: 500; }

.row-total { background: rgba(26,92,69,0.06); font-weight: 600; }
.row-total td { border-top: 2px solid var(--col-primary, #1a5c45) !important; }

.btn-view {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 8px;
    background: rgba(26,92,69,0.08); color: var(--col-primary, #1a5c45);
    transition: all 0.15s;
}
.btn-view:hover { background: var(--col-primary, #1a5c45); color: #fff; }

/* Charts */
.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
.chart-card { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; overflow: hidden; }
.chart-card .card-body { padding: 1.25rem; }

.bar-item { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.6rem; }
.bar-label { width: 120px; font-size: 0.78rem; color: #374151; text-align: right; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bar-track { flex: 1; height: 26px; background: #f3f4f6; border-radius: 8px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 8px; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px; font-size: 0.72rem; font-weight: 600; color: #fff; min-width: 30px; transition: width 0.6s ease; }
.bar-fill-primary { background: linear-gradient(135deg, var(--col-primary, #1a5c45), #2d8b68); }

.parite-item { margin-bottom: 0.5rem; }
.parite-label { font-size: 0.75rem; color: #374151; margin-bottom: 0.2rem; }
.parite-bar { display: flex; height: 22px; border-radius: 6px; overflow: hidden; }
.parite-h { background: linear-gradient(135deg, #3b82f6, #60a5fa); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 600; transition: width 0.6s ease; }
.parite-f { background: linear-gradient(135deg, #ec4899, #f472b6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 600; transition: width 0.6s ease; }
.parite-legend { display: flex; gap: 1rem; justify-content: center; margin-top: 0.75rem; font-size: 0.75rem; }
.legend-h { color: #3b82f6; } .legend-f { color: #ec4899; }

/* Alerts */
.alerts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
.card-alert { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; overflow: hidden; }
.card-header-danger { border-left: 4px solid #ef4444; }
.card-header-warning { border-left: 4px solid #f59e0b; }
.card-alert .card-body { padding: 0; }
.count-badge { font-size: 0.7rem; background: #ef4444; color: #fff; padding: 2px 8px; border-radius: 99px; margin-left: 0.5rem; }
.card-header-warning .count-badge { background: #f59e0b; }

.alert-list { }
.alert-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.25rem; border-bottom: 1px solid #f9fafb; transition: background 0.15s; }
.alert-item:hover { background: #fafafa; }
.alert-item:last-child { border-bottom: none; }
.alert-info strong { display: block; font-size: 0.85rem; color: #111827; }
.alert-meta { font-size: 0.75rem; color: #9ca3af; }
.alert-date { font-size: 0.8rem; color: #6b7280; text-align: right; white-space: nowrap; }
.alert-date.urgent { color: #ef4444; font-weight: 600; }
.alert-date small { display: block; font-size: 0.7rem; }

/* Responsive */
@media (max-width: 1024px) {
    .charts-row, .alerts-row { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .kpi-row { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush
@endsection
