@extends('layouts.app')

@section('title', 'Validation des avancements')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-heading" style="font-size: 1.8rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem 0;">Avancements & Bonifications</h1>
        <p class="page-subtitle" style="font-size: 0.9rem; color: #6b7280; margin: 0;">Validation officielle par la DDIS et suivi de l'historique des carrières.</p>
    </div>
</div>

{{-- Zone KPI Synthétiques Premium --}}
<div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="kpi-card" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; transition: transform 0.2s;">
        <div style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            ⏳
        </div>
        <div>
            <div style="font-size: 0.78rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">En attente</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937; line-height: 1.2;">{{ $avancements->where('statut', 'soumis')->count() }}</div>
        </div>
    </div>
    
    <div class="kpi-card" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; transition: transform 0.2s;">
        <div style="background: rgba(16, 185, 129, 0.1); color: #059669; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            ✅
        </div>
        <div>
            <div style="font-size: 0.78rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Validés (Ce mois)</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937; line-height: 1.2;">{{ $avancements->where('statut', 'valide')->count() }}</div>
        </div>
    </div>

    <div class="kpi-card" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; transition: transform 0.2s;">
        <div style="background: rgba(239, 68, 68, 0.1); color: #dc2626; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            ❌
        </div>
        <div>
            <div style="font-size: 0.78rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Rejetés</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937; line-height: 1.2;">{{ $avancements->where('statut', 'rejete')->count() }}</div>
        </div>
    </div>
</div>

{{-- Barre de Filtres Modernisée --}}
<div class="filters-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <form method="GET" action="{{ route('avancements.index') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="font-weight: 600; font-size: 0.88rem; color: #374151; display: inline-flex; align-items: center; gap: 0.35rem;">
            🔍 Filtrer par :
        </div>
        <div class="select-wrapper">
            <select name="statut" class="filter-select" onchange="this.form.submit()">
                <option value="soumis" {{ request('statut', 'soumis') == 'soumis' ? 'selected' : '' }}>En attente de validation</option>
                <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validés (Historique)</option>
                <option value="rejete" {{ request('statut') == 'rejete' ? 'selected' : '' }}>Rejetés</option>
            </select>
        </div>
        <div class="select-wrapper">
            <select name="type" class="filter-select" onchange="this.form.submit()">
                <option value="">Tous les types d'avancement</option>
                <option value="bonification" {{ request('type') == 'bonification' ? 'selected' : '' }}>Bonification (58 ans)</option>
                <option value="echelon" {{ request('type') == 'echelon' ? 'selected' : '' }}>Avancement d'échelon</option>
            </select>
        </div>
    </form>
</div>

{{-- Tableau Principal --}}
<div class="card premium-card">
    <div class="table-responsive">
        <table class="table premium-table">
            <thead>
                <tr>
                    <th>Salarié</th>
                    <th>Centre Hospitalier</th>
                    <th>Type de mesure</th>
                    <th>Référence Doc</th>
                    <th class="text-right">Ancien Salaire</th>
                    <th class="text-right">Nouveau Salaire</th>
                    <th class="text-center">Date Effet</th>
                    <th class="text-center">Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avancements as $avancement)
                <tr>
                    <td>
                        <div class="agent-profile">
                            <div class="agent-avatar">{{ substr($avancement->personnel->nom, 0, 1) }}{{ substr($avancement->personnel->prenoms, 0, 1) }}</div>
                            <div>
                                <div class="agent-name">{{ $avancement->personnel->nom_complet }}</div>
                                <div class="agent-subtext">{{ $avancement->personnel->corporation }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($avancement->personnel->centre)
                            <span class="centre-pill">{{ $avancement->personnel->centre->nom }}</span>
                        @else
                            <span class="text-muted" style="font-style: italic;">Hors centre</span>
                        @endif
                    </td>
                    <td>
                        <span class="type-tag type-{{ $avancement->type }}">
                            {{ $avancement->type_label }}
                        </span>
                    </td>
                    <td style="font-family: monospace; font-size: 0.8rem; color: #4b5563;">
                        {{ $avancement->numero_reference }}
                    </td>
                    <td class="text-right text-muted" style="font-weight: 500;">
                        {{ number_format($avancement->ancien_salaire, 0, ',', ' ') }} F
                    </td>
                    <td class="text-right text-gain" style="font-weight: 700;">
                        {{ number_format($avancement->nouveau_salaire, 0, ',', ' ') }} F
                    </td>
                    <td class="text-center text-muted" style="font-size: 0.82rem;">
                        {{ $avancement->date_effet?->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        <span class="status-pill status-{{ $avancement->statut }}">
                            {{ $avancement->statut_label }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div style="display: inline-flex; gap: 0.4rem; justify-content: flex-end; align-items: center;">
                            @if($avancement->isSoumis() && auth()->user()->isDDIS())
                                <form method="POST" action="{{ route('avancements.approuver', $avancement) }}" style="display: inline;" onsubmit="return confirm('Valider et appliquer officiellement cet avancement de salaire ?')">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn-success" title="Approuver l'avancement">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        Valider
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('avancements.rejeter', $avancement) }}" style="display: inline;" onsubmit="return confirm('Rejeter cette demande d\'avancement de salaire ?')">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn-danger" title="Rejeter l'avancement">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        Rejeter
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('avancements.document', $avancement) }}" target="_blank" class="action-btn action-btn-outline" title="Voir le courrier officiel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Lettre
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af; padding: 4rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📁</div>
                        Aucune demande d'avancement de salaire en attente ou enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($avancements->hasPages())
    <div class="pagination-footer" style="padding: 1.25rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: center;">
        {{ $avancements->withQueryString()->links('vendor.pagination.simple') }}
    </div>
    @endif
</div>

@push('styles')
<style>
/* CSS Refontes Avancements Premium */
.premium-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    overflow: hidden;
}

.premium-table {
    width: 100%;
    border-collapse: collapse;
}

.premium-table th {
    background-color: #f9fafb;
    border-bottom: 1.5px solid #e5e7eb;
    color: #4b5563;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 1.25rem;
}

.premium-table td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.premium-table tr:hover td {
    background-color: #f9fafb;
}

/* Agent Avatar & Profile info */
.agent-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.agent-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #1a5c45 0%, #2e7d62 100%);
    color: #ffffff;
    font-weight: bold;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}

.agent-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #111827;
    line-height: 1.3;
}

.agent-subtext {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Centre tag */
.centre-pill {
    font-size: 0.75rem;
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 500;
}

/* Type tags */
.type-tag {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-block;
    border: 1px solid transparent;
}
.type-bonification {
    background: #f5f3ff;
    color: #5b21b6;
    border-color: #ddd6fe;
}
.type-echelon {
    background: #eff6ff;
    color: #1e40af;
    border-color: #bfdbfe;
}

/* Status pills */
.status-pill {
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 99px;
    font-weight: 600;
    display: inline-block;
}
.status-soumis {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fef3c7;
}
.status-valide {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.status-rejete {
    background: #fdf2f2;
    color: #9b1c1c;
    border: 1px solid #fde8e8;
}

/* Gain values styling */
.text-gain {
    color: var(--col-primary, #1a5c45);
}

/* Custom dropdown selectors */
.filter-select {
    padding: 0.45rem 2.25rem 0.45rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
    background-color: #fff;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}
.filter-select:focus {
    border-color: var(--col-primary, #1a5c45);
    box-shadow: 0 0 0 3px rgba(26, 92, 69, 0.15);
    outline: none;
}

/* Premium micro buttons */
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 5px 10px;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid transparent;
    text-decoration: none;
}
.action-btn-success {
    background-color: #10b981;
    color: #ffffff;
}
.action-btn-success:hover {
    background-color: #059669;
    transform: translateY(-1px);
}
.action-btn-danger {
    background-color: #ef4444;
    color: #ffffff;
}
.action-btn-danger:hover {
    background-color: #dc2626;
    transform: translateY(-1px);
}
.action-btn-outline {
    background-color: #ffffff;
    color: #4b5563;
    border-color: #d1d5db;
}
.action-btn-outline:hover {
    background-color: #f9fafb;
    border-color: #9ca3af;
    color: #111827;
}

.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08);
}
</style>
@endpush
@endsection
