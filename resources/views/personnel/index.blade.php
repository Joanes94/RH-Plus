@extends('layouts.app')
@section('title', 'Personnel')
@section('page-title', 'Gestion du personnel')

@section('content')

{{-- Stats rapides --}}
<div class="kpi-grid kpi-grid-4 mb-24">
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['total'] }}</div><div class="kpi-label">Total personnel</div></div>
    </div>
    <div class="kpi-card kpi-green">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['actifs'] }}</div><div class="kpi-label">Actifs</div></div>
    </div>
    <div class="kpi-card kpi-amber">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['conges'] }}</div><div class="kpi-label">En congé</div></div>
    </div>
    <div class="kpi-card kpi-purple">
        <div class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
        <div class="kpi-body"><div class="kpi-value">{{ $stats['inactifs'] }}</div><div class="kpi-label">Inactifs</div></div>
    </div>
</div>

{{-- Barre d'actions avec tous les filtres --}}
<div class="table-toolbar">
    <form method="GET" class="toolbar-search">
        {{-- Recherche --}}
        <div class="input-wrapper search-input">
            <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher un agent…">
        </div>

        @if(auth()->user()->isGlobal())
        <div class="filter-group">
            <select name="centre_id" onchange="this.form.submit()" class="form-control form-control-sm">
                <option value="">Tous les centres</option>
                @foreach(\App\Models\Centre::actifs()->orderBy('nom')->get() as $centre)
                <option value="{{ $centre->id }}" {{ request('centre_id') == $centre->id ? 'selected' : '' }}>{{ $centre->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Filtre Service --}}
        <div class="input-wrapper select-wrapper filter-select">
            <select name="service">
                <option value="">Tous les services</option>
                @foreach($services as $s)
                    <option value="{{ $s }}" {{ ($filters['service'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtre Corporation (titre de fonction) --}}
        <div class="input-wrapper select-wrapper filter-select">
            <select name="corporation">
                <option value="">Toutes les corporations</option>
                @foreach($corporations as $c)
                    <option value="{{ $c }}" {{ ($filters['corporation'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtre Type de contrat --}}
        <div class="input-wrapper select-wrapper filter-select">
            <select name="contrat">
                <option value="">Tous les contrats</option>
                @foreach(\App\Models\Personnel::typesContrat() as $c)
                    <option value="{{ $c }}" {{ ($filters['contrat'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtre Statut --}}
        <div class="input-wrapper select-wrapper filter-select">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="actif"    {{ ($filters['statut'] ?? '') === 'actif'    ? 'selected' : '' }}>Actif</option>
                <option value="inactif"  {{ ($filters['statut'] ?? '') === 'inactif'  ? 'selected' : '' }}>Inactif</option>
                <option value="en_conge" {{ ($filters['statut'] ?? '') === 'en_conge' ? 'selected' : '' }}>En congé</option>
                <option value="retraite" {{ ($filters['statut'] ?? '') === 'retraite' ? 'selected' : '' }}>Retraité</option>
            </select>
        </div>

        <button type="submit" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Filtrer
        </button>
        @if(array_filter($filters))
            <a href="{{ route('personnel.index') }}" class="btn-ghost btn-sm">✕ Réinitialiser</a>
        @endif
    </form>

    <div class="toolbar-actions">
      <form method="POST" action="{{ route('avancements.verifier') }}" class="d-inline">
          @csrf
          <button type="submit" class="btn-ghost btn-sm" title="Vérifie les avancements d'échelon (24 mois) et les bonifications (58 ans) dus aujourd'hui">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
              Vérifier les avancements
          </button>
      </form>
      <a href="{{ route('personnel.anciens') }}" class="btn-ghost btn-sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
          Anciens travailleurs
      </a>
      @if(auth()->user()->isCRH())
        <a href="{{ route('personnel.import.form') }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import Excel
        </a>
        <a href="{{ route('contrats.import.form') }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 12 15 15"/></svg>
            Import Contrat
        </a>
        <a href="{{ route('contrats.assign-multiple.form') }}" class="btn-ghost btn-sm" style="background:#e0e7ff; color:#3730a3; border-color:#c7d2fe;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Assigner Contrats
        </a>
        <a href="{{ route('personnel.create') }}" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter un agent
        </a>
      @endif
    </div>
</div>

{{-- BANDEAU DE RÉSUMÉ DES RÉSULTATS --}}
@if(!$personnels->isEmpty())
<div class="result-summary mb-16">
    <div class="summary-left">
        <span class="summary-total">
            <span class="summary-number">{{ $personnels->total() }}</span> agent(s) trouvé(s)
            @if(array_filter($filters))
                <span class="summary-filter-info">
                    @if(!empty($filters['search']))
                        · recherche "{{ $filters['search'] }}"
                    @endif
                    @if(!empty($filters['service']))
                        · service <strong>{{ $filters['service'] }}</strong>
                    @endif
                    @if(!empty($filters['corporation']))
                        · <strong>{{ $filters['corporation'] }}</strong>
                    @endif
                    @if(!empty($filters['contrat']))
                        · contrat <strong>{{ $filters['contrat'] }}</strong>
                    @endif
                    @if(!empty($filters['statut']))
                        · statut <strong>{{ ucfirst(str_replace('_', ' ', $filters['statut'])) }}</strong>
                    @endif
                </span>
            @endif
        </span>
    </div>
    <div class="summary-right">
        @php
            // Calcul des statistiques de genre sur les résultats filtrés
            $hommes = $personnels->filter(fn($p) => $p->sexe === 'M')->count();
            $femmes = $personnels->filter(fn($p) => $p->sexe === 'F')->count();
        @endphp
        <span class="summary-gender">
            <span class="gender-male">♂️ {{ $hommes }} homme{{ $hommes > 1 ? 's' : '' }}</span>
            <span class="gender-female">♀️ {{ $femmes }} femme{{ $femmes > 1 ? 's' : '' }}</span>
        </span>
    </div>
</div>
@endif

{{-- Tableau --}}
<div class="dash-card p-0">
    @if($personnels->isEmpty())
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p>
                @if(array_filter($filters))
                    Aucun agent ne correspond à vos critères.
                @else
                    Aucun personnel trouvé.
                @endif
            </p>
            @if(array_filter($filters))
                <a href="{{ route('personnel.index') }}" class="btn-ghost btn-sm">Réinitialiser les filtres</a>
            @else
                <a href="{{ route('personnel.create') }}" class="btn-primary btn-sm">Ajouter le premier agent</a>
            @endif
        </div>
    @else
    <div class="table-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Corporation</th>
                <th>Service</th>
                <th>Contrat</th>
                <th>Catégorie/Échelon</th>
                <th>Embauche centre</th>
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
                            <img src="{{ $p->avatar_url }}" alt="{{ $p->initiales }}" class="agent-photo">
                        @endif
                        <div>
                            <div class="agent-name">{{ $p->nom_complet }}</div>
                            <div class="agent-meta">{{ $p->sexe === 'M' ? 'H' : 'F' }} · {{ $p->telephone ?: '—' }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="text-truncate-cell" title="{{ $p->corporation }}">{{ $p->corporation ?: '—' }}</span></td>
                <td><span class="text-truncate-cell" title="{{ $p->service }}">{{ $p->service ?: '—' }}</span></td>
                <td>
                    @if($p->type_contrat_actuel)
                        <span class="contrat-tag contrat-{{ strtolower($p->type_contrat_actuel) }}">{{ $p->type_contrat_actuel }}</span>
                    @else —
                    @endif
                </td>
                <td class="mono-text">
                    @if($p->contrat_actif?->categorie && $p->contrat_actif?->echelon)
                        {{ $p->contrat_actif->categorie }}-{{ $p->contrat_actif->echelon }}
                    @else —
                    @endif
                </td>
                <td>{{ $p->date_embauche_centre ? $p->date_embauche_centre->format('d/m/Y') : '—' }}</td>
                <td>
                    <span class="statut-pill statut-{{ $p->statut }}">{{ $p->statut_label }}</span>
                </td>
                <td class="td-actions">
                    <a href="{{ route('personnel.show', $p) }}" class="icon-btn" title="Voir la fiche">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    @if(!auth()->user()->isReadOnly())
                    <a href="{{ route('personnel.edit', $p) }}" class="icon-btn" title="Modifier">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    @if($personnels->hasPages())
    <div class="pagination-bar">
        <span class="pag-info">
            {{ $personnels->firstItem() }}–{{ $personnels->lastItem() }} sur {{ $personnels->total() }} agents
        </span>
        {{ $personnels->links('vendor.pagination.simple') }}
    </div>
    @endif
    @endif
</div>

{{-- Styles additionnels --}}
<style>
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
</style>

@endsection