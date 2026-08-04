{{-- resources/views/stagiaires/documents/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Document - ' . $stagiaire->nom_complet)
@section('page-title', 'Document en attente')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('drh.dashboard') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour au tableau de bord
    </a>
</div>

<div class="status-banner status-banner-{{ $document->statut_color }}">
    <div class="sb-left">
        @if($document->statut === 'soumis')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            En attente de votre décision
        @elseif($document->statut === 'approuve')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Approuvé le {{ $document->approuve_le->format('d/m/Y à H:i') }}
        @elseif($document->statut === 'rejete')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Rejeté — {{ $document->motif_rejet }}
        @endif
    </div>
</div>

<div class="fiche-grid">
    <div class="fiche-col">
        <div class="dash-card">
            <div class="card-header"><h3>Stagiaire</h3></div>
            <div class="agent-cell">
                <div class="agent-avatar av-lg">{{ $stagiaire->initiales }}</div>
                <div>
                    <div class="agent-name">{{ $stagiaire->nom_complet }}</div>
                    <div class="agent-meta">{{ $stagiaire->ecole_formation ?: '—' }}</div>
                </div>
            </div>
            <dl class="detail-list" style="margin-top:12px">
                <div class="dl-row"><dt>Document</dt><dd>{{ $document->type_document === 'autorisation' ? 'Autorisation' : 'Attestation' }}</dd></div>
                <div class="dl-row"><dt>Type de stage</dt><dd>{{ ucfirst($document->type_stage) }}</dd></div>
                <div class="dl-row"><dt>Référence</dt><dd><strong>{{ $document->reference }}</strong></dd></div>
                <div class="dl-row"><dt>Soumis par</dt><dd>{{ $document->creePar ? $document->creePar->nom_complet : '—' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="fiche-col">
        {{-- Actions DRH --}}
        @if($document->statut === 'soumis' && auth()->user()->canApprove())
        <div class="dash-card card-drh-action">
            <div class="card-header">
                <h3>Action DRH</h3>
                <span class="badge badge-warn">En attente de décision</span>
            </div>

            <form method="POST" action="{{ route('stagiaires.documents.approuver', [$stagiaire, $document]) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Référence</label>
                    <div class="input-wrapper">
                        <input type="text" name="reference" value="{{ $document->reference }}" placeholder="Référence">
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
                    Approuver
                </button>
            </form>

            <div class="drh-separator">ou</div>

            <form method="POST" action="{{ route('stagiaires.documents.rejeter', [$stagiaire, $document]) }}">
                @csrf
                <div class="form-group">
                    <label>Motif du rejet <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <textarea name="motif_rejet" rows="2" required
                            style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-danger btn-full-mobile"
                        onclick="return confirm('Rejeter ce document ?')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Rejeter
                </button>
            </form>
        </div>
        @elseif($document->statut === 'soumis' && !auth()->user()->canApprove())
        <div class="dash-card">
            <div class="card-header"><h3>Statut</h3></div>
            <p class="empty-inline">Ce document est en attente de la décision du DRH. Vous serez informé(e) dès qu'il sera traité.</p>
        </div>
        @endif
    </div>
</div>
@endsection