@extends('layouts.app')

@section('title', 'Gestion des Périodes de Paie')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Gestion des Périodes de Paie</h1>
        <p class="page-subtitle">Pilotez le calcul des salaires et suivez l'historique des mois traités.</p>
    </div>
    @if(!auth()->user()->isReadOnly())
    <div>
        <button class="btn btn-primary" onclick="document.getElementById('modalNewPeriod').showModal()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Démarrer un mois de paie
        </button>
    </div>
    @endif
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Mois de traitement</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mois (Code)</th>
                    <th>Désignation</th>
                    <th>Statut</th>
                    <th>Créé par</th>
                    <th>Date d'ouverture</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $period)
                <tr>
                    <td><strong>{{ $period->code }}</strong></td>
                    <td>{{ $period->label }}</td>
                    <td>
                        @if($period->statut === 'ouvert')
                            <span class="status-badge status-success" style="background: rgba(16, 185, 129, 0.1); color: #047857; font-size: 0.72rem; padding: 4px 10px; border-radius: 99px;">Ouvert (En cours)</span>
                        @else
                            <span class="status-badge status-secondary" style="background: rgba(107, 114, 128, 0.1); color: #4b5563; font-size: 0.72rem; padding: 4px 10px; border-radius: 99px;">Clôturé & Verrouillé</span>
                        @endif
                    </td>
                    <td>{{ $period->createdBy?->nom_complet ?? 'Automatique' }}</td>
                    <td>{{ $period->created_at->format('d/m/Y à H:i') }}</td>
                    <td style="text-align: right;">
                        <a href="{{ route('pay-periods.show', $period->id) }}" class="btn btn-sm btn-outline-primary" style="padding: 4px 12px; font-size: 0.82rem; border-radius: 6px; border: 1px solid var(--col-primary, #1a5c45); color: var(--col-primary, #1a5c45); text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Gérer la paie
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #9ca3af; padding: 3rem;">
                        Aucune période de paie n'a encore été ouverte.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale pour démarrer un nouveau mois --}}
<dialog id="modalNewPeriod" class="modal" style="border: none; border-radius: 20px; padding: 0; max-width: 480px; width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0;">
        <h2 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Démarrer un nouveau mois de paie</h2>
        <button onclick="document.getElementById('modalNewPeriod').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af; padding: 0;">&times;</button>
    </div>
    <form action="{{ route('pay-periods.store') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding: 1.5rem;">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="code" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Code Période (AAAA-MM)</label>
                <input type="text" name="code" id="code" class="form-control" placeholder="{{ date('Y-m') }}" required 
                       style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                <small style="color: #6b7280; font-size: 0.75rem;">Format : Année-Mois (ex. 2026-08 pour Août 2026).</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="label" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Désignation</label>
                <input type="text" name="label" id="label" class="form-control" placeholder="Août 2026" required
                       style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem;">
            </div>
        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalNewPeriod').close()" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem;">Annuler</button>
            <button type="submit" class="btn btn-primary" style="background: var(--col-primary, #1a5c45); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem;">Démarrer</button>
        </div>
    </form>
</dialog>

<script>
    // Autofill label based on code value change
    document.getElementById('code').addEventListener('input', function(e) {
        const value = e.target.value;
        const regex = /^(\d{4})-(\d{2})$/;
        const match = value.match(regex);
        if (match) {
            const year = match[1];
            const monthNum = match[2];
            const months = {
                '01': 'Janvier', '02': 'Février', '03': 'Mars', '04': 'Avril',
                '05': 'Mai', '06': 'Juin', '07': 'Juillet', '08': 'Août',
                '09': 'Septembre', '10': 'Octobre', '11': 'Novembre', '12': 'Décembre'
            };
            const monthName = months[monthNum];
            if (monthName) {
                document.getElementById('label').value = monthName + ' ' + year;
            }
        }
    });
</script>
@endsection
