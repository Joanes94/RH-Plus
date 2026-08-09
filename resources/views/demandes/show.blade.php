@extends('layouts.app')
@section('title', 'Demande')
@section('page-title', 'Détail de la demande')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('demandes.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
    <div class="header-actions">
        @if($demande->statut === 'approuve')
            <a href="{{ route('demandes.document', $demande) }}" class="btn-primary btn-sm" target="_blank">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Voir le document
            </a>
        @endif
        @if(auth()->user()->isAssistantRH() && $demande->isEditable())
            <a href="{{ route('demandes.edit', $demande) }}" class="btn-ghost btn-sm">Modifier</a>
        @endif
    </div>
</div>

{{-- Status banner --}}
<div class="status-banner status-banner-{{ $demande->statut_color }}">
    <div class="sb-left">
        @if($demande->statut === 'soumis')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            En attente d'approbation du DRH
        @elseif($demande->statut === 'approuve')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Approuvée le {{ $demande->approuve_le->format('d/m/Y à H:i') }}
            @if($demande->approuvePar) par {{ $demande->approuvePar->prenoms }} {{ strtoupper($demande->approuvePar->nom) }} @endif
        @elseif($demande->statut === 'rejete')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Rejetée — {{ $demande->motif_rejet }}
        @else
            Brouillon
        @endif
    </div>
    @if($demande->statut === 'brouillon' && auth()->user()->isAssistantRH())
    <form method="POST" action="{{ route('demandes.update', $demande) }}">
        @csrf @method('PUT')
        <input type="hidden" name="personnel_id"  value="{{ $demande->personnel_id }}">
        <input type="hidden" name="type_demande"  value="{{ $demande->type_demande }}">
        <input type="hidden" name="observations"  value="{{ $demande->observations }}">
        <button type="submit" name="action" value="soumettre" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </form>
    @endif
</div>

<div class="fiche-grid">
    <div class="fiche-col">
        <div class="dash-card">
            <div class="card-header"><h3>Agent concerné</h3></div>
            <div class="agent-cell" style="padding:4px 0 12px;border-bottom:1px solid var(--col-border);margin-bottom:12px">
                <div class="agent-avatar av-lg">{{ $demande->personnel->initiales }}</div>
                <div>
                    <div class="agent-name" style="font-size:.95rem">{{ $demande->personnel->nom_complet }}</div>
                    <div class="agent-meta">
                        {{ $demande->personnel->corporation ?: '—' }} · {{ $demande->personnel->service ?: '—' }}
                        <span class="badge {{ $demande->personnel->sexe === 'F' ? 'badge-warn' : 'badge-blue' }}" style="margin-left:6px;font-size:.68rem">
                            {{ $demande->personnel->sexe === 'F' ? 'Femme' : 'Homme' }}
                        </span>
                    </div>
                </div>
            </div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Type de demande</dt><dd><span class="demande-type-tag">{{ $demande->type_label }}</span></dd></div>
                @if($demande->date_debut)
                <div class="dl-row"><dt>Date de début</dt><dd>{{ $demande->date_debut->isoFormat('dddd D MMMM YYYY') }}</dd></div>
                @endif
                @if($demande->date_fin)
                <div class="dl-row"><dt>Date de fin</dt><dd>{{ $demande->date_fin->isoFormat('dddd D MMMM YYYY') }}</dd></div>
                @endif
                @if($demande->nb_jours)
                <div class="dl-row"><dt>Durée</dt><dd>{{ $demande->nb_jours }} jour(s) {{ $demande->type_demande === 'conge_maladie' ? 'ouvrables' : '' }}</dd></div>
                @endif
                @if($demande->type_demande === 'conge_maladie' && $demande->date_reprise)
                <div class="dl-row"><dt>Date de reprise</dt><dd><strong>{{ $demande->date_reprise->isoFormat('dddd D MMMM YYYY') }}</strong></dd></div>
                @endif
                @if($demande->date_accouchement_prevu)
                <div class="dl-row"><dt>Accouchement prévu</dt><dd>{{ $demande->date_accouchement_prevu->format('d/m/Y') }}</dd></div>
                @endif
                @if($demande->etablissement_stage)
                <div class="dl-row"><dt>Établissement</dt><dd>{{ $demande->etablissement_stage }}</dd></div>
                @endif
                @if($demande->niveau_etude)
                <div class="dl-row"><dt>Niveau d'étude</dt><dd>{{ $demande->niveau_etude }}</dd></div>
                @endif
                @if($demande->specialite)
                <div class="dl-row"><dt>Spécialité</dt><dd>{{ $demande->specialite }}</dd></div>
                @endif
                @if($demande->motif)
                <div class="dl-row"><dt>Motif</dt><dd>{{ $demande->motif }}</dd></div>
                @endif
                @if($demande->faits_reproches)
                <div class="dl-row"><dt>Faits reprochés</dt><dd style="white-space:pre-line">{{ $demande->faits_reproches }}</dd></div>
                @endif
                @if($demande->date_faits)
                <div class="dl-row"><dt>Date des faits</dt><dd>{{ $demande->date_faits->format('d/m/Y') }}</dd></div>
                @endif
                @if($demande->observations)
                <div class="dl-row"><dt>Observations</dt><dd>{{ $demande->observations }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

    <div class="fiche-col">
        <div class="dash-card">
            <div class="card-header"><h3>Suivi</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Rédigé par</dt><dd>{{ $demande->creePar?->nom_complet ?? '—' }}</dd></div>
                <div class="dl-row"><dt>Créé le</dt><dd>{{ $demande->created_at->format('d/m/Y à H:i') }}</dd></div>
                <div class="dl-row"><dt>Modifié le</dt><dd>{{ $demande->updated_at->format('d/m/Y à H:i') }}</dd></div>
            </dl>
        </div>

        {{-- Actions DRH --}}
        @if($demande->statut === 'soumis' && auth()->user()->canApprove())
        <div class="dash-card card-drh-action">
            <div class="card-header">
                <h3>Action DRH</h3>
                <span class="badge badge-warn">En attente</span>
            </div>
            <form method="POST" action="{{ route('demandes.approuver', $demande) }}" enctype="multipart/form-data" class="drh-form">
                @csrf
                <div class="form-group">
                    <label>Référence du document (N/REF)</label>
                    <div class="input-wrapper">
                        <input type="text" name="reference"
                               value="{{ old('reference', $demande->reference) }}"
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
            <form method="POST" action="{{ route('demandes.rejeter', $demande) }}">
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
                    Rejeter
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
