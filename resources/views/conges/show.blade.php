@extends('layouts.app')
@section('title', 'Demande de congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('conges.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
    <div class="header-actions">
        @if($conge->statut === 'approuve')
            <a href="{{ route('conges.document', $conge) }}" class="btn-primary btn-sm" target="_blank">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Voir le document
            </a>
        @endif
        @if($conge->isEditable())
            <a href="{{ route('conges.edit', $conge) }}" class="btn-ghost btn-sm">Modifier</a>
        @endif
    </div>
</div>

{{-- Status banner --}}
<div class="status-banner status-banner-{{ $conge->getStatutColor() }}">
    <div class="sb-left">
        @if($conge->statut === 'soumis')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            En attente d'approbation du DRH
        @elseif($conge->statut === 'approuve')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Approuvé le {{ $conge->approuve_le->format('d/m/Y à H:i') }}
            @if($conge->approuvePar) par {{ $conge->approuvePar->prenoms }} {{ strtoupper($conge->approuvePar->nom) }} @endif
        @elseif($conge->statut === 'rejete')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Rejeté — {{ $conge->motif_rejet }}
        @else
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Brouillon — non soumis au DRH
        @endif
    </div>
    @if($conge->statut === 'brouillon')
    <form method="POST" action="{{ route('conges.update', $conge) }}">
        @csrf @method('PUT')
        <input type="hidden" name="type_conge"        value="{{ $conge->type_conge }}">
        <input type="hidden" name="date_debut"        value="{{ $conge->date_debut->format('Y-m-d') }}">
        <input type="hidden" name="nb_jours_demandes" value="{{ $conge->nb_jours_demandes }}">
        <input type="hidden" name="observations"      value="{{ $conge->observations }}">
        <input type="hidden" name="annee"             value="{{ $conge->annee }}">
        <button type="submit" name="action" value="soumettre" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </form>
    @endif
</div>

<div class="fiche-grid">
    <div class="fiche-col">

        {{-- Info agent --}}
        <div class="dash-card">
            <div class="card-header"><h3>Agent concerné</h3></div>
            <div class="agent-cell" style="padding: 4px 0 12px; border-bottom: 1px solid var(--col-border); margin-bottom: 12px;">
                <div class="agent-avatar av-lg">{{ $conge->personnel->initiales }}</div>
                <div>
                    <div class="agent-name" style="font-size:.95rem">{{ $conge->personnel->nom_complet }}</div>
                    <div class="agent-meta">{{ $conge->personnel->corporation ?: '—' }} · {{ $conge->personnel->service ?: '—' }}</div>
                </div>
            </div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Type de congé</dt><dd>{{ $conge->getTypeCongeLabel() }}</dd></div>
                <div class="dl-row"><dt>Année</dt><dd class="mono-text">{{ $conge->annee }}</dd></div>
            </dl>
        </div>

        {{-- Solde --}}
        <div class="dash-card">
            <div class="card-header"><h3>Solde de congés — {{ $conge->annee }}</h3></div>
            <div class="solde-visual">
                <div class="sv-item sv-acquis">
                    <span class="sv-val">{{ $solde['acquis'] }}</span>
                    <span class="sv-label">Jours acquis</span>
                </div>
                <div class="sv-sep">−</div>
                <div class="sv-item sv-pris">
                    <span class="sv-val">{{ $solde['conges_pris'] }}</span>
                    <span class="sv-label">Congés pris</span>
                </div>
                <div class="sv-sep">−</div>
                <div class="sv-item sv-abs">
                    <span class="sv-val">{{ $solde['absences_ded'] }}</span>
                    <span class="sv-label">Abs. déduc.</span>
                </div>
                <div class="sv-sep">=</div>
                <div class="sv-item sv-restants">
                    <span class="sv-val {{ $solde['restants'] <= 0 ? 'text-red' : '' }}">{{ $solde['restants'] }}</span>
                    <span class="sv-label">Restants</span>
                </div>
            </div>
            <div class="sv-bar-wrap">
                @php $pct = $solde['acquis'] > 0 ? min(100, ($solde['total_pris'] / $solde['acquis']) * 100) : 0; @endphp
                <div class="sv-bar-bg">
                    <div class="sv-bar-fill {{ $pct >= 100 ? 'bar-red' : ($pct >= 80 ? 'bar-amber' : 'bar-green') }}" style="width: {{ $pct }}%"></div>
                </div>
                <span class="sv-bar-pct">{{ round($pct) }}% consommé</span>
            </div>
        </div>

    </div>

    <div class="fiche-col">

        {{-- Détails du congé --}}
        <div class="dash-card">
            <div class="card-header"><h3>Détails de la demande</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Date de début</dt><dd><strong>{{ $conge->date_debut->isoFormat('dddd D MMMM YYYY') }}</strong></dd></div>
                <div class="dl-row"><dt>Durée demandée</dt><dd><strong>{{ $conge->nb_jours_demandes }} jours ouvrables</strong></dd></div>
                <div class="dl-row"><dt>Date de reprise</dt><dd><strong>{{ $conge->date_fin->isoFormat('dddd D MMMM YYYY') }}</strong></dd></div>
                <div class="dl-row"><dt>Déjà pris cette année</dt><dd>{{ $conge->nb_jours_deja_pris }} j</dd></div>
                @if($conge->observations)
                <div class="dl-row"><dt>Observations</dt><dd>{{ $conge->observations }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Actions DRH --}}
        @if($conge->statut === 'soumis' && auth()->user()->canApprove())
        <div class="dash-card card-drh-action">
            <div class="card-header">
                <h3>Action DRH</h3>
                <span class="badge badge-warn">En attente de votre décision</span>
            </div>

            {{-- Approuver --}}
            <form method="POST" action="{{ route('conges.approuver', $conge) }}" enctype="multipart/form-data" class="drh-form">
                @csrf
                <div class="form-group">
                    <label>Référence du document (N/REF)</label>
                    <div class="input-wrapper">
                        <input type="text" name="reference"
                               value="{{ old('reference', $conge->reference) }}"
                               placeholder="…../{{ now()->format('m') }}-{{ now()->format('y') }}/AC/DDIS/CSVHHSL/DIR/DRH/ARH">
                    </div>
                    <span class="field-hint">Apparaîtra sur le document officiel. Laissez vide pour générer automatiquement.</span>
                </div>
                <div class="form-group">
                    <label>Signature (optionnel — utilise la signature enregistrée si absent)</label>
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

            {{-- Rejeter --}}
            <form method="POST" action="{{ route('conges.rejeter', $conge) }}">
                @csrf
                <div class="form-group">
                    <label>Motif du rejet <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <textarea name="motif_rejet" rows="2" placeholder="Expliquer le motif…" required
                            style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-danger btn-full-mobile"
                        onclick="return confirm('Rejeter cette demande ?')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Rejeter la demande
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
