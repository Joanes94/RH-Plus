@extends('layouts.app')

@section('title', 'Validation des avancements')
@section('page-title', 'Validation des avancements')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-heading">Avancements & Bonifications</h1>
        <p class="page-subtitle">Validation par la DDIS et historique</p>
    </div>
</div>

{{-- Filtres --}}
<div class="filters-bar" style="margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('avancements.index') }}" class="filters-form" style="display: flex; gap: 0.75rem;">
        <div class="filter-group">
            <select name="statut" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="soumis" {{ request('statut', 'soumis') == 'soumis' ? 'selected' : '' }}>En attente de validation</option>
                <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validés (Historique)</option>
                <option value="rejete" {{ request('statut') == 'rejete' ? 'selected' : '' }}>Rejetés</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous les types</option>
                <option value="bonification" {{ request('type') == 'bonification' ? 'selected' : '' }}>Bonification (58 ans)</option>
                <option value="echelon" {{ request('type') == 'echelon' ? 'selected' : '' }}>Avancement d'échelon</option>
            </select>
        </div>
    </form>
</div>

<div class="card card-table">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Centre</th>
                        <th>Type</th>
                        <th>Référence</th>
                        <th class="text-right">Ancien salaire</th>
                        <th class="text-right">Nouveau salaire</th>
                        <th class="text-center">Date effet</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avancements as $avancement)
                    <tr>
                        <td>
                            <strong>{{ $avancement->personnel->nom_complet }}</strong>
                            <div class="text-muted text-sm">{{ $avancement->personnel->corporation }}</div>
                        </td>
                        <td>
                            @if($avancement->personnel->centre)
                                <span class="centre-tag">{{ $avancement->personnel->centre->nom }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="type-badge type-{{ $avancement->type }}">
                                {{ $avancement->type_label }}
                            </span>
                        </td>
                        <td class="text-muted text-sm" style="font-family: monospace;">
                            {{ $avancement->numero_reference }}
                        </td>
                        <td class="text-right text-muted">
                            {{ number_format($avancement->ancien_salaire, 0, ',', ' ') }} F
                        </td>
                        <td class="text-right font-semibold text-primary">
                            {{ number_format($avancement->nouveau_salaire, 0, ',', ' ') }} F
                        </td>
                        <td class="text-center text-muted">
                            {{ $avancement->date_effet?->format('d/m/Y') }}
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-{{ $avancement->statut }}">
                                {{ $avancement->statut_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                @if($avancement->isSoumis() && auth()->user()->isDDIS())
                                    <form method="POST" action="{{ route('avancements.approuver', $avancement) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-xs" onclick="return confirm('Valider et appliquer cette bonification ?')">
                                            Valider
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('avancements.rejeter', $avancement) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Rejeter cette bonification ?')">
                                            Rejeter
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('avancements.document', $avancement) }}" target="_blank" class="btn btn-outline btn-xs" title="Voir le document">
                                    Lettre
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 2.5rem;">
                            Aucun avancement en attente.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 1.25rem;">
            {{ $avancements->withQueryString()->links('vendor.pagination.simple') }}
        </div>
    </div>
</div>

@push('styles')
<style>
.type-badge { font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 600; }
.type-bonification { background: rgba(139,92,246,0.1); color: #6d28d9; }
.type-echelon { background: rgba(59,130,246,0.1); color: #1d4ed8; }

.status-badge { font-size: 0.72rem; padding: 2px 8px; border-radius: 99px; font-weight: 600; }
.status-soumis { background: rgba(245,158,11,0.1); color: #b45309; }
.status-approuve { background: rgba(16,185,129,0.1); color: #047857; }
.status-rejete { background: rgba(239,68,68,0.1); color: #b91c1c; }

.centre-tag { font-size: 0.75rem; background: #f3f4f6; padding: 2px 8px; border-radius: 6px; color: #374151; }

.btn-xs { padding: 4px 8px; font-size: 0.72rem; border-radius: 6px; }
.btn-primary.btn-xs { background: var(--col-primary, #1a5c45); color: #fff; border: none; cursor: pointer; }
.btn-primary.btn-xs:hover { background: #134433; }
.btn-danger.btn-xs { background: #ef4444; color: #fff; border: none; cursor: pointer; }
.btn-danger.btn-xs:hover { background: #dc2626; }
.btn-outline.btn-xs { border: 1px solid #d1d5db; background: transparent; color: #374151; cursor: pointer; text-decoration: none; }
.btn-outline.btn-xs:hover { background: #f9fafb; }
</style>
@endpush
@endsection
