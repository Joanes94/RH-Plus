@extends('layouts.app')

@section('title', 'Validation des avancements')

@section('content')
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-heading" style="font-size: 1.75rem; font-weight: 800; color: #111827; margin: 0 0 0.25rem 0; letter-spacing: -0.02em;">Validations & Avancements de Carrière</h1>
        <p class="page-subtitle" style="font-size: 0.88rem; color: #6b7280; margin: 0;">Validation officielle par la DDIS des avancements d'échelon et bonifications d'ancienneté.</p>
    </div>
</div>

{{-- KPI Synthétiques Interactifs --}}
<div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <a href="{{ route('avancements.index', array_merge(request()->query(), ['statut' => 'soumis'])) }}" class="kpi-card {{ request('statut', 'soumis') == 'soumis' ? 'active-kpi' : '' }}" style="background: #ffffff; border: 2px solid {{ request('statut', 'soumis') == 'soumis' ? '#f59e0b' : '#e5e7eb' }}; border-radius: 14px; padding: 1.1rem 1.25rem; text-decoration: none; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.03); transition: all 0.2s ease;">
        <div style="background: #fef3c7; color: #d97706; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
            ⏳
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">En attente DDIS</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #111827; line-height: 1.1; margin-top: 2px;">{{ $countSoumis }}</div>
        </div>
    </a>
    
    <a href="{{ route('avancements.index', array_merge(request()->query(), ['statut' => 'valide'])) }}" class="kpi-card {{ request('statut') == 'valide' ? 'active-kpi' : '' }}" style="background: #ffffff; border: 2px solid {{ request('statut') == 'valide' ? '#10b981' : '#e5e7eb' }}; border-radius: 14px; padding: 1.1rem 1.25rem; text-decoration: none; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.03); transition: all 0.2s ease;">
        <div style="background: #d1fae5; color: #059669; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
            ✅
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Validés (Ce mois)</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #111827; line-height: 1.1; margin-top: 2px;">{{ $countValide }}</div>
        </div>
    </a>

    <a href="{{ route('avancements.index', array_merge(request()->query(), ['statut' => 'rejete'])) }}" class="kpi-card {{ request('statut') == 'rejete' ? 'active-kpi' : '' }}" style="background: #ffffff; border: 2px solid {{ request('statut') == 'rejete' ? '#ef4444' : '#e5e7eb' }}; border-radius: 14px; padding: 1.1rem 1.25rem; text-decoration: none; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.03); transition: all 0.2s ease;">
        <div style="background: #fee2e2; color: #dc2626; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
            ❌
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Rejetés</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #111827; line-height: 1.1; margin-top: 2px;">{{ $countRejete }}</div>
        </div>
    </a>
</div>

{{-- Barre de Filtres Moderne --}}
<div class="filters-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <form method="GET" action="{{ route('avancements.index') }}" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <div style="font-weight: 700; font-size: 0.85rem; color: #374151; display: inline-flex; align-items: center; gap: 0.35rem;">
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
        <div class="select-wrapper">
            <select name="centre_id" class="filter-select" onchange="this.form.submit()">
                <option value="">Tous les centres</option>
                @foreach($centres as $c)
                    <option value="{{ $c->id }}" {{ request('centre_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="input-wrapper">
            <input type="month" name="mois" class="filter-select" value="{{ request('mois') }}" onchange="this.form.submit()" title="Filtrer par mois d'effet" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.85rem;">
        </div>
        @if(request()->anyFilled(['centre_id', 'mois', 'type', 'statut']))
            <a href="{{ route('avancements.index') }}" style="font-size: 0.82rem; color: #ef4444; font-weight: 600; text-decoration: underline; margin-left: auto;">Réinitialiser</a>
        @endif
    </form>
</div>

{{-- Conteneur du Tableau Responsive --}}
<div class="card premium-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.03); overflow: hidden;">
    <div style="overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch;">
        <table class="table premium-table" style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 1.5px solid #e5e7eb;">
                    <th style="padding: 1rem 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 180px;">Salarié</th>
                    <th style="padding: 1rem 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 140px;">Centre</th>
                    <th style="padding: 1rem 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 150px;">Type de Mesure</th>
                    <th style="padding: 1rem 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 140px;">Réf. Courrier</th>
                    <th style="padding: 1rem 1.25rem; text-align: right; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 110px;">Ancien Sal.</th>
                    <th style="padding: 1rem 1.25rem; text-align: right; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 120px;">Nouveau Sal.</th>
                    <th style="padding: 1rem 1.25rem; text-align: center; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 100px;">Date Effet</th>
                    <th style="padding: 1rem 1.25rem; text-align: center; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 130px;">Statut</th>
                    <th style="padding: 1rem 1.25rem; text-align: right; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4b5563; min-width: 270px; position: sticky; right: 0; background: #f9fafb; box-shadow: -4px 0 8px rgba(0,0,0,0.02);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avancements as $avancement)
                <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.15s ease;">
                    {{-- Salarié --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #1a5c45 0%, #2e7d62 100%); color: #ffffff; font-weight: 800; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                {{ substr($avancement->personnel->nom, 0, 1) }}{{ substr($avancement->personnel->prenoms, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; font-weight: 700; color: #111827;">{{ $avancement->personnel->nom_complet }}</div>
                                <div style="font-size: 0.75rem; color: #6b7280;">{{ $avancement->personnel->corporation }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Centre Hospitalier --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle;">
                        @if($avancement->personnel->centre)
                            <span style="display: inline-block; font-size: 0.75rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 8px; font-weight: 600; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                🏥 {{ $avancement->personnel->centre->nom }}
                            </span>
                        @else
                            <span style="color: #9ca3af; font-style: italic; font-size: 0.8rem;">—</span>
                        @endif
                    </td>

                    {{-- Type de mesure --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle;">
                        @if($avancement->type === 'bonification')
                            <span style="display: inline-block; font-size: 0.75rem; background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; padding: 4px 10px; border-radius: 8px; font-weight: 700;">
                                🎁 Bonification (58 ans)
                            </span>
                        @else
                            <span style="display: inline-block; font-size: 0.75rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 8px; font-weight: 700;">
                                📈 Échelon (+2 ans)
                            </span>
                        @endif
                    </td>

                    {{-- Réf Courrier --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle; font-family: monospace; font-size: 0.78rem; color: #4b5563; white-space: nowrap;">
                        {{ $avancement->numero_reference }}
                    </td>

                    {{-- Ancien Salaire --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle; text-align: right; font-size: 0.88rem; color: #6b7280; font-weight: 500; white-space: nowrap;">
                        {{ number_format($avancement->ancien_salaire, 0, ',', ' ') }} F
                    </td>

                    {{-- Nouveau Salaire --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle; text-align: right; font-size: 0.92rem; color: #047857; font-weight: 800; white-space: nowrap;">
                        {{ number_format($avancement->nouveau_salaire, 0, ',', ' ') }} F
                    </td>

                    {{-- Date d'effet --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle; text-align: center; font-size: 0.82rem; color: #4b5563; font-weight: 600; white-space: nowrap;">
                        {{ $avancement->date_effet?->format('d/m/Y') }}
                    </td>

                    {{-- Statut --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle; text-align: center; white-space: nowrap;">
                        @if($avancement->statut === 'soumis')
                            @if($avancement->type === 'bonification')
                                <span style="display: inline-block; font-size: 0.75rem; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 99px; font-weight: 700;">
                                    ⏳ Attente validation CRH
                                </span>
                            @else
                                <span style="display: inline-block; font-size: 0.75rem; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 99px; font-weight: 700;">
                                    ⏳ Attente validation Centre
                                </span>
                            @endif
                        @elseif($avancement->statut === 'valide_crh')
                            <span style="display: inline-block; font-size: 0.75rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 99px; font-weight: 700;">
                                ⌛ Pré-validé CRH (Attente DDIS)
                            </span>
                        @elseif($avancement->statut === 'valide')
                            <span style="display: inline-block; font-size: 0.75rem; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 99px; font-weight: 700;">
                                ✓ Validé {{ $avancement->type === 'bonification' ? 'et Signé (DDIS)' : '(Centre)' }}
                            </span>
                        @else
                            <span style="display: inline-block; font-size: 0.75rem; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 4px 10px; border-radius: 99px; font-weight: 700;">
                                ✖ Rejeté
                            </span>
                        @endif
                    </td>

                    {{-- Actions (Valider / Rejeter / Lettre) --}}
                    <td style="padding: 1rem 1.25rem; vertical-align: middle; text-align: right; white-space: nowrap; position: sticky; right: 0; background: #ffffff; box-shadow: -4px 0 8px rgba(0,0,0,0.02);">
                        <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                            {{-- Action Avancement Échelon par le Centre --}}
                            @if($avancement->type === 'echelon' && $avancement->statut === 'soumis' && !auth()->user()->isReadOnly())
                                <form method="POST" action="{{ route('avancements.approuver', $avancement) }}" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir valider cet avancement d\'échelon au niveau du centre ?')">
                                    @csrf
                                    <button type="submit" style="padding: 6px 12px; font-weight: 700; font-size: 0.8rem; background: #059669; color: #ffffff; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(5,150,105,0.25);" title="Valider l'avancement d'échelon au niveau du centre">
                                        ✓ Valider (Centre)
                                    </button>
                                </form>
                            @endif

                            {{-- Étape 1 Bonification : Pré-validation CRH --}}
                            @if($avancement->type === 'bonification' && $avancement->statut === 'soumis' && (auth()->user()->isCRH() || auth()->user()->isGlobal()))
                                <form method="POST" action="{{ route('avancements.valider-crh', $avancement) }}" style="margin: 0;" onsubmit="return confirm('Confirmer la pré-validation par le CRH pour transmission à la DDIS ?')">
                                    @csrf
                                    <button type="submit" style="padding: 6px 12px; font-weight: 700; font-size: 0.8rem; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(37,99,235,0.25);" title="Pré-valider la bonification en tant que CRH">
                                        ✓ Pré-valider (CRH)
                                    </button>
                                </form>
                            @endif

                            {{-- Étape 2 Bonification : Validation finale DDIS avec signature --}}
                            @if($avancement->type === 'bonification' && $avancement->statut === 'valide_crh' && (auth()->user()->isDDIS() || auth()->user()->isGlobal()))
                                <form method="POST" action="{{ route('avancements.approuver', $avancement) }}" style="margin: 0;" onsubmit="return confirm('Confirmer la validation officielle et l\'imposition de la signature de la DDIS ?')">
                                    @csrf
                                    <button type="submit" style="padding: 6px 12px; font-weight: 700; font-size: 0.8rem; background: #7c3aed; color: #ffffff; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(124,58,237,0.25);" title="Valider et imposer la signature DDIS">
                                        ✍️ Signer & Valider (DDIS)
                                    </button>
                                </form>
                            @endif

                            @if(($avancement->statut === 'soumis' || $avancement->statut === 'valide_crh') && !auth()->user()->isReadOnly())
                                <form method="POST" action="{{ route('avancements.rejeter', $avancement) }}" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter cette demande ?')">
                                    @csrf
                                    <button type="submit" style="padding: 6px 12px; font-weight: 700; font-size: 0.8rem; background: #dc2626; color: #ffffff; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(220,38,38,0.25);" title="Rejeter la demande">
                                        ✖ Rejeter
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('avancements.document', $avancement) }}" target="_blank" style="padding: 6px 10px; font-weight: 600; font-size: 0.8rem; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Consulter la lettre officielle">
                                📄 Lettre
                            </a>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af; padding: 4rem;">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📁</div>
                        <div style="font-weight: 600; font-size: 1rem; color: #4b5563;">Aucune demande d'avancement enregistrée.</div>
                        <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 4px;">Modifiez les filtres ci-dessus pour consulter les dossiers.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($avancements->hasPages())
    <div style="padding: 1.25rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: center; background: #f9fafb;">
        {{ $avancements->withQueryString()->links('vendor.pagination.simple') }}
    </div>
    @endif
</div>

@endsection
