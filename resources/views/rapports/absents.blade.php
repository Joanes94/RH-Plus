@extends('layouts.app')
@section('title', 'Absents du jour')
@section('page-title', 'Rapport des absents')

@section('content')

<div class="rapport-tabs no-print">
    <a href="{{ route('rapports.personnel') }}" class="rapport-tab">📋 Situation actuelle</a>
    <a href="{{ route('rapports.absents') }}" class="rapport-tab rapport-tab-active">🚫 Absents du jour</a>
    <a href="{{ route('rapports.historique') }}" class="rapport-tab">🕒 Rapport historique (année / mois)</a>
</div>

<div class="rapport-layout">

    {{-- ── Panneau filtres ──────────────────────────────────────── --}}
    <div class="rapport-filters no-print">
        <div class="dash-card">
            <div class="card-header"><h3>Date à consulter</h3></div>

            <form method="GET" action="{{ route('rapports.absents') }}" id="filtreForm">

                <div class="form-group">
                    <label>Date</label>
                    <div class="input-wrapper">
                        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}">
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
                    <label>Sexe</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="sexe">
                            <option value="">Hommes et Femmes</option>
                            <option value="M" {{ ($filters['sexe'] ?? '') === 'M' ? 'selected' : '' }}>👨 Hommes uniquement</option>
                            <option value="F" {{ ($filters['sexe'] ?? '') === 'F' ? 'selected' : '' }}>👩 Femmes uniquement</option>
                        </select>
                    </div>
                </div>

                <div class="rapport-filter-actions">
                    <button type="submit" class="btn-ghost btn-sm" style="width:100%">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Voir
                    </button>
                    <button type="button" class="btn-ghost btn-sm" style="width:100%" onclick="window.print()">
                        🖨️ Imprimer / PDF
                    </button>
                    @if(array_filter($filters))
                        <a href="{{ route('rapports.absents', ['date' => $date->format('Y-m-d')]) }}" class="btn-ghost btn-sm" style="width:100%;text-align:center">
                            ✕ Réinitialiser les filtres
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── Zone résultats ───────────────────────────────────────── --}}
    <div class="rapport-content">

        <div class="print-header" style="display:none">
            <h2>Rapport des absents — {{ $date->isoFormat('dddd D MMMM YYYY') }}</h2>
        </div>

        {{-- BANDEAU DE RÉSUMÉ --}}
        <div class="result-summary mb-16">
            <div class="summary-left">
                <span class="summary-total">
                    <span class="summary-number">{{ $stats['total'] }}</span> absent(s) le
                    <strong>{{ $date->isoFormat('dddd D MMMM YYYY') }}</strong>
                </span>
            </div>
            <div class="summary-right">
                <span class="summary-gender">
                    <span class="gender-male">👨 {{ $stats['hommes'] }} homme{{ $stats['hommes'] > 1 ? 's' : '' }}</span>
                    <span class="gender-female">👩 {{ $stats['femmes'] }} femme{{ $stats['femmes'] > 1 ? 's' : '' }}</span>
                </span>
            </div>
        </div>

        {{-- KPIs statistiques --}}
        <div class="rapport-stats-grid">
            <div class="rs-card rs-total">
                <div class="rs-val">{{ $stats['total'] }}</div>
                <div class="rs-label">Absents ce jour</div>
            </div>
            <div class="rs-card">
                <div class="rs-title">📌 Par catégorie</div>
                <div class="rs-stat-list">
                    <div class="rs-stat-row"><span>🏖️ Congés</span><span class="rs-stat-n">{{ $stats['conge'] }}</span></div>
                    <div class="rs-stat-row"><span>🤒 Congés maladie</span><span class="rs-stat-n">{{ $stats['conge_maladie'] }}</span></div>
                    <div class="rs-stat-row"><span>📋 Absences exceptionnelles</span><span class="rs-stat-n">{{ $stats['absence'] }}</span></div>
                </div>
            </div>
        </div>

        @if($absents->isEmpty())
        <div class="dash-card" style="text-align:center;padding:48px">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3);display:block;margin:0 auto 16px"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p style="color:var(--col-text-2)">Personne n'est absent (congé/absence approuvé) à cette date.</p>
        </div>
        @else
        <div class="dash-card p-0" style="margin-top:0">
            <div class="card-header" style="padding:16px 20px">
                <h3>🚫 Agents absents le {{ $date->isoFormat('D MMMM YYYY') }}</h3>
            </div>
            <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:200px">Agent</th>
                        <th>Sexe</th>
                        <th>Service</th>
                        <th>Corporation</th>
                        <th>Contrat</th>
                        <th>Motif d'absence</th>          {{-- UNE SEULE COLONNE --}}
                        <th>Date de reprise</th>
                        <th class="no-print">Détail</th>   {{-- UNE SEULE COLONNE --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($absents as $a)
                    <tr>
                        <td>
                            <div class="agent-cell">
                                @if($a['personnel']->photo_url)
                                    <img src="{{ $a['personnel']->photo_url }}" alt="{{ $a['personnel']->nom_complet }}" class="agent-photo">
                                @else
                                    <div class="agent-avatar av-sm">{{ $a['personnel']->initiales }}</div>
                                @endif
                                <div>
                                    <div class="agent-name">{{ $a['personnel']->nom_complet }}</div>
                                    <div class="agent-meta">
                                        du {{ $a['date_debut']->format('d/m/Y') }} au {{ $a['date_fin']->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $a['personnel']->sexe === 'F' ? '👩 Femme' : '👨 Homme' }}</td>
                        <td style="font-size:.8rem">{{ $a['personnel']->service ?: '—' }}</td>
                        <td style="font-size:.8rem">{{ $a['personnel']->corporation ?: '—' }}</td>
                        <td>@if($a['personnel']->type_contrat_actuel)<span class="contrat-tag contrat-{{ strtolower($a['personnel']->type_contrat_actuel) }}">{{ $a['personnel']->type_contrat_actuel }}</span>@else —@endif</td>
                        <td>   {{-- UNE SEULE CELLULE pour le motif --}}
                            <span class="demande-type-tag">{{ $a['categorie'] }}</span>
                            <div style="font-size:.75rem;color:var(--col-text-2);margin-top:2px">{{ $a['type_label'] }}</div>
                        </td>
                        <td>{{ $a['date_reprise'] ? $a['date_reprise']->format('d/m/Y') : '—' }}</td>
                        <td class="no-print">   {{-- UNE SEULE CELLULE pour le détail --}}
                            <a href="{{ $a['route_show'] }}" class="icon-btn" title="Voir">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
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

{{-- Styles additionnels (inchangés) --}}
<style>
    .rapport-tabs { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
    .rapport-tab {
        padding: 8px 16px; border-radius: 20px; font-size: .85rem; font-weight: 500;
        background: var(--col-bg-2); color: var(--col-text-2); border: 1px solid var(--col-border-lg);
        text-decoration: none;
    }
    .rapport-tab:hover { background: var(--col-border); }
    .rapport-tab-active { background: var(--col-primary); color: #fff; border-color: var(--col-primary); }

    .rapport-layout { display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start; }
    @media (max-width: 992px) { .rapport-layout { grid-template-columns: 1fr; } }
    .rapport-filters { position: sticky; top: 20px; }
    .rapport-filter-actions { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; }

    .result-summary {
        display: flex; justify-content: space-between; align-items: center;
        background: var(--col-bg-2); padding: 12px 20px; border-radius: var(--radius);
        border: 1px solid var(--col-border-lg); flex-wrap: wrap; gap: 8px;
    }
    .summary-number { font-weight: 700; font-size: 1.1rem; color: var(--col-text-1); }
    .summary-gender { display: flex; gap: 16px; font-size: .9rem; font-weight: 500; }
    .gender-male { color: #2563eb; background: rgba(37,99,235,.1); padding: 2px 12px; border-radius: 20px; }
    .gender-female { color: #db2777; background: rgba(219,39,119,.1); padding: 2px 12px; border-radius: 20px; }
    .mb-16 { margin-bottom: 16px; }

    .rapport-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 20px; }
    .rs-card { background: var(--col-bg-1); border: 1px solid var(--col-border-lg); border-radius: var(--radius); padding: 16px; }
    .rs-card.rs-total { background: linear-gradient(135deg, var(--col-primary), var(--col-primary-dark)); color: #000; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; }
    .rs-card.rs-total .rs-label { color: rgba(14,14,14,.8); }
    .rs-val { font-size: 2rem; font-weight: 700; line-height: 1.2; }
    .rs-label { font-size: .8rem; color: var(--col-text-2); margin-top: 2px; }
    .rs-title { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--col-text-2); margin-bottom: 10px; }
    .rs-stat-list { display: flex; flex-direction: column; gap: 4px; }
    .rs-stat-row { display: flex; justify-content: space-between; font-size: .82rem; padding: 2px 0; border-bottom: 1px solid var(--col-border-light); }
    .rs-stat-row:last-child { border-bottom: none; }
    .rs-stat-n { font-weight: 600; }

    .agent-photo { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--col-border-lg); flex-shrink: 0; }

    .print-header { text-align: center; margin-bottom: 16px; }

    @media print {
        .no-print { display: none !important; }
        .rapport-layout { display: block; }
        .print-header { display: block !important; }
    }
</style>

@endsection