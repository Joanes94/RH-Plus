@extends('layouts.app')

@section('title', 'Gestion des Avances et Ajustements')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title" style="font-size: 1.8rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem 0;">Ajustements & Échéanciers Salariaux</h1>
        <p class="page-subtitle" style="font-size: 0.9rem; color: #6b7280; margin: 0;">Configurez les amortissements d'avances sur salaire, frais de soins médicaux ou les rappels de moins-perçus échelonnés par mois.</p>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
<div class="alert alert-success" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
    <span>✓</span> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
    <span>⚠️</span> {{ session('error') }}
</div>
@endif

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
        <button class="btn-premium" onclick="openAddModal()">
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
                    <th class="text-center">Échéancier</th>
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
                            <div class="agent-avatar">{{ strtoupper(substr($adj->personnel->nom ?? '', 0, 1)) }}{{ strtoupper(substr($adj->personnel->prenoms ?? '', 0, 1)) }}</div>
                            <div>
                                <div class="agent-name">{{ $adj->personnel->nom_complet ?? 'N/A' }}</div>
                                <div class="agent-subtext">{{ $adj->personnel->corporation ?? '' }}</div>
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
                        {{ $adj->type === 'moins_percu' ? '+' : '-' }}{{ number_format($adj->getMontantPourMois(date('Y-m')), 0, ',', ' ') }} F / mois
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
                        @if(!empty($adj->echeances))
                            <button type="button" class="btn-detail-schedule" onclick="toggleSchedule('sch-{{ $adj->id }}')">
                                📅 Voir ({{ count($adj->echeances) }} mois)
                            </button>
                        @else
                            <span style="color: #9ca3af; font-size: 0.78rem;">Standard</span>
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

                {{-- Ligne détail d'échéancier si présent --}}
                @if(!empty($adj->echeances))
                <tr id="sch-{{ $adj->id }}" class="schedule-detail-row" style="display: none; background: #f9fafb;">
                    <td colspan="9" style="padding: 0.8rem 1.25rem;">
                        <div style="font-weight: 600; font-size: 0.8rem; color: #374151; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>🗓️ Échéancier détaillé par mois pour {{ $adj->personnel->nom_complet ?? '' }} :</span>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($adj->echeances as $ech)
                                @php
                                    $isCurrent = ($ech['mois'] ?? '') === date('Y-m');
                                    $dt = \Carbon\Carbon::createFromFormat('Y-m', $ech['mois'] ?? date('Y-m'));
                                    $moisNom = $dt ? ucfirst($dt->translatedFormat('F Y')) : $ech['mois'];
                                @endphp
                                <div style="background: {{ $isCurrent ? '#ecfdf5' : '#ffffff' }}; border: 1px solid {{ $isCurrent ? '#a7f3d0' : '#e5e7eb' }}; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem;">
                                    <strong style="color: {{ $isCurrent ? '#065f46' : '#111827' }};">{{ $moisNom }}</strong> :
                                    <span style="font-weight: 700; color: #dc2626;">{{ number_format($ech['montant'], 0, ',', ' ') }} F</span>
                                    @if($isCurrent) <span style="font-size: 0.7rem; background: #065f46; color: #fff; padding: 1px 5px; border-radius: 4px; margin-left: 4px;">Ce mois</span> @endif
                                </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af; padding: 4rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">💸</div>
                        Aucun plan d'ajustement salarial enregistré pour ce centre.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale d'enregistrement de plan --}}
<dialog id="modalAddAdjustment" class="modal" style="border: none; border-radius: 20px; padding: 0; max-width: 650px; width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: linear-gradient(135deg, #1a5c45 0%, #227055 100%); color: #fff;">
        <h2 style="margin: 0; font-size: 1.15rem; font-weight: 600; color: #fff;">Nouveau plan d'ajustement salarial</h2>
        <button onclick="document.getElementById('modalAddAdjustment').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: rgba(255,255,255,0.7); padding: 0;">&times;</button>
    </div>
    
    <form action="{{ route('pay-adjustments.store') }}" method="POST" id="formAdjustment">
        @csrf
        <div class="modal-body" style="padding: 1.5rem; max-height: 75vh; overflow-y: auto;">
            
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
                <input type="text" name="libelle" id="libelle" class="form-input" placeholder="ex. Remboursement Avance Scolaire / Frais d'hospitalisation" required>
            </div>

            <div id="wrapperDetteGlobale" class="form-group" style="margin-bottom: 1.25rem;">
                <label for="montant_total" class="form-label">Montant global de la dette à rembourser (FCFA) *</label>
                <input type="number" name="montant_total" id="montant_total" class="form-input" placeholder="ex. 10675" oninput="updateCalculsEcheancier()">
            </div>

            {{-- Échéancier mensuel personnalisé --}}
            <div id="wrapperEcheancier" style="margin-bottom: 1.25rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div>
                        <strong style="font-size: 0.88rem; color: #111827;">Plan de remboursement mensuel (Échéancier)</strong>
                        <p style="margin: 0; font-size: 0.75rem; color: #6b7280;">Sélectionnez chaque mois et saisissez le montant correspondant (à partir du mois en cours).</p>
                    </div>
                    <button type="button" class="btn-add-month" onclick="addMoisRow()">
                        + Ajouter un mois
                    </button>
                </div>

                <div id="echeancesContainer" style="display: flex; flex-direction: column; gap: 0.6rem;">
                    {{-- Les lignes de mois seront générées dynamiquement en JS --}}
                </div>

                {{-- Récapitulatif solde --}}
                <div id="recapEcheancier" style="margin-top: 0.8rem; padding-top: 0.6rem; border-top: 1px dashed #d1d5db; display: flex; justify-content: space-between; font-size: 0.83rem;">
                    <span>Total échéances saisies : <strong id="totalSaisi">0 F</strong></span>
                    <span>Solde restant : <strong id="soldeRestant">0 F</strong></span>
                </div>
            </div>

            {{-- Champ simple pour Moins-perçu --}}
            <div id="wrapperMontantMensuelSimple" class="form-group" style="margin-bottom: 1.25rem; display: none;">
                <label for="montant_mensuel" class="form-label">Montant mensuel à verser (FCFA) *</label>
                <input type="number" name="montant_mensuel" id="montant_mensuel" class="form-input" placeholder="ex. 15000">
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
.premium-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    overflow: hidden;
}
.premium-table { width: 100%; border-collapse: collapse; }
.premium-table th {
    background-color: #f9fafb; border-bottom: 1.5px solid #e5e7eb;
    color: #4b5563; font-size: 0.75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.25rem;
}
.premium-table td { padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.premium-table tr:hover td { background-color: #f9fafb; }

.agent-profile { display: flex; align-items: center; gap: 0.75rem; }
.agent-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, #1a5c45 0%, #2e7d62 100%);
    color: #ffffff; font-weight: bold; font-size: 0.8rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.agent-name { font-size: 0.9rem; font-weight: 600; color: #111827; line-height: 1.3; }
.agent-subtext { font-size: 0.75rem; color: #6b7280; }

.type-pill { font-size: 0.72rem; padding: 3px 8px; border-radius: 6px; font-weight: 600; display: inline-block; border: 1px solid transparent; }
.type-avance { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
.type-medicaux { background: #fdf2f2; color: #9b1c1c; border-color: #fde8e8; }
.type-moinspercu { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }

.status-pill { font-size: 0.72rem; padding: 3px 8px; border-radius: 99px; font-weight: 600; display: inline-block; }
.status-actif { background: #d1fae5; color: #065f46; }
.status-termine { background: #f3f4f6; color: #4b5563; }

.btn-premium {
    background: linear-gradient(135deg, #1a5c45 0%, #227055 100%);
    color: #ffffff; border: none; padding: 0.55rem 1.25rem; border-radius: 10px;
    font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center;
    transition: all 0.2s ease; box-shadow: 0 4px 10px rgba(26, 92, 69, 0.15);
}
.btn-premium:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(26, 92, 69, 0.25); }

.filter-select { padding: 0.45rem 2.25rem 0.45rem 0.75rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.85rem; font-weight: 500; color: #374151; background-color: #fff; cursor: pointer; }

.action-btn { padding: 4px 8px; font-size: 0.75rem; font-weight: 600; border-radius: 6px; border: 1px solid transparent; cursor: pointer; transition: all 0.15s; }
.btn-archive { background-color: #f3f4f6; color: #4b5563; border-color: #d1d5db; }
.btn-archive:hover { background-color: #e5e7eb; color: #1f2937; }
.btn-activate { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.btn-activate:hover { background-color: #d1fae5; }
.btn-delete { background-color: #fdf2f2; color: #b91c1c; border-color: #fde8e8; }
.btn-delete:hover { background-color: #fde8e8; }

.btn-detail-schedule {
    background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;
    padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;
    cursor: pointer; transition: all 0.15s;
}
.btn-detail-schedule:hover { background: #dcfce7; }

.form-label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
.form-input { width: 100%; padding: 0.55rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem; box-sizing: border-box; transition: border-color 0.15s; background-color: #fff; }
.form-input:focus { border-color: var(--col-primary, #1a5c45); outline: none; }

.action-btn-submit { background-color: var(--col-primary, #1a5c45); color: #fff; border: none; padding: 0.5rem 1.25rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 600; }
.action-btn-submit:hover { background-color: #124030; }
.action-btn-back { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 0.5rem 1.25rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 500; }

.btn-add-month {
    background: #1a5c45; color: #fff; border: none; padding: 4px 10px;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
}
.btn-add-month:hover { background: #124030; }
.btn-remove-row {
    background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;
    border-radius: 6px; width: 28px; height: 28px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-weight: bold;
}
.btn-remove-row:hover { background: #fee2e2; }

.ech-row { display: flex; gap: 0.5rem; align-items: center; }
.ech-row select, .ech-row input { padding: 0.45rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.85rem; }
</style>
@endpush

@push('scripts')
<script>
    const currentMonthStr = "{{ date('Y-m') }}"; // Ex: "2026-08"

    function toggleSchedule(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }
    }

    function toggleFormFields(value) {
        const wrapperDette = document.getElementById('wrapperDetteGlobale');
        const wrapperEcheancier = document.getElementById('wrapperEcheancier');
        const wrapperSimple = document.getElementById('wrapperMontantMensuelSimple');
        const inputDette = document.getElementById('montant_total');
        const inputSimple = document.getElementById('montant_mensuel');

        if (value === 'moins_percu') {
            wrapperDette.style.display = 'none';
            wrapperEcheancier.style.display = 'none';
            wrapperSimple.style.display = 'block';
            inputDette.removeAttribute('required');
            inputSimple.setAttribute('required', 'required');
        } else {
            wrapperDette.style.display = 'block';
            wrapperEcheancier.style.display = 'block';
            wrapperSimple.style.display = 'none';
            inputDette.setAttribute('required', 'required');
            inputSimple.removeAttribute('required');
        }
    }

    function getNextMonthCode(lastMonthCode) {
        if (!lastMonthCode) return currentMonthStr;
        const parts = lastMonthCode.split('-');
        let y = parseInt(parts[0], 10);
        let m = parseInt(parts[1], 10);
        m++;
        if (m > 12) {
            m = 1;
            y++;
        }
        return y + '-' + (m < 10 ? '0' + m : m);
    }

    function formatMonthFrench(monthCode) {
        const parts = monthCode.split('-');
        const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        const mIdx = parseInt(parts[1], 10) - 1;
        return months[mIdx] + ' ' + parts[0];
    }

    let echeanceIndex = 0;

    function addMoisRow(defaultMonthCode = null, defaultAmount = '') {
        const container = document.getElementById('echeancesContainer');
        const existingRows = container.querySelectorAll('.ech-row');
        
        let monthCode = defaultMonthCode;
        if (!monthCode) {
            if (existingRows.length > 0) {
                const lastSelect = existingRows[existingRows.length - 1].querySelector('select');
                monthCode = getNextMonthCode(lastSelect.value);
            } else {
                monthCode = currentMonthStr;
            }
        }

        const idx = echeanceIndex++;
        const row = document.createElement('div');
        row.className = 'ech-row';
        row.id = 'echRow_' + idx;

        // Générer la liste des 24 prochains mois à partir de currentMonthStr
        let monthOptionsHtml = '';
        let mCursor = currentMonthStr;
        for (let i = 0; i < 24; i++) {
            const isSelected = (mCursor === monthCode) ? 'selected' : '';
            monthOptionsHtml += `<option value="${mCursor}" ${isSelected}>${formatMonthFrench(mCursor)}</option>`;
            mCursor = getNextMonthCode(mCursor);
        }

        row.innerHTML = `
            <div style="flex: 1;">
                <select name="echeances[${idx}][mois]" class="form-input" style="padding: 0.45rem;" onchange="updateCalculsEcheancier()" required>
                    ${monthOptionsHtml}
                </select>
            </div>
            <div style="flex: 1;">
                <input type="number" name="echeances[${idx}][montant]" class="form-input ech-montant-input" placeholder="Montant (ex. 3000)" value="${defaultAmount}" oninput="updateCalculsEcheancier()" required min="1">
            </div>
            <button type="button" class="btn-remove-row" onclick="removeMoisRow(${idx})">×</button>
        `;

        container.appendChild(row);
        updateCalculsEcheancier();
    }

    function removeMoisRow(idx) {
        const row = document.getElementById('echRow_' + idx);
        if (row) row.remove();
        updateCalculsEcheancier();
    }

    function updateCalculsEcheancier() {
        const totalDette = parseFloat(document.getElementById('montant_total').value) || 0;
        const inputs = document.querySelectorAll('.ech-montant-input');
        let sum = 0;
        inputs.forEach(inp => {
            sum += parseFloat(inp.value) || 0;
        });

        const solde = totalDette - sum;
        document.getElementById('totalSaisi').textContent = new Intl.NumberFormat('fr-FR').format(sum) + ' F';
        
        const soldeEl = document.getElementById('soldeRestant');
        soldeEl.textContent = new Intl.NumberFormat('fr-FR').format(solde) + ' F';
        if (solde === 0 && totalDette > 0) {
            soldeEl.style.color = '#059669';
        } else if (solde < 0) {
            soldeEl.style.color = '#dc2626';
        } else {
            soldeEl.style.color = '#b45309';
        }
    }

    function openAddModal() {
        const container = document.getElementById('echeancesContainer');
        container.innerHTML = '';
        echeanceIndex = 0;
        // Ajouter 1 premier mois par défaut
        addMoisRow(currentMonthStr);
        document.getElementById('modalAddAdjustment').showModal();
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleFormFields(document.getElementById('type').value);
    });
</script>
@endpush
@endsection


