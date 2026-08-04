{{-- resources/views/stagiaires/documents/attente.blade.php --}}
@extends('layouts.app')
@section('title', 'Document soumis')
@section('page-title', 'Document soumis au DRH')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('stagiaires.show', $stagiaire) }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la fiche
    </a>
</div>

<div class="dash-card" style="text-align:center;padding:48px">
    <div style="font-size:4rem;margin-bottom:16px">📤</div>
    <h3 style="margin-bottom:8px;color:var(--col-primary)">Document soumis avec succès !</h3>
    <p style="color:var(--col-text-2);font-size:1rem;max-width:500px;margin:0 auto">
        Votre demande de <strong>{{ $document->type_document === 'autorisation' ? 'autorisation' : 'attestation' }}</strong>
        a été envoyée au DRH pour validation.
    </p>
    <div style="margin-top:16px;font-size:.9rem;color:var(--col-text-3)">
        Référence : <strong>{{ $document->reference }}</strong>
    </div>
    <div style="margin-top:24px;display:flex;gap:12px;justify-content:center">
        <a href="{{ route('stagiaires.show', $stagiaire) }}" class="btn-ghost">Retour au stagiaire</a>
        <a href="{{ route('stagiaires.documents.choisir', $stagiaire) }}" class="btn-ghost">Nouveau document</a>
    </div>
</div>
@endsection