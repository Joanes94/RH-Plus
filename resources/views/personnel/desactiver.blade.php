@extends('layouts.app')

@section('title', 'Désactiver — ' . $personnel->nom_complet)
@section('page-title', 'Désactivation du personnel')

@section('content')
<div class="form-page">
    <div class="form-card">
        <div class="form-card-header">
            <a href="{{ route('personnel.show', $personnel) }}" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Retour
            </a>
            <h2>Désactiver : {{ $personnel->nom_complet }}</h2>
        </div>

        <form method="POST" action="{{ route('personnel.desactiver.store', $personnel) }}">
            @csrf
            <div class="form-body">
                <div class="alert alert-warning" style="margin-bottom: 1.5rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Cette action désactivera le personnel et archivera son contrat en cours. Le personnel restera consultable dans les anciens travailleurs.
                </div>

                <div class="personnel-summary">
                    <div class="summary-avatar">{{ $personnel->initiales }}</div>
                    <div>
                        <strong>{{ $personnel->nom_complet }}</strong>
                        <span>{{ $personnel->corporation }} — {{ $personnel->centre?->nom ?? 'N/A' }}</span>
                        <span>Contrat : {{ $personnel->type_contrat_actuel ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="motif_depart">Motif de désactivation *</label>
                    <select name="motif_depart" id="motif_depart" required class="form-control">
                        <option value="">— Choisir le motif —</option>
                        <option value="licenciement">Licenciement</option>
                        <option value="mise_disponibilite">Mise en disponibilité</option>
                        <option value="deces">Décès</option>
                        <option value="demission">Démission</option>
                        <option value="fin_contrat">Fin de contrat</option>
                        <option value="retraite">Retraite</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="commentaire">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="3" class="form-control" placeholder="Précisions éventuelles..."></textarea>
                </div>
            </div>

            <div class="form-footer">
                <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Confirmer la désactivation de {{ $personnel->nom_complet }} ?')">
                    Désactiver le personnel
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
.btn-danger { background: #ef4444; color: #fff; border: none; }
.btn-danger:hover { background: #dc2626; }

.personnel-summary {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem;
    background: #f9fafb; border-radius: 12px;
}
.summary-avatar {
    width: 48px; height: 48px; border-radius: 12px;
    background: var(--col-primary, #1a5c45); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.9rem;
}
.personnel-summary span { display: block; font-size: 0.8rem; color: #6b7280; }

.alert-warning {
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.2);
    color: #92400e;
    border-radius: 10px; padding: 0.75rem 1rem;
    display: flex; align-items: flex-start; gap: 0.5rem;
    font-size: 0.85rem;
}
</style>
@endpush
@endsection
