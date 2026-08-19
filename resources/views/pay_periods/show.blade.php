@extends('layouts.app')

@section('title', 'Détails de la Paie - ' . $payPeriod->label)

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <a href="{{ route('pay-periods.index') }}" class="back-link">
            &larr; Retour aux périodes de paie
        </a>
        <h1 class="page-title" style="font-size: 1.8rem; font-weight: 700; color: #111827; margin: 0.5rem 0 0.25rem 0;">Mois de paie : {{ $payPeriod->label }}</h1>
        <p class="page-subtitle" style="font-size: 0.9rem; margin: 0;">Statut de calcul : 
            @if($payPeriod->statut === 'ouvert')
                <span class="status-pill status-ouvert">Ouvert (Calculs modifiables)</span>
            @else
                <span class="status-pill status-cloture">Clôturé & Verrouillé (Lecture seule)</span>
            @endif
        </p>
    </div>
    
    <div>
        @if($payPeriod->statut === 'ouvert' && !auth()->user()->isReadOnly())
            <form action="{{ route('pay-periods.cloturer', $payPeriod->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir CLÔTURER la paie pour ce mois ? Cette action est irréversible, toutes les valeurs seront figées et les échéanciers d\'avances/soins seront décrémentés de 1 mois.')">
                @csrf
                <button type="submit" class="btn-cloture">
                    🔒 Clôturer le mois de paie
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Section Filtre de Centre et Boutons d'Exports --}}
<div class="action-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-weight: 600; font-size: 0.88rem; color: #374151; display: inline-flex; align-items: center; gap: 0.35rem;">
            🏥 Centre :
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
    
    {{-- Boutons d'exports --}}
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('pay-periods.livre', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="Livre des salaires">
            📄 Livre de paie
        </a>
        <a href="{{ route('pay-periods.registre', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="Registre complet Excel">
            📊 Registre de paie
        </a>
        <a href="{{ route('pay-periods.virements', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="Banques de virement">
            🏦 Virements Banque
        </a>
        <a href="{{ route('pay-periods.cnss', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="Déclaration sociale CNSS">
            🛡️ Déclaration CNSS
        </a>
        <a href="{{ route('pay-periods.its', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="Déclaration fiscale ITS">
            💼 Déclaration ITS
        </a>
    </div>
</div>

{{-- Grille des bulletins --}}
<div class="card premium-card">
    <div class="table-responsive">
        <table class="table premium-table">
            <thead>
                <tr>
                    <th>Salarié</th>
                    <th>Grille Contrat</th>
                    <th class="text-right">Salaire Base</th>
                    <th class="text-right">Salaire Brut</th>
                    <th class="text-right">Charges Sociales & ITS</th>
                    <th class="text-right">Ajustements Net</th>
                    <th class="text-right">Salaire Net à payer</th>
                    <th>Règlement</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slips as $slip)
                @php
                    $ajustementsNet = (float)$slip->moins_percu_rembourse 
                                    - (float)$slip->frais_medicaux 
                                    - (float)$slip->avance_salaire 
                                    - (float)$slip->mise_a_pied 
                                    - (float)$slip->taxe_radio 
                                    - (float)$slip->taxe_tele 
                                    - (float)$slip->trop_percu_net;
                @endphp
                <tr>
                    <td>
                        <div class="agent-profile">
                            <div class="agent-avatar">{{ substr($slip->personnel->nom, 0, 1) }}{{ substr($slip->personnel->prenoms, 0, 1) }}</div>
                            <div>
                                <div class="agent-name">{{ $slip->personnel->nom_complet }}</div>
                                <div class="agent-subtext">{{ $slip->poste }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($slip->categorie)
                            <span class="grille-badge">{{ $slip->categorie }} - E{{ $slip->echelon }}</span>
                        @else
                            <span class="text-muted" style="font-size: 0.78rem; font-style: italic;">Hors grille</span>
                        @endif
                    </td>
                    <td class="text-right font-medium">{{ number_format($slip->salaire_base, 0, ',', ' ') }} F</td>
                    <td class="text-right font-medium">{{ number_format($slip->salaire_brut, 0, ',', ' ') }} F</td>
                    <td class="text-right text-deduction" style="font-size: 0.85rem;">
                        -{{ number_format($slip->cotisation_sociale_salarie + $slip->impot_its, 0, ',', ' ') }} F
                    </td>
                    <td class="text-right font-medium" style="font-size: 0.85rem; color: {{ $ajustementsNet >= 0 ? '#059669' : '#dc2626' }};">
                        {{ $ajustementsNet >= 0 ? '+' : '' }}{{ number_format($ajustementsNet, 0, ',', ' ') }} F
                    </td>
                    <td class="text-right font-bold text-net" style="font-size: 1.05rem;">
                        {{ number_format($slip->salaire_net, 0, ',', ' ') }} FCFA
                    </td>
                    <td>
                        <div style="font-weight: 600; font-size: 0.82rem; color: #374151;">{{ $slip->mode_reglement }}</div>
                        @if($slip->banque)
                            <span style="font-size: 0.72rem; color: #6b7280;">({{ $slip->banque }})</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                            <a href="{{ route('pay-slips.pdf', $slip->id) }}" target="_blank" class="action-btn action-btn-pdf" title="Télécharger le bulletin">
                                🖨️ Bulletin
                            </a>
                            <a href="{{ route('pay-slips.solde-tout-compte', $slip->id) }}" target="_blank" class="action-btn action-btn-solde" title="Générer reçu Solde de tout compte">
                                📄 Solde compte
                            </a>
                            @if($payPeriod->statut === 'ouvert' && !auth()->user()->isReadOnly())
                                <button type="button" class="action-btn action-btn-ajuste" onclick="openVariablesModal({{ json_encode($slip) }})">
                                    ⚙️ Ajuster
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af; padding: 4rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👨‍⚕️</div>
                        Aucun agent actif enregistré pour la paie dans ce centre.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale d'ajustement des variables - Totalement redessinée --}}
<dialog id="modalVariables" class="modal" style="border: none; border-radius: 20px; padding: 0; max-width: 720px; width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15); overflow: hidden;">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: linear-gradient(135deg, #1a5c45 0%, #227055 100%); color: #fff;">
        <h2 style="margin: 0; font-size: 1.15rem; font-weight: 600; color: #fff;">Ajustements : <span id="varAgentName" style="color: #63e2b7;">Agent</span></h2>
        <button onclick="document.getElementById('modalVariables').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: rgba(255,255,255,0.7); padding: 0;">&times;</button>
    </div>
    
    <form id="formVariables" method="POST">
        @csrf
        @method('PUT')
        
        <div class="modal-body" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto; background-color: #f9fafb;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                
                {{-- SECTION TEMPS --}}
                <div class="form-section-card">
                    <h4 class="section-title">⏱️ Temps de travail & Absences</h4>
                    <div class="form-group">
                        <label class="form-label">Jours d'absence du mois</label>
                        <input type="number" name="jours_absence" id="varJoursAbsence" class="form-input" min="0" max="30">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jours de Mise à Pied disciplinaire</label>
                        <input type="number" name="jours_mise_a_pied" id="varJoursMiseAPied" class="form-input" min="0" max="30">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heures supplémentaires</label>
                        <input type="number" name="heures_supplementaires" id="varHeuresSup" class="form-input" min="0" step="0.5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heures d'astreinte / Gardes</label>
                        <input type="number" name="heures_astreinte" id="varHeuresAstreinte" class="form-input" min="0" step="0.5">
                    </div>
                </div>

                {{-- SECTION INDEMNITES --}}
                <div class="form-section-card">
                    <h4 class="section-title">💰 Indemnités & Ajustement Brut</h4>
                    <div class="form-group">
                        <label class="form-label">Logement (FCFA)</label>
                        <input type="number" name="indemnite_logement" id="varIndemLogement" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Transport (FCFA)</label>
                        <input type="number" name="indemnite_transport" id="varIndemTransport" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Autre Indemnité (FCFA)</label>
                        <input type="number" name="autre_indemnite" id="varAutreIndemnite" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Écart d'ajustement Brut (FCFA)</label>
                        <input type="number" name="ecart" id="varEcart" class="form-input">
                    </div>
                </div>

                {{-- SECTION PRIMES --}}
                <div class="form-section-card" style="grid-column: 1 / -1;">
                    <h4 class="section-title">⭐ Primes Exceptionnelles</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Caisse (FCFA)</label>
                            <input type="number" name="prime_caisse" id="varPrimeCaisse" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Risque (FCFA)</label>
                            <input type="number" name="prime_risque" id="varPrimeRisque" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Responsabilité (FCFA)</label>
                            <input type="number" name="prime_responsabilite" id="varPrimeResponsabilite" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Garde (FCFA)</label>
                            <input type="number" name="prime_garde" id="varPrimeGarde" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Autre Prime (FCFA)</label>
                            <input type="number" name="autre_prime" id="varAutrePrime" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Trop perçu Brut (Déduction)</label>
                            <input type="number" name="trop_percu_brut" id="varTropPercuBrut" class="form-input" min="0">
                        </div>
                    </div>
                </div>

                {{-- SECTION AJUSTEMENTS NET --}}
                <div class="form-section-card">
                    <h4 class="section-title">💸 Ajustements & Retenues sur Net</h4>
                    <div class="form-group">
                        <label class="form-label">Frais médicaux soins (FCFA)</label>
                        <input type="number" name="frais_medicaux" id="varFraisMedicaux" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prélèvement Avance (FCFA)</label>
                        <input type="number" name="avance_salaire" id="varAvanceSalaire" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trop perçu Net (Déduction)</label>
                        <input type="number" name="trop_percu_net" id="varTropPercuNet" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Moins-perçu (Remboursement)</label>
                        <input type="number" name="moins_percu_rembourse" id="varMoinsPercu" class="form-input" min="0">
                    </div>
                </div>

                {{-- SECTION BANQUE --}}
                <div class="form-section-card">
                    <h4 class="section-title">💳 Règlement & Banque</h4>
                    <div class="form-group">
                        <label class="form-label">Banque de virement</label>
                        <select name="banque" id="varBanque" class="form-input">
                            <option value="BOA">BOA</option>
                            <option value="Ecobank">Ecobank</option>
                            <option value="Archevêché">Archevêché (Caisse)</option>
                            <option value="Espèces">Espèces</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mode de règlement</label>
                        <select name="mode_reglement" id="varModeReglement" class="form-input">
                            <option value="Virement">Virement</option>
                            <option value="Espèces">Espèces (Caisse)</option>
                            <option value="Chèque">Chèque</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1; margin-top: 0.5rem;">
                        <label class="form-label">Numéro de compte / Réf.</label>
                        <input type="text" name="numero_compte" id="varNumeroCompte" class="form-input">
                    </div>
                </div>

            </div>
        </div>
        
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem; background-color: #fff;">
            <button type="button" class="action-btn-back" onclick="document.getElementById('modalVariables').close()">Annuler</button>
            <button type="submit" class="action-btn-submit">Calculer & Sauvegarder</button>
        </div>
    </form>
</dialog>

@push('styles')
<style>
/* CSS Périodes de Paie Show Premium */
.back-link {
    color: #6b7280;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: color 0.15s;
}
.back-link:hover {
    color: var(--col-primary, #1a5c45);
}

.status-pill {
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 99px;
    font-weight: 600;
    display: inline-block;
}
.status-ouvert {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.status-cloture {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #d1d5db;
}

.btn-cloture {
    background: #ef4444;
    color: #fff;
    border: none;
    padding: 0.55rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15);
    transition: all 0.2s;
}
.btn-cloture:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(239, 68, 68, 0.25);
}

.export-btn {
    background-color: #ffffff;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 0.45rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.15s;
}
.export-btn:hover {
    background-color: #f9fafb;
    border-color: #9ca3af;
    transform: translateY(-1px);
}

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

.grille-badge {
    font-size: 0.75rem;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 6px;
    color: #374151;
    font-family: monospace;
    font-weight: bold;
}

.text-deduction {
    color: #ef4444;
}

.text-net {
    color: var(--col-primary, #1a5c45);
}

.font-medium { font-weight: 500; }
.font-bold { font-weight: 700; }

.action-btn {
    padding: 4px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s;
}
.action-btn-pdf {
    background-color: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}
.action-btn-pdf:hover {
    background-color: #d1fae5;
}
.action-btn-solde {
    background-color: #fffbeb;
    color: #d97706;
    border-color: #fef3c7;
}
.action-btn-solde:hover {
    background-color: #fef3c7;
}
.action-btn-ajuste {
    background-color: var(--col-primary, #1a5c45);
    color: #ffffff;
}
.action-btn-ajuste:hover {
    background-color: #124030;
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

/* Modale variables cards */
.form-section-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
}
.section-title {
    margin: 0 0 0.75rem 0;
    font-size: 0.88rem;
    font-weight: 700;
    color: #1f2937;
    border-bottom: 1.5px solid #f3f4f6;
    padding-bottom: 0.35rem;
}

.form-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 0.25rem;
}
.form-input {
    width: 100%;
    padding: 0.45rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 0.85rem;
    box-sizing: border-box;
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
    function openVariablesModal(slip) {
        document.getElementById('varAgentName').innerText = slip.personnel.nom + ' ' + slip.personnel.prenoms;
        
        // Attribuer l'action du formulaire
        document.getElementById('formVariables').action = '/paie/bulletins/' + slip.id;
        
        // Charger les données dans les inputs
        document.getElementById('varJoursAbsence').value = slip.jours_absence || 0;
        
        // Calcul jours_mise_a_pied
        const joursMiseAPied = 30 - parseInt(slip.jours_travailles) - parseInt(slip.jours_absence || 0);
        document.getElementById('varJoursMiseAPied').value = joursMiseAPied > 0 ? joursMiseAPied : 0;
        
        document.getElementById('varHeuresSup').value = parseFloat(slip.heures_supplementaires) || 0;
        document.getElementById('varHeuresAstreinte').value = parseFloat(slip.heures_astreinte) || 0;
        
        document.getElementById('varIndemLogement').value = parseFloat(slip.indemnite_logement) || 0;
        document.getElementById('varIndemTransport').value = parseFloat(slip.indemnite_transport) || 0;
        document.getElementById('varAutreIndemnite').value = parseFloat(slip.autre_indemnite) || 0;
        document.getElementById('varEcart').value = parseFloat(slip.ecart) || 0;
        
        document.getElementById('varPrimeCaisse').value = parseFloat(slip.prime_caisse) || 0;
        document.getElementById('varPrimeRisque').value = parseFloat(slip.prime_risque) || 0;
        document.getElementById('varPrimeResponsabilite').value = parseFloat(slip.prime_responsabilite) || 0;
        document.getElementById('varPrimeGarde').value = parseFloat(slip.prime_garde) || 0;
        document.getElementById('varAutrePrime').value = parseFloat(slip.autre_prime) || 0;
        
        document.getElementById('varTropPercuBrut').value = parseFloat(slip.trop_percu_brut) || 0;
        document.getElementById('varFraisMedicaux').value = parseFloat(slip.frais_medicaux) || 0;
        document.getElementById('varAvanceSalaire').value = parseFloat(slip.avance_salaire) || 0;
        document.getElementById('varTropPercuNet').value = parseFloat(slip.trop_percu_net) || 0;
        document.getElementById('varMoinsPercu').value = parseFloat(slip.moins_percu_rembourse) || 0;
        
        document.getElementById('varBanque').value = slip.banque || 'Archevêché';
        document.getElementById('varModeReglement').value = slip.mode_reglement || 'Virement';
        document.getElementById('varNumeroCompte').value = slip.numero_compte || '';
        
        document.getElementById('modalVariables').showModal();
    }
</script>
@endsection
