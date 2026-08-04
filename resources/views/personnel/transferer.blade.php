@extends('layouts.app')

@section('title', 'Transférer — ' . $personnel->nom_complet)
@section('page-title', 'Transfert de personnel')

@section('content')
<div class="form-page">
    <div class="form-card">
        <div class="form-card-header">
            <a href="{{ route('personnel.show', $personnel) }}" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Retour
            </a>
            <h2>Transférer : {{ $personnel->nom_complet }}</h2>
        </div>

        <form method="POST" action="{{ route('personnel.transferer.store', $personnel) }}">
            @csrf
            <div class="form-body">
                <div class="transfer-visual">
                    <div class="transfer-from">
                        <span class="transfer-label">Centre actuel</span>
                        <div class="transfer-centre">
                            <div class="centre-dot" style="background: var(--col-primary, #1a5c45)"></div>
                            {{ $personnel->centre?->nom ?? 'Non affecté' }}
                        </div>
                    </div>
                    <div class="transfer-arrow">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                    <div class="transfer-to">
                        <span class="transfer-label">Nouveau centre</span>
                        <select name="centre_id" id="centre_id" required class="form-control">
                            <option value="">— Choisir —</option>
                            @foreach($centres as $centre)
                                @if($centre->id !== $personnel->centre_id)
                                <option value="{{ $centre->id }}">{{ $centre->nom }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="date_transfert">Date effective du transfert *</label>
                    <input type="date" name="date_transfert" id="date_transfert" required class="form-control" value="{{ date('Y-m-d') }}">
                </div>

                <div class="form-group">
                    <label for="commentaire">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="3" class="form-control" placeholder="Motif du transfert..."></textarea>
                </div>
            </div>

            <div class="form-footer">
                <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                    Confirmer le transfert
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.form-page { max-width: 600px; }
.form-card { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; overflow: hidden; }
.form-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 1rem; }
.form-card-header h2 { margin: 0; font-size: 1.05rem; font-weight: 600; }
.form-body { padding: 1.5rem; }
.form-footer { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-back { display: flex; align-items: center; gap: 0.25rem; color: #6b7280; font-size: 0.85rem; text-decoration: none; }

.transfer-visual {
    display: flex; align-items: center; gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(26,92,69,0.04), rgba(59,130,246,0.04));
    border-radius: 12px;
    border: 1px dashed #d1d5db;
}
.transfer-from, .transfer-to { flex: 1; }
.transfer-arrow { color: var(--col-primary, #1a5c45); flex-shrink: 0; }
.transfer-label { display: block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 0.4rem; font-weight: 600; }
.transfer-centre { display: flex; align-items: center; gap: 0.5rem; font-weight: 500; font-size: 0.9rem; }
.centre-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
</style>
@endpush
@endsection
