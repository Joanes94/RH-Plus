@extends('layouts.app')

@section('title', 'Gestion des Corporations')
@section('page-title', 'Corporations & Titres de fonction')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 class="page-heading" style="font-size:1.5rem; font-weight:700; margin:0 0 0.25rem 0;">Corporations & Métiers</h1>
        <p class="page-subtitle" style="color:var(--col-text-3, #64748b); font-size:0.875rem; margin:0;">
            Référentiel des corps de métier et fonctions. Les créations par les centres restent strictement isolées à leur établissement.
        </p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('modalAjoutCorp').showModal()" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.6rem 1.1rem; border-radius:8px; font-weight:600; cursor:pointer;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvelle corporation
        </button>
    </div>
</div>

@if(session('success'))
    <div style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:0.875rem 1rem; border-radius:6px; margin-bottom:1.25rem; font-size:0.9rem;">
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div style="background:#fffbeb; border-left:4px solid #f59e0b; color:#92400e; padding:0.875rem 1rem; border-radius:6px; margin-bottom:1.25rem; font-size:0.9rem;">
        {{ session('warning') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fef2f2; border-left:4px solid #ef4444; color:#991b1b; padding:0.875rem 1rem; border-radius:6px; margin-bottom:1.25rem; font-size:0.9rem;">
        {{ session('error') }}
    </div>
@endif

{{-- ── Bannière de contexte d'isolation par centre ───────────────────────────── --}}
@if(!$isGlobal && $centreActuel)
    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
        <div style="width:36px; height:36px; border-radius:8px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div>
            <div style="font-weight:600; font-size:0.925rem; color:#1e293b;">
                Espace dédié : <span style="color:var(--col-primary, #1a5c45);">{{ $centreActuel->nom }}</span>
            </div>
            <div style="font-size:0.8rem; color:#64748b; margin-top:2px;">
                Toute corporation créée par votre équipe reste <strong>strictement réservée à {{ $centreActuel->nom }}</strong> et n'apparaîtra dans aucun autre centre de santé.
            </div>
        </div>
    </div>
@endif

{{-- ── Barre de filtre et de recherche ───────────────────────────────────────── --}}
<div style="background:#fff; border:1px solid var(--col-border, #e2e8f0); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <form method="GET" action="{{ route('corporations.index') }}" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:center;">
        <div style="flex:1; min-width:240px; position:relative;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une corporation..."
                   style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:6px; font-size:0.875rem;">
        </div>

        @if($isGlobal && $centres->isNotEmpty())
            <div style="min-width:220px;">
                <select name="centre_id" style="width:100%; padding:0.6rem 0.85rem; border:1px solid #cbd5e1; border-radius:6px; font-size:0.875rem;">
                    <option value="">Tous les centres (Vue globale)</option>
                    @foreach($centres as $c)
                        <option value="{{ $c->id }}" {{ (string)request('centre_id') === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div style="display:flex; gap:0.5rem;">
            <button type="submit" class="btn btn-primary" style="padding:0.6rem 1rem; border-radius:6px; font-size:0.875rem; cursor:pointer;">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'centre_id']))
                <a href="{{ route('corporations.index') }}" class="btn btn-secondary" style="padding:0.6rem 1rem; border-radius:6px; font-size:0.875rem; text-decoration:none;">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ── Table des Corporations ────────────────────────────────────────────────── --}}
<div style="background:#fff; border:1px solid var(--col-border, #e2e8f0); border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="overflow-x:auto;">
        <table class="data-table" style="width:100%; border-collapse:collapse; text-align:left; font-size:0.875rem;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#475569; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.04em;">
                    <th style="padding:0.85rem 1.25rem;">Corporation / Fonction</th>
                    <th style="padding:0.85rem 1.25rem;">Portée & Visibilité</th>
                    <th style="padding:0.85rem 1.25rem;">Ajoutée par</th>
                    <th style="padding:0.85rem 1.25rem;">Date</th>
                    <th style="padding:0.85rem 1.25rem; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($corporations as $corp)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:0.9rem 1.25rem; font-weight:600; color:#1e293b;">
                            {{ $corp->nom }}
                        </td>
                        <td style="padding:0.9rem 1.25rem;">
                            @if($corp->isGlobal())
                                <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.25rem 0.65rem; border-radius:999px; font-size:0.75rem; font-weight:600; background:#e0f2fe; color:#0369a1;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                                    Standard Commun (Tous centres)
                                </span>
                            @else
                                <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.25rem 0.65rem; border-radius:999px; font-size:0.75rem; font-weight:600; background:#fef3c7; color:#92400e;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                    Spécifique à : {{ $corp->centre?->nom ?? 'Centre #' . $corp->centre_id }}
                                </span>
                            @endif
                        </td>
                        <td style="padding:0.9rem 1.25rem; color:#64748b;">
                            {{ $corp->creator?->nom_complet ?? ($corp->isGlobal() ? 'Standard National/Diocèse' : '—') }}
                        </td>
                        <td style="padding:0.9rem 1.25rem; color:#64748b;">
                            {{ $corp->created_at ? $corp->created_at->format('d/m/Y') : '—' }}
                        </td>
                        <td style="padding:0.9rem 1.25rem; text-align:right;">
                            @if(!$corp->isGlobal() || auth()->user()->isCRH())
                                <form action="{{ route('corporations.destroy', $corp) }}" method="POST" style="display:inline-block;"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la corporation \'{{ addslashes($corp->nom) }}\' ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem; font-weight:500;"
                                            title="Supprimer">
                                        Supprimer
                                    </button>
                                </form>
                            @else
                                <span style="font-size:0.75rem; color:#94a3b8; font-style:italic;">Protégée</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:2.5rem 1rem; text-align:center; color:#64748b;">
                            Aucune corporation trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($corporations->hasPages())
        <div style="padding:1rem 1.25rem; border-top:1px solid #f1f5f9;">
            {{ $corporations->links() }}
        </div>
    @endif
</div>

{{-- ── Modal Création de Corporation ─────────────────────────────────────────── --}}
<dialog id="modalAjoutCorp" class="modal">
    <div class="modal-content" style="max-width:520px; width:100%;">
        <div class="modal-header">
            <div>
                <h2>Nouvelle corporation</h2>
                <p class="modal-subtitle">Ajouter un titre de fonction ou corps de métier</p>
            </div>
            <button type="button" onclick="document.getElementById('modalAjoutCorp').close()" class="modal-close">&times;</button>
        </div>
        <form action="{{ route('corporations.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="display:flex; flex-direction:column; gap:1.25rem;">
                    <div class="form-group">
                        <label for="nom_corp" style="display:block; font-weight:600; margin-bottom:0.4rem;">Nom de la corporation *</label>
                        <input type="text" name="nom" id="nom_corp" required class="form-control"
                               placeholder="Ex: INFIRMIER SURVEILLANT, CAISSIER PRINCIPAL..."
                               style="width:100%; text-transform:uppercase;">
                        <small style="color:#64748b; font-size:0.75rem; display:block; margin-top:0.35rem;">
                            Le nom sera automatiquement converti en majuscules non genrées pour prévenir tout doublon.
                        </small>
                    </div>

                    @if($isGlobal)
                        <div class="form-group">
                            <label for="centre_id_corp" style="display:block; font-weight:600; margin-bottom:0.4rem;">Portée du centre</label>
                            <select name="centre_id" id="centre_id_corp" class="form-control" style="width:100%;">
                                <option value="">Standard Commun (Accessible à TOUS les centres)</option>
                                @foreach($centres as $c)
                                    <option value="{{ $c->id }}" {{ (string)request('centre_id') === (string)$c->id ? 'selected' : '' }}>
                                        Spécifique à : {{ $c->nom }}
                                    </option>
                                @endforeach
                            </select>
                            <small style="color:#64748b; font-size:0.75rem; display:block; margin-top:0.35rem;">
                                En sélectionnant un centre, la corporation ne sera visible que par ce centre.
                            </small>
                        </div>
                    @else
                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:0.85rem; font-size:0.825rem; color:#166534;">
                            <strong>Centre d'affectation :</strong> {{ $centreActuel->nom ?? 'Votre établissement' }}<br>
                            Cette corporation sera <strong>exclusivement réservée</strong> à votre centre. Elle ne sera jamais partagée avec les autres centres.
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
                <button type="button" onclick="document.getElementById('modalAjoutCorp').close()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer la corporation</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
