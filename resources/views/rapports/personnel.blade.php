@extends('layouts.app')
@section('title', 'Rapports Personnel')
@section('page-title', 'Rapports du personnel')

@section('content')

<div class="rapport-tabs">
    <a href="{{ route('rapports.personnel') }}" class="rapport-tab rapport-tab-active">📋 Situation actuelle</a>
    <a href="{{ route('rapports.absents') }}" class="rapport-tab">🚫 Absents du jour</a>
    <a href="{{ route('rapports.historique') }}" class="rapport-tab">🕒 Rapport historique (année / mois)</a>
</div>

<div class="rapport-layout">

    {{-- ── Panneau filtres ──────────────────────────────────────── --}}
    <div class="rapport-filters">
        <div class="dash-card">
            <div class="card-header"><h3>Filtres du rapport</h3></div>

            <form method="GET" action="{{ route('rapports.personnel') }}" id="filtreForm">

                <div class="form-group">
                    <label>Service</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="service" id="f_service">
                            <option value="">Tous les services</option>
                            @foreach($services as $s)
                                <option value="{{ $s }}" {{ ($filters['service'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Corporation / Fonction</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="corporation" id="f_corp">
                            <option value="">Toutes les corporations</option>
                            @foreach($corporations as $c)
                                <option value="{{ $c }}" {{ ($filters['corporation'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Sexe</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="sexe" id="f_sexe">
                            <option value="">Hommes et Femmes</option>
                            <option value="M" {{ ($filters['sexe'] ?? '') === 'M' ? 'selected' : '' }}>👨 Hommes uniquement</option>
                            <option value="F" {{ ($filters['sexe'] ?? '') === 'F' ? 'selected' : '' }}>👩 Femmes uniquement</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Type de contrat</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="type_contrat" id="f_contrat">
                            <option value="">Tous les contrats</option>
                            @foreach($contrats as $c)
                                <option value="{{ $c }}" {{ ($filters['type_contrat'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Statut</label>
                    <div class="input-wrapper select-wrapper">
                        <select name="statut" id="f_statut">
                            <option value="">Tous les statuts</option>
                            <option value="actif"    {{ ($filters['statut'] ?? '') === 'actif'    ? 'selected' : '' }}>Actifs</option>
                            <option value="inactif"  {{ ($filters['statut'] ?? '') === 'inactif'  ? 'selected' : '' }}>Inactifs</option>
                            <option value="en_conge" {{ ($filters['statut'] ?? '') === 'en_conge' ? 'selected' : '' }}>En congé</option>
                            <option value="retraite" {{ ($filters['statut'] ?? '') === 'retraite' ? 'selected' : '' }}>Retraités</option>
                        </select>
                    </div>
                </div>

                <div class="rapport-filter-actions">
                    <button type="submit" class="btn-ghost btn-sm" style="width:100%">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Aperçu
                    </button>
                    @if(array_filter($filters))
                        <a href="{{ route('rapports.personnel') }}" class="btn-ghost btn-sm" style="width:100%;text-align:center">
                            ✕ Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bouton générer PDF --}}
        {{-- Bouton générer PDF --}}
@if($stats['total'] > 0)
<div class="dash-card" style="text-align:center;padding:18px">
    <div style="margin-bottom:12px">
        <span style="font-size:1.2rem;font-weight:700;color:var(--col-primary)">
            {{ $stats['total'] }}
        </span>
        <span style="font-size:.82rem;color:var(--col-text-2)">
            agent(s) correspondent aux filtres
        </span>
    </div>

    <div style="display:flex;gap:6px;font-size:.78rem;color:var(--col-text-2);justify-content:center;margin-bottom:14px">
        <span style="color:#2563eb">👨 {{ $stats['hommes'] }} H</span>
        <span style="color:#db2777">👩 {{ $stats['femmes'] }} F</span>
        <span>·</span>
        <span>📋 {{ $stats['cdi'] + $stats['cdd'] }} contrats</span>
    </div>

    <a href="{{ route('rapports.personnel.pdf', request()->only([
            'service',
            'corporation',
            'type_contrat',
            'sexe',
            'statut'
        ])) }}"
       target="_blank"
       class="btn-primary"
       style="width:100%;justify-content:center">

        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>

        📄 Générer le rapport PDF
    </a>
</div>

        @endif
    </div>

    {{-- ── Zone résultats ───────────────────────────────────────── --}}
    <div class="rapport-content">

        @if($stats['total'] === 0)
        <div class="dash-card" style="text-align:center;padding:48px">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3);display:block;margin:0 auto 16px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <p style="color:var(--col-text-2)">Aucun agent ne correspond aux filtres sélectionnés.</p>
            <a href="{{ route('rapports.personnel') }}" class="btn-ghost btn-sm" style="margin-top:12px">Réinitialiser les filtres</a>
        </div>
        @else

        {{-- BANDEAU DE RÉSUMÉ --}}
        <div class="result-summary mb-16">
            <div class="summary-left">
                <span class="summary-total">
                    <span class="summary-number">{{ $stats['total'] }}</span> agent(s) trouvé(s)
                    @if(array_filter($filters))
                        <span class="summary-filter-info">
                            @if(!empty($filters['service']))
                                · service <strong>{{ $filters['service'] }}</strong>
                            @endif
                            @if(!empty($filters['corporation']))
                                · <strong>{{ $filters['corporation'] }}</strong>
                            @endif
                            @if(!empty($filters['sexe']))
                                · {{ $filters['sexe'] === 'M' ? '👨 Hommes' : '👩 Femmes' }}
                            @endif
                            @if(!empty($filters['type_contrat']))
                                · contrat <strong>{{ $filters['type_contrat'] }}</strong>
                            @endif
                            @if(!empty($filters['statut']))
                                · statut <strong>{{ ucfirst(str_replace('_', ' ', $filters['statut'])) }}</strong>
                            @endif
                        </span>
                    @endif
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
                <div class="rs-label">Total agents</div>
            </div>

            {{-- Répartition H/F avec barres --}}
            <div class="rs-card">
                <div class="rs-title">📊 Répartition Sexe</div>
                <div class="rs-hf">
                    <div class="rs-hf-item">
                        <div class="rs-hf-val rs-h">👨 {{ $stats['hommes'] }}</div>
                        <div class="rs-hf-label">Hommes</div>
                        @if($stats['total'] > 0)
                        <div class="rs-hf-pct">{{ round($stats['hommes'] / $stats['total'] * 100) }}%</div>
                        @endif
                    </div>
                    <div class="rs-hf-sep"></div>
                    <div class="rs-hf-item">
                        <div class="rs-hf-val rs-f">👩 {{ $stats['femmes'] }}</div>
                        <div class="rs-hf-label">Femmes</div>
                        @if($stats['total'] > 0)
                        <div class="rs-hf-pct">{{ round($stats['femmes'] / $stats['total'] * 100) }}%</div>
                        @endif
                    </div>
                </div>
                @if($stats['total'] > 0)
                <div class="rs-bar-hf">
                    <div class="rs-bar-h" style="width:{{ round($stats['hommes'] / $stats['total'] * 100) }}%"></div>
                    <div class="rs-bar-f" style="width:{{ round($stats['femmes'] / $stats['total'] * 100) }}%"></div>
                </div>
                @endif
            </div>

            {{-- Statuts --}}
            <div class="rs-card">
                <div class="rs-title">📌 Statuts</div>
                <div class="rs-stat-list">
                    <div class="rs-stat-row"><span>🟢 Actifs</span><span class="rs-stat-n rs-actif">{{ $stats['actifs'] }}</span></div>
                    <div class="rs-stat-row"><span>🟡 En congé</span><span class="rs-stat-n rs-conge">{{ $stats['en_conge'] }}</span></div>
                    <div class="rs-stat-row"><span>🔴 Inactifs</span><span class="rs-stat-n rs-inactif">{{ $stats['inactifs'] }}</span></div>
                    <div class="rs-stat-row"><span>⚫ Retraités</span><span class="rs-stat-n">{{ $stats['retraites'] ?? 0 }}</span></div>
                </div>
            </div>

            {{-- Contrats --}}
            <div class="rs-card">
                <div class="rs-title">📋 Types de contrat</div>
                <div class="rs-stat-list">
                    <div class="rs-stat-row"><span>📄 CDI</span><span class="rs-stat-n">{{ $stats['cdi'] }}</span></div>
                    <div class="rs-stat-row"><span>📄 CDD</span><span class="rs-stat-n">{{ $stats['cdd'] }}</span></div>
                    @php 
                        $autres = $stats['total'] - $stats['cdi'] - $stats['cdd'];
                        $stagiaires = $stats['stagiaires'] ?? 0;
                        $prestataires = $stats['prestataires'] ?? 0;
                    @endphp
                    @if($stagiaires > 0)
                    <div class="rs-stat-row"><span>🎓 Stagiaires</span><span class="rs-stat-n">{{ $stagiaires }}</span></div>
                    @endif
                    @if($prestataires > 0)
                    <div class="rs-stat-row"><span>🤝 Prestataires</span><span class="rs-stat-n">{{ $prestataires }}</span></div>
                    @endif
                    @if($autres > 0)
                    <div class="rs-stat-row"><span>📌 Autres</span><span class="rs-stat-n">{{ $autres }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Aperçu liste avec photos --}}
        @if($apercu->isNotEmpty())
        <div class="dash-card p-0" style="margin-top:0">
            <div class="card-header" style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                <h3>👥 Aperçu ({{ $apercu->count() }} premiers résultats)</h3>
                <span style="font-size:.78rem;color:var(--col-text-2)">
                    📄 Le PDF contiendra tous les <strong>{{ $stats['total'] }}</strong> agents
                </span>
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
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apercu as $p)
                    <tr>
                        <td>
                            <div class="agent-cell">
                                @if($p->photo_url)
                                    <img src="{{ $p->photo_url }}" alt="{{ $p->nom_complet }}" class="agent-photo">
                                @else
                                    <div class="agent-avatar av-sm">{{ $p->initiales }}</div>
                                @endif
                                <div>
                                    <div class="agent-name">{{ $p->nom_complet }}</div>
                                    <div class="agent-meta">{{ $p->sexe === 'M' ? 'H' : 'F' }} · {{ $p->telephone ?: '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $p->sexe === 'F' ? '👩 Femme' : '👨 Homme' }}</td>
                        <td style="font-size:.8rem">{{ $p->service ?: '—' }}</td>
                        <td style="font-size:.8rem">{{ $p->corporation ?: '—' }}</td>
                        <td>@if($p->type_contrat_actuel)<span class="contrat-tag contrat-{{ strtolower($p->type_contrat_actuel) }}">{{ $p->type_contrat_actuel }}</span>@else —@endif</td>
                        <td><span class="statut-pill statut-{{ $p->statut }}">{{ $p->statut_label }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @if($stats['total'] > 5)
            <div style="padding:12px 20px;font-size:.8rem;color:var(--col-text-2);border-top:1px solid var(--col-border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:4px">
                <span>➕ {{ $stats['total'] - 5 }} agent(s) supplémentaire(s) dans le PDF filtré</span>
                <a href="{{ route('rapports.personnel.pdf', $filters) }}"
                   target="_blank" class="btn-ghost btn-sm" style="padding:4px 12px">
                    📄 Voir tout le rapport PDF filtré
                </a>
            </div>
            @endif
        </div>
        @endif

        @endif
    </div>
</div>

{{-- Styles additionnels --}}
<style>
    .rapport-tabs { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
    .rapport-tab {
        padding: 9px 16px; border-radius: 8px; font-size: .85rem; font-weight: 600;
        color: var(--col-text-2); background: var(--col-bg-2); border: 1px solid var(--col-border-lg);
        text-decoration: none; transition: background .15s, color .15s;
    }
    .rapport-tab:hover { background: var(--col-border); }
    .rapport-tab-active { background: var(--col-primary); color: #fff; border-color: var(--col-primary); }

    .rapport-layout {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .rapport-layout {
            grid-template-columns: 1fr;
        }
    }

    .rapport-filters {
        position: sticky;
        top: 20px;
    }

    .rapport-filters .dash-card {
        margin-bottom: 16px;
    }

    .rapport-filters .dash-card:last-child {
        margin-bottom: 0;
    }

    .rapport-filter-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 16px;
    }

    .result-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--col-bg-2);
        padding: 12px 20px;
        border-radius: var(--radius);
        border: 1px solid var(--col-border-lg);
        flex-wrap: wrap;
        gap: 8px;
    }

    .summary-total {
        font-size: 0.95rem;
        color: var(--col-text-2);
    }

    .summary-number {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--col-text-1);
    }

    .summary-filter-info {
        font-size: 0.85rem;
        color: var(--col-text-3);
        margin-left: 4px;
    }

    .summary-filter-info strong {
        color: var(--col-text-1);
    }

    .summary-gender {
        display: flex;
        gap: 16px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .gender-male {
        color: #2563eb;
        background: rgba(37, 99, 235, 0.1);
        padding: 2px 12px;
        border-radius: 20px;
    }

    .gender-female {
        color: #db2777;
        background: rgba(219, 39, 119, 0.1);
        padding: 2px 12px;
        border-radius: 20px;
    }

    .mb-16 {
        margin-bottom: 16px;
    }

    .rapport-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .rs-card {
        background: var(--col-bg-1);
        border: 1px solid var(--col-border-lg);
        border-radius: var(--radius);
        padding: 16px;
    }

    .rs-card.rs-total {
        background: linear-gradient(135deg, var(--col-primary), var(--col-primary-dark));
        color: black;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .rs-card.rs-total .rs-label {
        color: rgba(14, 14, 14, 0.8);
    }

    .rs-val {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .rs-label {
        font-size: 0.8rem;
        color: var(--col-text-2);
        margin-top: 2px;
    }

    .rs-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--col-text-2);
        margin-bottom: 10px;
    }

    .rs-hf {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .rs-hf-item {
        flex: 1;
        text-align: center;
    }

    .rs-hf-val {
        font-size: 1.3rem;
        font-weight: 700;
    }

    .rs-hf-val.rs-h { color: #2563eb; }
    .rs-hf-val.rs-f { color: #db2777; }

    .rs-hf-label {
        font-size: 0.7rem;
        color: var(--col-text-2);
    }

    .rs-hf-pct {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--col-text-2);
    }

    .rs-hf-sep {
        width: 1px;
        background: var(--col-border);
        height: 30px;
    }

    .rs-bar-hf {
        display: flex;
        height: 6px;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 8px;
        background: var(--col-bg-2);
    }

    .rs-bar-h {
        background: #2563eb;
        transition: width 0.3s;
    }

    .rs-bar-f {
        background: #db2777;
        transition: width 0.3s;
    }

    .rs-stat-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .rs-stat-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        padding: 2px 0;
        border-bottom: 1px solid var(--col-border-light);
    }

    .rs-stat-row:last-child {
        border-bottom: none;
    }

    .rs-stat-n {
        font-weight: 600;
    }

    .rs-stat-n.rs-actif { color: #16a34a; }
    .rs-stat-n.rs-conge { color: #f59e0b; }
    .rs-stat-n.rs-inactif { color: #dc2626; }

    .agent-photo {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--col-border-lg);
        flex-shrink: 0;
    }
</style>

@endsection