@extends('layouts.app')

@section('title', 'Gestion des Avances et Ajustements')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title" style="font-size: 1.8rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem 0;">Ajustements & Échéanciers Salariaux</h1>
        <p class="page-subtitle" style="font-size: 0.9rem; color: #6b7280; margin: 0;">Configurez les amortissements d'avances sur salaire, frais de soins médicaux ou les rappels de moins-perçus échelonnés.</p>
    </div>
</div>

{{-- Barre d'actions & Filtre Centre --}}
<div class="action-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-weight: 600; font-size: 0.88rem; color: #374151; display: inline-flex; align-items: center; gap: 0.35rem;">
            🏥 Filtrer par Centre :
        </span>
        @if($user->isGlobal())
            <div class="select-wrapper">
                <select onchange="window.location.href = '?centre_id=' + this.value" class="filter-select">
                    @foreach($centres as $c)
                        <option value="{{ $c->id }}" {{ $selectedCentre->id == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <strong style="color: var(--col-primary, #1a5c45); font-size: 0.95rem; font-weight: 700;">{{ $selectedCentre->nom }}</strong>
        @endif
    </div>
    
    @if(!auth()->user()->isReadOnly())
    <div>
        <button class="btn-premium" onclick="document.getElementById('modalAddAdjustment').showModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 0.25rem;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouveau plan d'ajustement
        </button>
    </div>
    @endif
</div>

{{-- Tableau des plans salariaux --}}
<div class="card premium-card">
    <div class="table-responsive">
        <table class="table premium-table">
            <thead>
                <tr>
                    <th>Salarié</th>
                    <th>Type d'ajustement</th>
                    <th>Objet / Description</th>
                    <th class="text-right">Montant mensuel</th>
                    <th class="text-right">Dette Globale</th>
                    <th class="text-center">Durée restante</th>
                    <th class="text-center">Statut</th>
                    @if(!auth()->user()->isReadOnly())
                        <th class="text-right">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td>
                        <div class="agent-profile">
                            <div class="agent-avatar">{{ substr($adj->personnel->nom, 0, 1) }}{{ substr($adj->personnel->prenoms, 0, 1) }}</div>
                            <div>
                                <div class="agent-name">{{ $adj->personnel->nom_complet }}</div>
                                <div class="agent-subtext">{{ $adj->personnel->corporation }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($adj->type === 'avance_salaire')
                            <span class="type-pill type-avance">Avance sur salaire</span>
                        @elseif($adj->type === 'frais_medicaux')
                            <span class="type-pill type-medicaux">Frais médicaux</span>
                        @else
                            <span class="type-pill type-moinspercu">Moins-perçu (Rappel)</span>
                        @endif
                    </td>
                    <td style="color: #4b5563; font-weight: 500; font-size: 0.88rem;">
                        {{ $adj->libelle }}
                    </td>
                    <td class="text-right" style="font-weight: 700; font-size: 0.95rem; color: {{ $adj->type === 'moins_percu' ? '#059669' : '#dc2626' }};">
                        {{ $adj->type === 'moins_percu' ? '+' : '-' }}{{ number_format($adj->montant_mensuel, 0, ',', ' ') }} F / mois
                    </td>
                    <td class="text-right" style="font-weight: 500; color: #4b5563;">
                        {{ $adj->montant_total ? number_format($adj->montant_total, 0, ',', ' ') . ' F' : '—' }}
                    </td>
                    <td class="text-center">
                        @if($adj->mois_restants !== null)
                            <span style="font-weight: 600; color: #1f2937;">{{ $adj->mois_restants }} mois</span>
                        @else
                            <span style="color: #6b7280; font-style: italic; font-size: 0.82rem;">Permanent</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($adj->statut === 'actif')
                            <span class="status-pill status-actif">Actif</span>
                        @else
                            <span class="status-pill status-termine">Terminé</span>
                        @endif
                    </td>
                    @if(!auth()->user()->isReadOnly())
                    <td class="text-right">
                        <div style="display: inline-flex; gap: 0.4rem; justify-content: flex-end; align-items: center;">
                            <form action="{{ route('pay-adjustments.toggle', $adj->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="action-btn {{ $adj->statut === 'actif' ? 'btn-archive' : 'btn-activate' }}" title="{{ $adj->statut === 'actif' ? 'Désactiver / Archiver le plan' : 'Réactiver le plan' }}">
                                    @if($adj->statut === 'actif')
                                        🗄️ Archiver
                                    @else
                                        ⚡ Réactiver
                                    @endif
                                </button>
                            </form>
                            
                            <form action="{{ route('pay-adjustments.destroy', $adj->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Supprimer définitivement ce plan d\'ajustement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn btn-delete" title="Supprimer le plan">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #9ca3af; padding: 4rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">💸</div>
                        Aucun plan d'ajustement salarial enregistré pour ce centre.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale d'enregistrement de plan - Ultra propre --}}
<dialog id="modalAddAdjustment" class="modal" style="border: none; border-radius: 20px; padding: 0; max-width: 520px; width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: linear-gradient(135deg, #1a5c45 0%, #227055 100%); color: #fff;">
        <h2 style="margin: 0; font-size: 1.15rem; font-weight: 600; color: #fff;">Nouveau plan d'ajustement salarial</h2>
        <button onclick="document.getElementById('modalAddAdjustment').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: rgba(255,255,255,0.7); padding: 0;">&times;</button>
    </div>
    
    <form action="{{ route('pay-adjustments.store') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding: 1.5rem;">
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="personnel_id" class="form-label">Salarié bénéficiaire / redevable *</label>
                <select name="personnel_id" id="personnel_id" class="form-input" required>
                    <option value="">-- Sélectionner l'agent --</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}">{{ $p->nom_complet }} ({{ $p->corporation }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="type" class="form-label">Nature de la retenue / du versement *</label>
                <select name="type" id="type" class="form-input" required onchange="toggleFormFields(this.value)">
                    <option value="avance_salaire">Avance sur salaire (Déduction mensuelle du net)</option>
                    <option value="frais_medicaux">Frais médicaux / Soins (Déduction mensuelle du net)</option>
                    <option value="moins_percu">Moins-perçu (Remboursement mensuel versé à l'agent)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="libelle" class="form-label">Motif / Libellé du plan *</label>
                <input type="text" name="libelle" id="libelle" class="form-input" placeholder="ex. Remboursement Avance Scolaire" required>
            </div>

            <div id="wrapperMontantTotal" class="form-group" style="margin-bottom: 1.25rem;">
                <label for="montant_total" class="form-label">Montant global de la dette (FCFA) *</label>
                <input type="number" name="montant_total" id="montant_total" class="form-input" placeholder="ex. 150000">
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="montant_mensuel" class="form-label">Prélèvement / Versement mensuel (FCFA) *</label>
                <input type="number" name="montant_mensuel" id="montant_mensuel" class="form-input" placeholder="ex. 15000" required>
            </div>

            <div id="wrapperMoisRestants" class="form-group" style="margin-bottom: 1.25rem;">
                <label for="mois_restants" class="form-label">Durée de l'amortissement (Nombre de mois) *</label>
                <input type="number" name="mois_restants" id="mois_restants" class="form-input" placeholder="ex. 10">
            </div>

        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="action-btn-back" onclick="document.getElementById('modalAddAdjustment').close()">Annuler</button>
            <button type="submit" class="action-btn-submit">Enregistrer & Activer</button>
        </div>
    </form>
</dialog>

@push('styles')
<style>
/* CSS Ajustements Salariaux Premium */
.premium-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    overflow: hidden;
}

.premium-table {
    width: 100%;
    border-collapse: collapse;
}

.premium-table th {
    background-color: #f9fafb;
    border-bottom: 1.5px solid #e5e7eb;
    color: #4b5563;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 1.25rem;
}

.premium-table td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.premium-table tr:hover td {
    background-color: #f9fafb;
}

/* Agent Avatar & Profile info */
.agent-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.agent-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #1a5c45 0%, #2e7d62 100%);
    color: #ffffff;
    font-weight: bold;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}

.agent-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #111827;
    line-height: 1.3;
}

.agent-subtext {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Type adjustment pills */
.type-pill {
    font-size: 0.72rem;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-block;
    border: 1px solid transparent;
}
.type-avance {
    background: #eff6ff;
    color: #1e40af;
    border-color: #bfdbfe;
}
.type-medicaux {
    background: #fdf2f2;
    color: #9b1c1c;
    border-color: #fde8e8;
}
.type-moinspercu {
    background: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
}

/* Status pills */
.status-pill {
    font-size: 0.72rem;
    padding: 3px 8px;
    border-radius: 99px;
    font-weight: 600;
    display: inline-block;
}
.status-actif {
    background: #d1fae5;
    color: #065f46;
}
.status-termine {
    background: #f3f4f6;
    color: #4b5563;
}

/* Buttons style */
.btn-premium {
    background: linear-gradient(135deg, #1a5c45 0%, #227055 100%);
    color: #ffffff;
    border: none;
    padding: 0.55rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
    box-shadow: 0 4px 10px rgba(26, 92, 69, 0.15);
}
.btn-premium:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(26, 92, 69, 0.25);
}

.filter-select {
    padding: 0.45rem 2.25rem 0.45rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
    background-color: #fff;
    cursor: pointer;
}

/* Actions inline buttons */
.action-btn {
    padding: 4px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-archive {
    background-color: #f3f4f6;
    color: #4b5563;
    border-color: #d1d5db;
}
.btn-archive:hover {
    background-color: #e5e7eb;
    color: #1f2937;
}
.btn-activate {
    background-color: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}
.btn-activate:hover {
    background-color: #d1fae5;
}
.btn-delete {
    background-color: #fdf2f2;
    color: #b91c1c;
    border-color: #fde8e8;
}
.btn-delete:hover {
    background-color: #fde8e8;
}

/* Form Styles */
.form-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.35rem;
}
.form-input {
    width: 100%;
    padding: 0.55rem;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 0.88rem;
    box-sizing: border-box;
    transition: border-color 0.15s;
    background-color: #fff;
}
.form-input:focus {
    border-color: var(--col-primary, #1a5c45);
    outline: none;
}

.action-btn-submit {
    background-color: var(--col-primary, #1a5c45);
    color: #fff;
    border: none;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.88rem;
    font-weight: 600;
}
.action-btn-submit:hover {
    background-color: #124030;
}
.action-btn-back {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.88rem;
    font-weight: 500;
}
</style>
@endpush

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

    document.addEventListener('DOMContentLoaded', function () {
        toggleFormFields(document.getElementById('type').value);
    });
</script>
@endsection
