@extends('layouts.app')
@section('title', 'Rapport historique du personnel')
@section('page-title', 'Rapport historique')

@section('content')

<div class="rapport-tabs">
    <a href="{{ route('rapports.personnel') }}" class="rapport-tab">📋 Situation actuelle</a>
    <a href="{{ route('rapports.absents') }}" class="rapport-tab">🚫 Absents du jour</a>    <a href="{{ route('rapports.historique') }}" class="rapport-tab rapport-tab-active">🕒 Rapport historique (année / mois)</a>
</div>

<div class="rapport-layout">

    {{-- ── Panneau filtres ──────────────────────────────────────── --}}
    <div class="rapport-filters">
        <div class="dash-card">
            <div class="card-header"><h3>Période à consulter</h3></div>

            <form method="GET" action="{{ route('rapports.historique') }}">
                <div class="form-group">
                    <label>Année <span class="req">*</span></label>
                    <div class="input-wrapper select-wrapper">
                        <select name="annee" required>
                            @for($y = now()->year; $y >= now()->year - 20; $y--)
                                <option value="{{ $y }}" {{ ($filters['annee'] ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mois</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="mois">
                            <option value="">Toute l'année</option>
                            @foreach(['1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin','7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $num => $label)
                                <option value="{{ $num }}" {{ (string)($filters['mois'] ?? '') === (string)$num ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Service</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="service">
                            <option value="">Tous les services</option>
                            @foreach($services as $s)
                                <option value="{{ $s }}" {{ ($filters['service'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Type de contrat</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="type_contrat">
                            <option value="">Tous les contrats</option>
                            @foreach($contratsTypes as $c)
                                <option value="{{ $c }}" {{ ($filters['type_contrat'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rapport-filter-actions">
                    <button type="submit" class="btn-ghost btn-sm" style="width:100%">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Voir la période
                    </button>
                </div>
            </form>
        </div>

        @if($stats['total_personnes'] > 0)
        <div class="dash-card" style="text-align:center;padding:18px">
            <div style="margin-bottom:12px">
                <span style="font-size:1.2rem;font-weight:700;color:var(--col-primary)">{{ $stats['total_personnes'] }}</span>
                <span style="font-size:.82rem;color:var(--col-text-2)">personne(s) en poste sur la période</span>
            </div>
            <a href="{{ route('rapports.historique.pdf', $filters) }}" target="_blank" class="btn-primary" style="width:100%;justify-content:center">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                📄 Générer le rapport PDF
            </a>
        </div>
        @endif
    </div>

    {{-- ── Zone résultats ───────────────────────────────────────── --}}
    <div class="rapport-content">

        <div class="result-summary mb-16">
            <div class="summary-left">
                <span class="summary-total">
                    <strong>{{ $periode_label }}</strong> ·
                    <span class="summary-number">{{ $stats['total_personnes'] }}</span> personne(s),
                    {{ $stats['total_contrats'] }} contrat(s) actif(s) sur la période
                </span>
            </div>
            <div class="summary-right">
                <span class="summary-gender">
                    <span class="gender-male">👨 {{ $stats['hommes'] }}</span>
                    <span class="gender-female">👩 {{ $stats['femmes'] }}</span>
                </span>
            </div>
        </div>

        @if($contrats->isEmpty())
        <div class="dash-card" style="text-align:center;padding:48px">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3);display:block;margin:0 auto 16px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <p style="color:var(--col-text-2)">Aucun contrat actif n'a été trouvé pour {{ strtolower($periode_label) }}.</p>
        </div>
        @else
        <div class="dash-card p-0" style="margin-top:0">
            <div class="card-header" style="padding:16px 20px"><h3>👥 Personnel en poste — {{ $periode_label }}</h3></div>
            <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:200px">Agent</th>
                        <th>Service</th>
                        <th>Fonction</th>
                        <th>Centre</th>
                        <th>Contrat</th>
                        <th>Période du contrat</th>
                        <th>Statut aujourd'hui</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contrats as $c)
                    <tr>
                        <td>
                            @if($c->personnel)
                            <div class="agent-cell">
                                @if($c->personnel->photo_url)
                                    <img src="{{ $c->personnel->photo_url }}" alt="{{ $c->personnel->nom_complet }}" class="agent-photo">
                                @else
                                    <div class="agent-avatar av-sm">{{ $c->personnel->initiales }}</div>
                                @endif
                                <div>
                                    <a href="{{ route('personnel.show', $c->personnel) }}" class="agent-name" style="text-decoration:none;color:inherit">{{ $c->personnel->nom_complet }}</a>
                                </div>
                            </div>
                            @else — @endif
                        </td>
                        <td style="font-size:.8rem">{{ $c->service ?: '—' }}</td>
                        <td style="font-size:.8rem">{{ $c->fonction ?: '—' }}</td>
                        <td style="font-size:.8rem">{{ $c->centre ?: '—' }}</td>
                        <td><span class="contrat-tag contrat-{{ strtolower($c->type_contrat) }}">{{ $c->type_contrat }}</span></td>
                        <td style="font-size:.8rem">
                            Du {{ $c->date_debut->format('d/m/Y') }}
                            {{ $c->date_fin ? 'au ' . $c->date_fin->format('d/m/Y') : '(en cours)' }}
                        </td>
                        <td>
                            @if($c->personnel)
                            <span class="statut-pill statut-{{ $c->personnel->statut }}">{{ $c->personnel->statut_label }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .rapport-tabs { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
    .rapport-tab {
        padding: 9px 16px; border-radius: 8px; font-size: .85rem; font-weight: 600;
        color: var(--col-text-2); background: var(--col-bg-2); border: 1px solid var(--col-border-lg);
        text-decoration: none; transition: background .15s, color .15s;
    }
    .rapport-tab:hover { background: var(--col-border); }
    .rapport-tab-active { background: var(--col-primary); color: #fff; border-color: var(--col-primary); }

    .rapport-layout { display: grid; grid-template-columns: 300px 1fr; gap: 24px; align-items: start; }
    @media (max-width: 992px) { .rapport-layout { grid-template-columns: 1fr; } }
    .rapport-filters { position: sticky; top: 20px; }
    .rapport-filters .dash-card { margin-bottom: 16px; }
    .rapport-filters .dash-card:last-child { margin-bottom: 0; }
    .rapport-filter-actions { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; }
    .result-summary {
        display: flex; justify-content: space-between; align-items: center;
        background: var(--col-bg-2); padding: 12px 20px; border-radius: var(--radius);
        border: 1px solid var(--col-border-lg); flex-wrap: wrap; gap: 8px;
    }
    .summary-number { font-weight: 700; font-size: 1.1rem; color: var(--col-text-1); }
    .mb-16 { margin-bottom: 16px; }
</style>

@endsection