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
    
    {{-- Boutons d'exports R1, R2, R3, R4 --}}
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('pay-periods.registre', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="R1 - Registre de Paie Mensuel">
            📊 R1 - Registre de Paie
        </a>
        <a href="{{ route('pay-periods.cnss', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="R2 - Déclaration Mensuelle CNSS">
            🛡️ R2 - Déclaration CNSS
        </a>
        <a href="{{ route('pay-periods.its', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="R3 - Déclaration Mensuelle ITS">
            💼 R3 - Déclaration ITS
        </a>
        <a href="{{ route('pay-periods.virements', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="export-btn" title="R4 - État de paiement Banque (Filtre par banque)">
            🏦 R4 - État de paiement
        </a>
        @if(!auth()->user()->isReadOnly())
        <form action="{{ route('pay-periods.envoyer-email', $payPeriod->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Confirmer l\'envoi par email des bulletins de paie de tout le personnel actif de {{ $selectedCentre->nom }} ?')">
            @csrf
            <input type="hidden" name="centre_id" value="{{ $selectedCentre->id }}">
            <button type="submit" class="export-btn" style="background: #1a5c45; color: #fff; border-color: #1a5c45; font-weight: 600;" title="Envoyer le bulletin de paie par email à chaque salarié du centre">
                📧 Envoyer Bulletins par Email
            </button>
        </form>
        @endif
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
                    $totalPrimes = (float)$slip->prime_caisse + (float)$slip->prime_risque + (float)$slip->prime_responsabilite + (float)$slip->prime_garde + (float)$slip->prime_specialite + (float)$slip->autre_prime;
                    $ajustementsNet = (float)$slip->moins_percu_rembourse 
                                    - (float)$slip->frais_medicaux 
                                    - (float)$slip->avance_salaire 
                                    - (float)$slip->mise_a_pied 
                                    - (float)$slip->taxe_radio 
                                    - (float)$slip->taxe_tele 
                                    - (float)$slip->trop_percu_net;
                @endphp
                <tr style="{{ $slip->is_fictif ? 'opacity: 0.65; background: #f9fafb;' : '' }}">
                    <td>
                        <div class="agent-profile">
                            <div class="agent-avatar" style="{{ $slip->is_fictif ? 'background: #9ca3af;' : '' }}">{{ strtoupper(substr($slip->personnel->nom ?? '', 0, 1)) }}{{ strtoupper(substr($slip->personnel->prenoms ?? '', 0, 1)) }}</div>
                            <div>
                                <div class="agent-name">
                                    {{ $slip->personnel->nom_complet ?? 'N/A' }}
                                    @if($slip->is_fictif)
                                        <span style="background: #f3f4f6; color: #4b5563; border: 1px dashed #9ca3af; padding: 1px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 4px; font-style: italic;">
                                            👻 Fictif (Transféré)
                                        </span>
                                    @endif
                                </div>
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
                    <td class="text-right font-medium" style="font-size: 0.85rem;">
                        <div style="color: {{ $ajustementsNet >= 0 ? '#059669' : '#dc2626' }}; font-weight: 700;">
                            {{ $ajustementsNet >= 0 ? '+' : '' }}{{ number_format($ajustementsNet, 0, ',', ' ') }} F
                        </div>
                        @if($totalPrimes > 0)
                            <div style="font-size: 0.71rem; color: #059669;">(Primes: +{{ number_format($totalPrimes, 0, ',', ' ') }} F)</div>
                        @endif
                        @if($slip->avance_salaire > 0)
                            <div style="font-size: 0.71rem; color: #dc2626;">(Avance: -{{ number_format($slip->avance_salaire, 0, ',', ' ') }} F)</div>
                        @endif
                        @if($slip->frais_medicaux > 0)
                            <div style="font-size: 0.71rem; color: #dc2626;">(Soins: -{{ number_format($slip->frais_medicaux, 0, ',', ' ') }} F)</div>
                        @endif
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
                            @if(!$slip->is_fictif)
                                <a href="{{ route('pay-slips.solde-tout-compte', $slip->id) }}" target="_blank" class="action-btn action-btn-solde" title="Générer reçu Solde de tout compte">
                                    📄 Solde compte
                                </a>
                                @if($payPeriod->statut === 'ouvert' && !auth()->user()->isReadOnly())
                                    <button type="button" class="action-btn action-btn-ajuste" onclick="openVariablesModal({{ json_encode($slip) }})">
                                        ⚙️ Ajuster
                                    </button>
                                @endif
                            @else
                                <span style="font-size: 0.72rem; color: #6b7280; font-style: italic; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; border: 1px dashed #d1d5db;">
                                    🔒 Fictif (Lecture seule)
                                </span>
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
                    <h4 class="section-title">⏱️ Temps de travail & Absences (Base 24 jours)</h4>
                    <div class="form-group">
                        <label class="form-label">Jours d'absence du mois</label>
                        <input type="number" name="jours_absence" id="varJoursAbsence" class="form-input" min="0" max="24">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jours de Mise à Pied disciplinaire</label>
                        <input type="number" name="jours_mise_a_pied" id="varJoursMiseAPied" class="form-input" min="0" max="24">
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
                    <h4 class="section-title">⭐ Primes Fixes & Exceptionnelles</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem;">
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
                            <label class="form-label">Spécialité (FCFA)</label>
                            <input type="number" name="prime_specialite" id="varPrimeSpecialite" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Autre Prime (FCFA)</label>
                            <input type="number" name="autre_prime" id="varAutrePrime" class="form-input" min="0">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Trop perçu Brut (Déduction)</label>
                            <input type="number" name="trop_percu_brut" id="varTropPercuBrut" class="form-input" min="0">
                        </div>
                    </div>
                </div>

                {{-- SECTION AJUSTEMENTS NET --}}
                <div class="form-section-card" style="grid-column: 1 / -1;">
                    <h4 class="section-title">💸 Prêts, Avances & Saisies sur Net</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Prélèvement Avance (FCFA)</label>
                            <input type="number" name="avance_salaire" id="varAvanceSalaire" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Frais médicaux soins (FCFA)</label>
                            <input type="number" name="frais_medicaux" id="varFraisMedicaux" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Délégation / Saisie-arrêt</label>
                            <input type="number" name="delegation_saisie" id="varDelegationSaisie" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prêt long terme</label>
                            <input type="number" name="pret_long_terme" id="varPretLongTerme" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Retenue compte tiers</label>
                            <input type="number" name="retenue_compte_tiers" id="varRetenueCompteTiers" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prêt Ecobank</label>
                            <input type="number" name="pret_ecobank" id="varPretEcobank" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assurance ASCOMA</label>
                            <input type="number" name="assurance_ascoma" id="varAssuranceAscoma" class="form-input" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Trop perçu Net (Déduction)</label>
                            <input type="number" name="trop_percu_net" id="varTropPercuNet" class="form-input" min="0">
                        </div>
                        <div class="form-group" style="grid-column: span 4;">
                            <label class="form-label">Moins-perçu (Remboursement)</label>
                            <input type="number" name="moins_percu_rembourse" id="varMoinsPercu" class="form-input" min="0">
                        </div>
                    </div>
                </div>

                {{-- SECTION BANQUE --}}
                <div class="form-section-card">
                    <h4 class="section-title">💳 Règlement & Banque</h4>
                    <div class="form-group">
                        <label class="form-label">Banque de virement</label>
                        <select name="banque" id="varBanque" class="form-input">
                            <option value="BOA">BOA</option>
                            <option value="BIIC">BIIC</option>
                            <option value="ECOBANK">ECOBANK</option>
                            <option value="UBA">UBA</option>
                            <option value="ARCHEVECHE">ARCHEVECHE</option>
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
    background: linear-gradient(135deg, var(--col-primary, #1a5c45) 0%, #227055 100%);
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(26, 92, 69, 0.25);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.action-btn-ajuste:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26, 92, 69, 0.35);
    background: linear-gradient(135deg, #124030 0%, #1a5c45 100%);
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
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.15rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    transition: box-shadow 0.2s ease;
}
.form-section-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
}
.section-title {
    margin: 0 0 0.85rem 0;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    border-bottom: 1.5px solid #f1f5f9;
    padding-bottom: 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.form-label {
    display: block;
    font-size: 0.76rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.form-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    font-size: 0.88rem;
    font-weight: 500;
    box-sizing: border-box;
    background-color: #f8fafc;
    transition: all 0.2s ease;
}
.form-input:focus {
    border-color: var(--col-primary, #1a5c45);
    background-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(26, 92, 69, 0.15);
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
        
        // Calcul jours_mise_a_pied sur base statutaire de 24 jours
        const joursMiseAPied = 24 - parseInt(slip.jours_travailles) - parseInt(slip.jours_absence || 0);
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
        document.getElementById('varPrimeSpecialite').value = parseFloat(slip.prime_specialite) || 0;
        document.getElementById('varAutrePrime').value = parseFloat(slip.autre_prime) || 0;
        
        document.getElementById('varTropPercuBrut').value = parseFloat(slip.trop_percu_brut) || 0;
        document.getElementById('varFraisMedicaux').value = parseFloat(slip.frais_medicaux) || 0;
        document.getElementById('varAvanceSalaire').value = parseFloat(slip.avance_salaire) || 0;
        
        document.getElementById('varDelegationSaisie').value = parseFloat(slip.delegation_saisie) || 0;
        document.getElementById('varPretLongTerme').value = parseFloat(slip.pret_long_terme) || 0;
        document.getElementById('varRetenueCompteTiers').value = parseFloat(slip.retenue_compte_tiers) || 0;
        document.getElementById('varPretEcobank').value = parseFloat(slip.pret_ecobank) || 0;
        document.getElementById('varAssuranceAscoma').value = parseFloat(slip.assurance_ascoma) || 0;
        
        document.getElementById('varTropPercuNet').value = parseFloat(slip.trop_percu_net) || 0;
        document.getElementById('varMoinsPercu').value = parseFloat(slip.moins_percu_rembourse) || 0;
        
        document.getElementById('varBanque').value = slip.banque || 'BOA';
        document.getElementById('varModeReglement').value = slip.mode_reglement || 'Virement';
        document.getElementById('varNumeroCompte').value = slip.numero_compte || '';
        
        document.getElementById('modalVariables').showModal();
    }
</script>
@endsection
