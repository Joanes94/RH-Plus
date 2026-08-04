@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord — Assistant RH')

@section('content')
<div class="dashboard-assistant">
    @if($centre)
    <div class="centre-banner">
        <div class="centre-banner-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <span>{{ $centre->nom }}</span>
    </div>
    @endif

    <div class="kpi-row">
        <div class="kpi-card kpi-primary">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $effectifTotal }}</span>
                <span class="kpi-label">Effectif en poste</span>
                <div class="kpi-sub">
                    <span class="kpi-tag kpi-tag-blue">{{ $hommes }} H</span>
                    <span class="kpi-tag kpi-tag-pink">{{ $femmes }} F</span>
                </div>
            </div>
        </div>

        <div class="kpi-card kpi-info">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $enConge }}</span>
                <span class="kpi-label">En congé aujourd'hui</span>
            </div>
        </div>

        <div class="kpi-card kpi-warning">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
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

        <div class="kpi-card kpi-accent">
            <div class="kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $nbAnciens }}</span>
                <span class="kpi-label">Anciens travailleurs</span>
            </div>
        </div>
    </div>

    @if($anciens && $anciens->count() > 0)
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Derniers départs
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Corporation</th>
                        <th>Motif</th>
                        <th>Date de départ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anciens as $ancien)
                    <tr>
                        <td>{{ $ancien->nom_complet }}</td>
                        <td>{{ $ancien->corporation }}</td>
                        <td>{{ $ancien->motif_depart ?? '—' }}</td>
                        <td>{{ $ancien->date_depart ? $ancien->date_depart->format('d/m/Y') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.dashboard-assistant { max-width: 1200px; }

.centre-banner {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, rgba(26,92,69,0.06), rgba(26,92,69,0.02));
    border: 1px solid rgba(26,92,69,0.12);
    border-radius: 12px;
    margin-bottom: 1.5rem;
    color: var(--col-primary, #1a5c45);
    font-weight: 600;
    font-size: 0.9rem;
}
.centre-banner-icon { opacity: 0.7; }

.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
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

.kpi-value { font-size: 1.75rem; font-weight: 700; line-height: 1; display: block; color: #111827; }
.kpi-label { font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem; display: block; }
.kpi-sub { display: flex; gap: 0.4rem; margin-top: 0.5rem; flex-wrap: wrap; }
.kpi-tag { font-size: 0.7rem; padding: 2px 8px; border-radius: 99px; background: #f3f4f6; color: #374151; }
.kpi-tag-blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
.kpi-tag-pink { background: rgba(236,72,153,0.1); color: #ec4899; }

.card { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; overflow: hidden; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #f0f0f0; }
.card-title { font-size: 0.95rem; font-weight: 600; color: #111827; display: flex; align-items: center; gap: 0.5rem; margin: 0; }
</style>
@endpush
@endsection
