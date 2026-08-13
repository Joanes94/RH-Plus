@extends('layouts.app')

@section('title', 'Gestion des Avances et Ajustements')

@section('content')
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-title">Échéanciers & Ajustements Salariaux</h1>
        <p class="page-subtitle">Configurez les prélèvements d'avances, de frais de soins ou les rappels de moins-perçus échelonnés.</p>
    </div>
</div>

{{-- Zone Filtre de Centre (si global) --}}
<div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-weight: 500; font-size: 0.88rem; color: #374151;">Centre hospitalier :</span>
        @if($user->isGlobal())
            <select onchange="window.location.href = '?centre_id=' + this.value" style="padding: 0.4rem 2rem 0.4rem 0.75rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem; background-color: #fff;">
                @foreach($centres as $c)
                    <option value="{{ $c->id }}" {{ $selectedCentre->id == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                @endforeach
            </select>
        @else
            <strong style="color: var(--col-primary, #1a5c45); font-size: 0.95rem;">{{ $selectedCentre->nom }}</strong>
        @endif
    </div>
    
    @if(!auth()->user()->isReadOnly())
    <div>
        <button class="btn btn-primary" onclick="document.getElementById('modalAddAdjustment').showModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouveau plan
        </button>
    </div>
    @endif
</div>

{{-- Tableau des plans --}}
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Type d'ajustement</th>
                    <th>Libellé / Objet</th>
                    <th style="text-align: right;">Montant mensuel</th>
                    <th style="text-align: right;">Montant global</th>
                    <th>Mois restants</th>
                    <th>Statut</th>
                    @if(!auth()->user()->isReadOnly())
                        <th style="text-align: right;">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #111827;">{{ $adj->personnel->nom_complet }}</div>
                    </td>
                    <td>
                        @if($adj->type === 'avance_salaire')
                            <span style="font-size: 0.72rem; padding: 3px 8px; border-radius: 99px; background: rgba(59, 130, 246, 0.1); color: #1d4ed8; font-weight: 500;">Avance sur salaire</span>
                        @elseif($adj->type === 'frais_medicaux')
                            <span style="font-size: 0.72rem; padding: 3px 8px; border-radius: 99px; background: rgba(239, 68, 68, 0.1); color: #b91c1c; font-weight: 500;">Frais médicaux</span>
                        @else
                            <span style="font-size: 0.72rem; padding: 3px 8px; border-radius: 99px; background: rgba(16, 185, 129, 0.1); color: #047857; font-weight: 500;">Moins-perçu (Rappel)</span>
                        @endif
                    </td>
                    <td>{{ $adj->libelle }}</td>
                    <td style="text-align: right; font-weight: 600; color: {{ $adj->type === 'moins_percu' ? '#10b981' : '#ef4444' }};">
                        {{ $adj->type === 'moins_percu' ? '+' : '-' }}{{ number_format($adj->montant_mensuel, 0, ',', ' ') }} F / mois
                    </td>
                    <td style="text-align: right; font-weight: 500;">
                        {{ $adj->montant_total ? number_format($adj->montant_total, 0, ',', ' ') . ' F' : 'Indéfini' }}
                    </td>
                    <td>
                        @if($adj->mois_restants !== null)
                            <strong>{{ $adj->mois_restants }} mois</strong>
                        @else
                            <span style="color: #6b7280; font-style: italic;">Permanent</span>
                        @endif
                    </td>
                    <td>
                        @if($adj->statut === 'actif')
                            <span class="status-badge" style="background: rgba(16, 185, 129, 0.1); color: #047857; font-size: 0.72rem; padding: 3px 8px; border-radius: 99px;">Actif (En cours)</span>
                        @else
                            <span class="status-badge" style="background: rgba(107, 114, 128, 0.1); color: #4b5563; font-size: 0.72rem; padding: 3px 8px; border-radius: 99px;">Terminé</span>
                        @endif
                    </td>
                    @if(!auth()->user()->isReadOnly())
                    <td style="text-align: right; white-space: nowrap;">
                        <form action="{{ route('pay-adjustments.toggle', $adj->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm" style="border: 1px solid #d1d5db; background: transparent; padding: 4px 8px; border-radius: 6px; color: #374151; font-size: 0.78rem; cursor: pointer; margin-right: 0.25rem;">
                                {{ $adj->statut === 'actif' ? 'Archiver' : 'Activer' }}
                            </button>
                        </form>
                        
                        <form action="{{ route('pay-adjustments.destroy', $adj->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce plan ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="border: 1px solid #ef4444; background: transparent; padding: 4px 8px; border-radius: 6px; color: #ef4444; font-size: 0.78rem; cursor: pointer;">
                                Supprimer
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #9ca3af; padding: 3rem;">
                        Aucun plan d'ajustement salarial enregistré pour ce centre.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale pour créer un ajustement --}}
<dialog id="modalAddAdjustment" class="modal" style="border: none; border-radius: 20px; padding: 0; max-width: 520px; width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0;">
        <h2 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Enregistrer un plan d'ajustement salarial</h2>
        <button onclick="document.getElementById('modalAddAdjustment').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af; padding: 0;">&times;</button>
    </div>
    
    <form action="{{ route('pay-adjustments.store') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding: 1.5rem;">
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="personnel_id" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Salarié concerné</label>
                <select name="personnel_id" id="personnel_id" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem; background-color:#fff;">
                    <option value="">-- Sélectionner l'agent --</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}">{{ $p->nom_complet }} ({{ $p->corporation }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="type" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Type de plan</label>
                <select name="type" id="type" class="form-control" required onchange="toggleFormFields(this.value)" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem; background-color:#fff;">
                    <option value="avance_salaire">Avance sur salaire (Prélèvement mensuel)</option>
                    <option value="frais_medicaux">Frais médicaux / Soins (Prélèvement mensuel)</option>
                    <option value="moins_percu">Moins-perçu (Remboursement mensuel positif)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="libelle" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Objet / Libellé explicatif</label>
                <input type="text" name="libelle" id="libelle" class="form-control" placeholder="ex. Remboursement Avance Fête de Tabaski" required style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem;">
            </div>

            <div id="wrapperMontantTotal" class="form-group" style="margin-bottom: 1rem;">
                <label for="montant_total" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Montant total de la dette (FCFA)</label>
                <input type="number" name="montant_total" id="montant_total" class="form-control" placeholder="ex. 120000" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="montant_mensuel" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Montant prélevé / versé par mois (FCFA)</label>
                <input type="number" name="montant_mensuel" id="montant_mensuel" class="form-control" placeholder="ex. 10000" required style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem;">
            </div>

            <div id="wrapperMoisRestants" class="form-group" style="margin-bottom: 1rem;">
                <label for="mois_restants" style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Durée de l'amortissement (Nombre de mois)</label>
                <input type="number" name="mois_restants" id="mois_restants" class="form-control" placeholder="ex. 12" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem;">
            </div>

        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalAddAdjustment').close()" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem;">Annuler</button>
            <button type="submit" class="btn btn-primary" style="background: var(--col-primary, #1a5c45); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem;">Enregistrer le plan</button>
        </div>
    </form>
</dialog>

<script>
    function toggleFormFields(value) {
        const wrapperTotal = document.getElementById('wrapperMontantTotal');
        const wrapperMois = document.getElementById('wrapperMoisRestants');
        const inputTotal = document.getElementById('montant_total');
        const inputMois = document.getElementById('mois_restants');

        if (value === 'moins_percu') {
            wrapperTotal.style.display = 'none';
            wrapperMois.style.display = 'none';
            inputTotal.removeAttribute('required');
            inputMois.removeAttribute('required');
        } else {
            wrapperTotal.style.display = 'block';
            wrapperMois.style.display = 'block';
            inputTotal.setAttribute('required', 'required');
            inputMois.setAttribute('required', 'required');
        }
    }

    // Set initial configuration
    document.addEventListener('DOMContentLoaded', function () {
        toggleFormFields(document.getElementById('type').value);
    });
</script>
@endsection
