@extends('layouts.app')
@section('title', 'Tableau de bord DRH')
@section('page-title', 'Tableau de bord — DRH')

@section('content')

{{-- KPI --}}
<div class="kpi-grid mb-24">
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div class="kpi-body">
            <div class="kpi-value">{{ $stats['personnel_actif'] }}</div>
            <div class="kpi-label">Agents actifs</div>
            @php
                $hDrh = \App\Models\Personnel::personnelPrincipal()->where('statut','actif')->where('sexe','M')->count();
                $fDrh = \App\Models\Personnel::personnelPrincipal()->where('statut','actif')->where('sexe','F')->count();
            @endphp
            <div class="kpi-hf">
                <span class="kpi-hf-h">{{ $hDrh }} H</span>
                <span class="kpi-hf-sep">·</span>
                <span class="kpi-hf-f">{{ $fDrh }} F</span>
            </div>
        </div>
    </div>
    <div class="kpi-card kpi-amber">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['en_attente'] }}</div><div class="kpi-label">En attente de décision</div></div>
        @if($stats['en_attente'] > 0) <div class="kpi-trend warn">À traiter</div> @endif
    </div>
    <div class="kpi-card kpi-green">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['conges_en_cours'] }}</div><div class="kpi-label">Congés en cours</div></div>
    </div>
    <div class="kpi-card kpi-purple">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ \App\Models\Stagiaire::where('statut','en_cours')->count() }}</div><div class="kpi-label">Stagiaires en cours</div></div>
    </div>
    <div class="kpi-card kpi-purple">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['anciens_travailleurs'] }}</div><div class="kpi-label">Anciens travailleurs</div></div>
        <a href="{{ route('personnel.anciens') }}" class="kpi-trend">Voir la liste →</a>
    </div>
</div>

<div class="dash-two-col">

    {{-- Congés en attente --}}
    <div class="dash-card">
        <div class="card-header">
            <h3>Demande de Congés en attente</h3>
            <span class="badge badge-warn">{{ $congesEnAttente->count() }}</span>
        </div>
        @if($congesEnAttente->isEmpty())
            <p class="empty-inline">Aucune demande de congé en attente.</p>
        @else
        <div class="attente-list">
            @foreach($congesEnAttente as $c)
            <a href="{{ route('conges.show', $c) }}" class="attente-item">
                <div class="agent-avatar av-sm">{{ $c->personnel->initiales }}</div>
                <div class="attente-info">
                    <span class="attente-name">{{ $c->personnel->nom_complet }}</span>
                    <span class="attente-meta">
                        {{ $c->getTypeCongeLabel() }} · {{ $c->nb_jours_demandes }}j
                        · du {{ $c->date_debut->format('d/m') }}
                    </span>
                </div>
                <span class="badge badge-warn">Décider</span>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <div class="dash-col-right">

        {{-- Absences en attente --}}
        <div class="dash-card">
            <div class="card-header">
                <h3>Demande d'Absences en attente</h3>
                <span class="badge badge-warn">{{ $absencesEnAttente->count() }}</span>
            </div>
            @if($absencesEnAttente->isEmpty())
                <p class="empty-inline">Aucune demande d'absence en attente.</p>
            @else
            <div class="attente-list">
                @foreach($absencesEnAttente as $a)
                <a href="{{ route('absences.show', $a) }}" class="attente-item">
                    <div class="agent-avatar av-sm">{{ $a->personnel->initiales }}</div>
                    <div class="attente-info">
                        <span class="attente-name">{{ $a->personnel->nom_complet }}</span>
                        <span class="attente-meta">{{ $a->getTypeLabel() }} · {{ $a->nb_jours }}j</span>
                    </div>
                    <span class="badge badge-warn">Décider</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Demandes en attente --}}
        <div class="dash-card">
            <div class="card-header">
                <h3>Autres Demandes en attente</h3>
                <span class="badge badge-warn">{{ $demandesEnAttente->count() }}</span>
            </div>
            @if($demandesEnAttente->isEmpty())
                <p class="empty-inline">Aucune demande en attente.</p>
            @else
            <div class="attente-list">
                @foreach($demandesEnAttente as $d)
                <a href="{{ route('demandes.show', $d) }}" class="attente-item">
                    <div class="agent-avatar av-sm">{{ $d->personnel->initiales }}</div>
                    <div class="attente-info">
                        <span class="attente-name">{{ $d->personnel->nom_complet }}</span>
                        <span class="attente-meta">{{ $d->type_label }}</span>
                    </div>
                    <span class="badge badge-warn">Décider</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Accès rapides — le DRH a maintenant les mêmes droits de création --}}
<div class="dash-card" style="margin-top:0">
    <div class="card-header"><h3>Accès rapides</h3></div>
    <div class="quick-links">
        <a href="{{ route('personnel.index') }}" class="ql-item">
            <div class="ql-icon ql-blue"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
            <span>Personnel</span>
        </a>
        <a href="{{ route('conges.create') }}" class="ql-item">
            <div class="ql-icon ql-amber"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <span>Nouveau congé</span>
        </a>
        <a href="{{ route('absences.create') }}" class="ql-item">
            <div class="ql-icon ql-purple"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <span>Nouvelle absence</span>
        </a>
        <a href="{{ route('demandes.create') }}" class="ql-item">
            <div class="ql-icon ql-teal"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <span>Nouvelle demande</span>
        </a>
        <a href="{{ route('drh.historique') }}" class="ql-item">
            <div class="ql-icon ql-green"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
            <span>Mon historique</span>
        </a>
        <a href="{{ route('rapports.personnel') }}" class="ql-item">
            <div class="ql-icon" style="background:var(--col-red-lt);color:var(--col-red)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
            <span>Rapports PDF</span>
        </a>
        <a href="{{ route('config-rh.index') }}" class="ql-item">
            <div class="ql-icon" style="background:var(--col-primary-lt);color:var(--col-primary)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></div>
            <span>Configuration</span>
        </a>
    </div>
</div>

{{-- Anciens travailleurs récents --}}
<div class="dash-card" style="margin-top:24px">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3>📦 Anciens travailleurs récents</h3>
        <a href="{{ route('personnel.anciens') }}" class="btn-ghost btn-sm">Voir tout ({{ $stats['anciens_travailleurs'] }})</a>
    </div>
    @forelse($anciensRecents as $a)
    <div class="dl-row" style="padding:10px 0;border-bottom:1px solid var(--col-border)">
        <dt style="display:flex;align-items:center;gap:8px">
            <div class="agent-avatar av-sm">{{ $a->initiales }}</div>
            <a href="{{ route('personnel.show', $a) }}" style="text-decoration:none;color:inherit;font-weight:600">{{ $a->nom_complet }}</a>
        </dt>
        <dd style="color:var(--col-text-2);font-size:.82rem">
            {{ $a->motif_depart_label }} @if($a->date_depart) · {{ $a->date_depart->format('d/m/Y') }} @endif
        </dd>
    </div>
    @empty
        <p class="empty-inline">Aucun ancien travailleur pour le moment.</p>
    @endforelse
</div>

@endsection