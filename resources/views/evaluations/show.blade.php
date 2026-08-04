{{-- resources/views/evaluations/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Fiche d\'évaluation')
@section('page-title', 'Fiche d\'évaluation')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('stagiaires.show', $evaluation->stagiaire) }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour au stagiaire
    </a>
    <div class="header-actions">
        @if($evaluation->statut === 'approuve')
            <a href="{{ route('evaluations.document', $evaluation) }}" class="btn-primary btn-sm" target="_blank">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Voir le document
            </a>
        @endif
        @if($evaluation->isEditable())
            <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn-ghost btn-sm">Modifier</a>
        @endif
        @if(auth()->user()->isDRH() || auth()->user()->isAssistantRH())
            <a href="{{ route('evaluations.create', ['stagiaire_id' => $evaluation->stagiaire->id]) }}" class="btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="12 18 7 13 12 8"/></svg>
                Nouvelle évaluation
            </a>
        @endif
    </div>
</div>

{{-- Status banner --}}
<div class="status-banner status-banner-{{ $evaluation->statut_color }}">
    <div class="sb-left">
        @if($evaluation->statut === 'soumis')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            En attente d'approbation du DRH
        @elseif($evaluation->statut === 'approuve')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Approuvée le {{ $evaluation->approuve_le->format('d/m/Y à H:i') }}
            @if($evaluation->approuvePar) par {{ $evaluation->approuvePar->nom_complet }} @endif
        @elseif($evaluation->statut === 'rejete')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Rejetée — {{ $evaluation->motif_rejet }}
        @else
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Brouillon — non soumis au DRH
        @endif
    </div>
    @if($evaluation->statut === 'brouillon' && !auth()->user()->isDRH())
    <form method="POST" action="{{ route('evaluations.update', $evaluation) }}">
        @csrf @method('PUT')
        <input type="hidden" name="qualites" value="{{ $evaluation->qualites }}">
        <input type="hidden" name="defauts" value="{{ $evaluation->defauts }}">
        <input type="hidden" name="maitrise_pratique" value="{{ $evaluation->maitrise_pratique }}">
        <input type="hidden" name="appreciation_personnelle" value="{{ $evaluation->appreciation_personnelle }}">
        <button type="submit" name="action" value="soumettre" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </form>
    @endif
    @if($evaluation->statut === 'brouillon' && auth()->user()->isDRH())
    <form method="POST" action="{{ route('evaluations.update', $evaluation) }}">
        @csrf @method('PUT')
        <input type="hidden" name="qualites" value="{{ $evaluation->qualites }}">
        <input type="hidden" name="defauts" value="{{ $evaluation->defauts }}">
        <input type="hidden" name="maitrise_pratique" value="{{ $evaluation->maitrise_pratique }}">
        <input type="hidden" name="appreciation_personnelle" value="{{ $evaluation->appreciation_personnelle }}">
        <button type="submit" name="action" value="soumettre" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre (validation DRH)
        </button>
    </form>
    @endif
</div>

<div class="fiche-grid">
    <div class="fiche-col">

        {{-- Stagiaire --}}
        <div class="dash-card">
            <div class="card-header"><h3>Stagiaire concerné</h3></div>
            <div class="agent-cell" style="padding:4px 0 12px;border-bottom:1px solid var(--col-border);margin-bottom:12px">
                <div class="agent-avatar av-lg">{{ $evaluation->stagiaire->initiales }}</div>
                <div>
                    <div class="agent-name" style="font-size:.95rem">{{ $evaluation->stagiaire->nom_complet }}</div>
                    <div class="agent-meta">{{ $evaluation->stagiaire->service ?: '—' }} · {{ $evaluation->stagiaire->niveau_etude ?: '—' }}</div>
                </div>
            </div>
            <dl class="detail-list">
                <div class="dl-row"><dt>École / Formation</dt><dd>{{ $evaluation->stagiaire->ecole_formation ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Période de stage</dt>
                    <dd>
                        @if($evaluation->stagiaire->date_debut_stage && $evaluation->stagiaire->date_fin_stage)
                            {{ $evaluation->stagiaire->date_debut_stage->format('d/m/Y') }} → {{ $evaluation->stagiaire->date_fin_stage->format('d/m/Y') }}
                        @else — @endif
                    </dd>
                </div>
            </dl>
        </div>

    </div>

    <div class="fiche-col">

        {{-- Évaluation --}}
        <div class="dash-card">
            <div class="card-header"><h3>Détails de l'évaluation</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>I. Qualités</dt>
                    <dd style="white-space:pre-wrap;">{{ $evaluation->qualites ?: '—' }}</dd>
                </div>
                <div class="dl-row"><dt>II. Défauts</dt>
                    <dd style="white-space:pre-wrap;">{{ $evaluation->defauts ?: '—' }}</dd>
                </div>
                <div class="dl-row"><dt>III. Maîtrise de la pratique</dt>
                    <dd style="white-space:pre-wrap;">{{ $evaluation->maitrise_pratique ?: '—' }}</dd>
                </div>
                <div class="dl-row"><dt>IV. Appréciation personnelle</dt>
                    <dd style="white-space:pre-wrap;">{{ $evaluation->appreciation_personnelle ?: '—' }}</dd>
                </div>
                <div class="dl-row"><dt>Rédigé par</dt><dd>{{ $evaluation->creePar ? $evaluation->creePar->nom_complet : '—' }}</dd></div>
                <div class="dl-row"><dt>Créé le</dt><dd>{{ $evaluation->created_at->format('d/m/Y à H:i') }}</dd></div>
            </dl>
        </div>

        {{-- Actions DRH --}}
        @if($evaluation->statut === 'soumis' && auth()->user()->isDRH())
        <div class="dash-card card-drh-action">
            <div class="card-header">
                <h3>Action DRH</h3>
                <span class="badge badge-warn">En attente de votre décision</span>
            </div>

            <form method="POST" action="{{ route('evaluations.approuver', $evaluation) }}" enctype="multipart/form-data" class="drh-form">
                @csrf
                <div class="form-group">
                    <label>Référence du document</label>
                    <div class="input-wrapper">
                        <input type="text" name="reference"
                               value="{{ old('reference', $evaluation->reference) }}"
                               placeholder="EVAL/{{ now()->format('m') }}-{{ now()->format('y') }}/NOM">
                    </div>
                </div>
                <div class="form-group">
                    <label>Signature (optionnel)</label>
                    <div class="input-wrapper">
                        <input type="file" name="signature" accept="image/png,image/jpeg" class="file-input-std">
                    </div>
                </div>
                <button type="submit" class="btn-primary btn-full-mobile">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Approuver et signer
                </button>
            </form>

            <div class="drh-separator">ou</div>

            <form method="POST" action="{{ route('evaluations.rejeter', $evaluation) }}">
                @csrf
                <div class="form-group">
                    <label>Motif du rejet <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <textarea name="motif_rejet" rows="2" required
                            style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-danger btn-full-mobile"
                        onclick="return confirm('Rejeter cette évaluation ?')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Rejeter
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection