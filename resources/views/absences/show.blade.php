@extends('layouts.app')
@section('title', 'Autorisation d\'absence')
@section('page-title', 'Autorisation d\'absence')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('absences.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
    <div class="header-actions">
        @if($absence->statut === 'approuve')
            <a href="{{ route('absences.document', $absence) }}" class="btn-primary btn-sm" target="_blank">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Voir le document
            </a>
        @endif
        @if($absence->isEditable())
            <a href="{{ route('absences.edit', $absence) }}" class="btn-ghost btn-sm">Modifier</a>
        @endif
    </div>
</div>

{{-- Status banner --}}
<div class="status-banner status-banner-{{ $absence->getStatutColor() }}">
    <div class="sb-left">
        @if($absence->statut === 'soumis')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            En attente d'approbation du DRH
        @elseif($absence->statut === 'approuve')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Approuvée le {{ $absence->approuve_le->format('d/m/Y à H:i') }}
            @if($absence->approuvePar) par {{ $absence->approuvePar->prenoms }} {{ strtoupper($absence->approuvePar->nom) }} @endif
        @elseif($absence->statut === 'rejete')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Rejetée — {{ $absence->motif_rejet }}
        @else
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Brouillon — non soumis au DRH
        @endif
    </div>
    @if($absence->statut === 'brouillon')
    <form method="POST" action="{{ route('absences.update', $absence) }}">
        @csrf @method('PUT')
        <input type="hidden" name="type_absence" value="{{ $absence->type_absence }}">
        <input type="hidden" name="date_debut"   value="{{ $absence->date_debut->format('Y-m-d') }}">
        <input type="hidden" name="date_fin"     value="{{ $absence->date_fin->format('Y-m-d') }}">
        <input type="hidden" name="motif"        value="{{ $absence->motif }}">
        <input type="hidden" name="observations" value="{{ $absence->observations }}">
        <button type="submit" name="action" value="soumettre" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </form>
    @endif
</div>

<div class="fiche-grid">
    <div class="fiche-col">

        {{-- Agent --}}
        <div class="dash-card">
            <div class="card-header"><h3>Agent concerné</h3></div>
            <div class="agent-cell" style="padding:4px 0 12px;border-bottom:1px solid var(--col-border);margin-bottom:12px">
                <div class="agent-avatar av-lg">{{ $absence->personnel->initiales }}</div>
                <div>
                    <div class="agent-name" style="font-size:.95rem">{{ $absence->personnel->nom_complet }}</div>
                    <div class="agent-meta">{{ $absence->personnel->corporation ?: '—' }} · {{ $absence->personnel->service ?: '—' }}</div>
                </div>
            </div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Type d'absence</dt><dd><strong>{{ $absence->getTypeLabel() }}</strong></dd></div>
                <div class="dl-row">
                    <dt>Déductible</dt>
                    <dd>
                        @if($absence->deductible)
                            <span class="badge badge-warn">Oui — déduit des congés</span>
                        @else
                            <span class="badge badge-green">Non déductible</span>
                        @endif
                    </dd>
                </div>
                @if($absence->motif)
                <div class="dl-row"><dt>Motif</dt><dd>{{ $absence->motif }}</dd></div>
                @endif
                @if($absence->observations)
                <div class="dl-row"><dt>Observations</dt><dd>{{ $absence->observations }}</dd></div>
                @endif
            </dl>
        </div>

    </div>

    <div class="fiche-col">

        {{-- Détails --}}
<div class="dash-card">
    <div class="card-header"><h3>Détails de la période</h3></div>
    <dl class="detail-list">
        <div class="dl-row"><dt>Date de début</dt><dd><strong>{{ $absence->date_debut->isoFormat('dddd D MMMM YYYY') }}</strong></dd></div>
        <div class="dl-row"><dt>Date de fin</dt><dd><strong>{{ $absence->date_fin->isoFormat('dddd D MMMM YYYY') }}</strong></dd></div>
        <div class="dl-row"><dt>Date de reprise</dt><dd><strong>{{ $absence->date_reprise->isoFormat('dddd D MMMM YYYY') }}</strong></dd></div>
        <div class="dl-row"><dt>Durée</dt><dd><strong>{{ $absence->nb_jours_ouvrables }} jour(s) ouvrable(s)</strong></dd></div>
        <div class="dl-row"><dt>Rédigé par</dt><dd>{{ $absence->creePar ? $absence->creePar->nom_complet : '—' }}</dd></div>
        <div class="dl-row"><dt>Soumis le</dt><dd>{{ $absence->updated_at->format('d/m/Y') }}</dd></div>
    </dl>
</div>

        {{-- Actions DRH --}}
        @if($absence->statut === 'soumis' && auth()->user()->canApprove())
        <div class="dash-card card-drh-action">
            <div class="card-header">
                <h3>Action DRH</h3>
                <span class="badge badge-warn">En attente de votre décision</span>
            </div>

            <form method="POST" action="{{ route('absences.approuver', $absence) }}" enctype="multipart/form-data" class="drh-form">
                @csrf
                <div class="form-group">
                    <label>Référence du document (N/REF)</label>
                    <div class="input-wrapper">
                        <input type="text" name="reference"
                               value="{{ old('reference', $absence->reference) }}"
                               placeholder="…../{{ now()->format('m') }}-{{ now()->format('y') }}/AC/DDIS/CSVHHSL/DIR/DRH/ARH">
                    </div>
                    <span class="field-hint">Apparaîtra sur le document officiel. Laissez vide pour générer automatiquement.</span>
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

            <form method="POST" action="{{ route('absences.rejeter', $absence) }}">
                @csrf
                <div class="form-group">
                    <label>Motif du rejet <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <textarea name="motif_rejet" rows="2" required
                            style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-danger btn-full-mobile"
                        onclick="return confirm('Rejeter cette demande ?')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Rejeter
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
