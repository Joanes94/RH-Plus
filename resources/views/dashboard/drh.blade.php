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

<div class="dashboard-centre">
    @if($centre)
    <div class="centre-banner">
        <div class="centre-banner-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <span>{{ $centre->nom }}</span>
    </div>
    @endif

    <div class="kpi-grid">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-body">
                <div class="kpi-value">{{ $effectifTotal }}</div>
                <div class="kpi-label">Effectif en poste</div>
            </div>
            <div class="kpi-trend up">{{ $hommes }} H · {{ $femmes }} F</div>
        </div>

        <div class="kpi-card kpi-green">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-body">
                <div class="kpi-value">{{ is_object($enConge) ? $enConge->count() : $enConge }}</div>
                <div class="kpi-label">En congé aujourd'hui</div>
            </div>
            <div class="kpi-trend warn">{{ $nbCongesSoumis }} congés à traiter</div>
        </div>

        <div class="kpi-card kpi-amber">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="kpi-body">
                <div class="kpi-value">{{ $nbCongesSoumis + $nbAbsencesSoumis + $nbDemandesSoumis }}</div>
                <div class="kpi-label">Demandes en attente</div>
            </div>
            <div class="kpi-trend warn">{{ $nbAbsencesSoumis }} abs. · {{ $nbDemandesSoumis }} dem.</div>
        </div>

        <div class="kpi-card kpi-purple">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-body">
                <div class="kpi-value">{{ $nbAnciens }}</div>
                <div class="kpi-label">Anciens travailleurs</div>
            </div>
            <div class="kpi-trend up">{{ $nbStagiairesEnCours }} stagiaires actifs</div>
        </div>
    </div>

    <div class="dash-two-col">
        <div class="dash-card">
            <div class="card-header">
                <h3>Répartition par service</h3>
            </div>
            <div class="dept-chart">
                @forelse($parService as $service => $count)
                <div class="dept-row">
                    <div class="dept-name">{{ $service ?: 'Non défini' }}</div>
                    <div class="dept-bar-wrap">
                        <div class="dept-bar" style="width: {{ $parService->count() > 0 ? min(100, round(($count / max($parService->max(), 1)) * 100)) : 0 }}%; background: var(--col-primary, #1a5c45)"></div>
                    </div>
                    <div class="dept-count">{{ $count }}</div>
                </div>
                @empty
                <p class="empty-inline">Aucune répartition disponible.</p>
                @endforelse
            </div>
        </div>

        <div class="dash-col-right">
            <div class="dash-card">
                <div class="card-header">
                    <h3>Contrats expirant bientôt</h3>
                    <span class="badge badge-warn">{{ $contratsExpirantBientot->count() }}</span>
                </div>
                <div class="recruit-list">
                    @forelse($contratsExpirantBientot as $contrat)
                    <div class="recruit-item">
                        <div class="recruit-info">
                            <span class="recruit-poste">{{ $contrat->personnel->nom_complet }}</span>
                            <span class="recruit-dept">{{ $contrat->type_contrat }} · {{ $contrat->date_fin?->format('d/m/Y') }}</span>
                        </div>
                        <div class="recruit-right">
                            <span class="recruit-count">{{ $contrat->personnel->service ?: '—' }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="empty-inline">Aucun contrat à surveiller.</p>
                    @endforelse
                </div>
            </div>

            <div class="dash-card card-mini">
                <div class="card-header">
                    <h3>Personnel en congé</h3>
                    <span class="badge badge-blue">{{ is_object($enConge) ? $enConge->count() : $enConge }}</span>
                </div>
                <div class="alert-rh-list">
                    @forelse(is_object($enConge) ? $enConge : collect() as $conge)
                    <div class="alert-rh-item alert-rh-amber">
                        <span>{{ $conge->personnel->nom_complet }}</span>
                    </div>
                    @empty
                    <p class="empty-inline">Aucun agent en congé actuellement.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.centre-selector { margin-bottom: 1.25rem; }
.selector-form { display: flex; align-items: center; gap: 0.75rem; }
.selector-form label { font-size: 0.85rem; font-weight: 600; color: #374151; white-space: nowrap; }
.selector-form select { min-width: 250px; }
.centre-banner { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; background:linear-gradient(135deg, rgba(26,92,69,0.06), rgba(26,92,69,0.02)); border:1px solid rgba(26,92,69,0.12); border-radius:12px; margin-bottom:1.25rem; color:var(--col-primary, #1a5c45); font-weight:600; }
.centre-banner-icon { opacity:0.7; }
.dashboard-centre .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
.dashboard-centre .kpi-card { background:#fff; border-radius:16px; padding:1.1rem; display:flex; align-items:flex-start; gap:0.8rem; box-shadow:0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border:1px solid #f0f0f0; }
.dashboard-centre .kpi-value { font-size:1.6rem; font-weight:700; line-height:1; }
.dashboard-centre .kpi-label { font-size:0.8rem; color:#6b7280; margin-top:0.25rem; }
.dashboard-centre .kpi-trend { font-size:0.72rem; margin-left:auto; color:#6b7280; }
.dashboard-centre .kpi-blue .kpi-icon { color:#2563eb; background:rgba(37,99,235,0.1); }
.dashboard-centre .kpi-green .kpi-icon { color:#16a34a; background:rgba(22,163,74,0.1); }
.dashboard-centre .kpi-amber .kpi-icon { color:#d97706; background:rgba(217,119,6,0.1); }
.dashboard-centre .kpi-purple .kpi-icon { color:#8b5cf6; background:rgba(139,92,246,0.1); }
.dashboard-centre .dash-two-col { display:grid; grid-template-columns:1.3fr 0.9fr; gap:1rem; margin-top:1rem; }
.dashboard-centre .dash-card { background:#fff; border-radius:16px; padding:1rem 1.1rem; border:1px solid #f0f0f0; }
.dashboard-centre .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:0.8rem; }
.dashboard-centre .dept-row { display:grid; grid-template-columns:1.4fr 2fr 40px; gap:0.75rem; align-items:center; margin-bottom:0.65rem; font-size:0.85rem; }
.dashboard-centre .dept-bar-wrap { height:8px; background:#f3f4f6; border-radius:999px; overflow:hidden; }
.dashboard-centre .dept-bar { height:100%; border-radius:999px; }
.dashboard-centre .recruit-list { display:flex; flex-direction:column; gap:0.75rem; }
.dashboard-centre .recruit-item { display:flex; justify-content:space-between; gap:0.75rem; align-items:center; padding:0.7rem 0; border-bottom:1px solid #f3f4f6; }
.dashboard-centre .recruit-info { display:flex; flex-direction:column; gap:0.2rem; }
.dashboard-centre .recruit-poste { font-weight:600; color:#111827; }
.dashboard-centre .recruit-dept { font-size:0.8rem; color:#6b7280; }
.dashboard-centre .recruit-count { font-size:0.78rem; color:#6b7280; }
.dashboard-centre .empty-inline { margin:0; color:#6b7280; font-size:0.9rem; }
@media (max-width: 900px) { .dashboard-centre .dash-two-col { grid-template-columns:1fr; } }
</style>
@endpush
