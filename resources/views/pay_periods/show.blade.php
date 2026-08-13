@extends('layouts.app')

@section('title', 'Détails de la Paie - ' . $payPeriod->label)

@section('content')
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <a href="{{ route('pay-periods.index') }}" style="color: #6b7280; text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Retour aux périodes
        </a>
        <h1 class="page-title">Mois de paie : {{ $payPeriod->label }}</h1>
        <p class="page-subtitle">Statut actuel : 
            @if($payPeriod->statut === 'ouvert')
                <span style="font-weight: 600; color: #10b981;">Ouvert (Calculs modifiables)</span>
            @else
                <span style="font-weight: 600; color: #6b7280;">Clôturé & Figé (Lecture seule)</span>
            @endif
        </p>
    </div>
    
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        @if($payPeriod->statut === 'ouvert' && !auth()->user()->isReadOnly())
            <form action="{{ route('pay-periods.cloturer', $payPeriod->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir CLÔTURER la paie pour ce mois ? Cette action est irréversible, toutes les valeurs seront figées et les échéanciers d\'avances/soins seront décrémentés de 1 mois.')">
                @csrf
                <button type="submit" class="btn" style="background: #ef4444; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 500;">
                    Clôturer le mois
                </button>
            </form>
        @endif
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
    
    {{-- Boutons d'exports --}}
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('pay-periods.livre', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #d1d5db; text-decoration: none; color: #374151; display: inline-flex; align-items: center; gap: 0.25rem;">
            Livre de paie
        </a>
        <a href="{{ route('pay-periods.registre', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #d1d5db; text-decoration: none; color: #374151; display: inline-flex; align-items: center; gap: 0.25rem;">
            Registre de paie
        </a>
        <a href="{{ route('pay-periods.virements', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #d1d5db; text-decoration: none; color: #374151; display: inline-flex; align-items: center; gap: 0.25rem;">
            Virements Banque
        </a>
        <a href="{{ route('pay-periods.cnss', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #d1d5db; text-decoration: none; color: #374151; display: inline-flex; align-items: center; gap: 0.25rem;">
            Déclaration CNSS
        </a>
        <a href="{{ route('pay-periods.its', [$payPeriod->id, $selectedCentre->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #d1d5db; text-decoration: none; color: #374151; display: inline-flex; align-items: center; gap: 0.25rem;">
            Déclaration ITS
        </a>
    </div>
</div>

{{-- Liste des bulletins --}}
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Catégorie/Échelon</th>
                    <th style="text-align: right;">Salaire Base</th>
                    <th style="text-align: right;">Salaire Brut</th>
                    <th style="text-align: right;">Retenues (CNSS+ITS)</th>
                    <th style="text-align: right;">Ajustements</th>
                    <th style="text-align: right;">Salaire Net</th>
                    <th>Mode</th>
                    <th style="text-align: right;">Actions</th>
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
                        <div style="font-weight: 600; color: #111827;">{{ $slip->personnel->nom_complet }}</div>
                        <span style="font-size: 0.72rem; color: #6b7280;">{{ $slip->poste }}</span>
                    </td>
                    <td>
                        @if($slip->categorie)
                            <span style="font-family: monospace; font-size: 0.82rem; background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">{{ $slip->categorie }}-{{ $slip->echelon }}</span>
                        @else
                            <span style="font-size: 0.78rem; color: #9ca3af;">Hors grille / vacataire</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 500;">{{ number_format($slip->salaire_base, 0, ',', ' ') }} F</td>
                    <td style="text-align: right; font-weight: 500; color: #111827;">{{ number_format($slip->salaire_brut, 0, ',', ' ') }} F</td>
                    <td style="text-align: right; color: #ef4444; font-size: 0.85rem;">
                        -{{ number_format($slip->cotisation_sociale_salarie + $slip->impot_its, 0, ',', ' ') }} F
                    </td>
                    <td style="text-align: right; font-size: 0.85rem; color: {{ $ajustementsNet >= 0 ? '#10b981' : '#ef4444' }};">
                        {{ $ajustementsNet >= 0 ? '+' : '' }}{{ number_format($ajustementsNet, 0, ',', ' ') }} F
                    </td>
                    <td style="text-align: right; font-weight: 700; color: var(--col-primary, #1a5c45); font-size: 1.05rem;">
                        {{ number_format($slip->salaire_net, 0, ',', ' ') }} F
                    </td>
                    <td style="font-size: 0.78rem;">
                        {{ $slip->mode_reglement }}
                        @if($slip->banque)
                            <div style="font-size: 0.7rem; color: #6b7280;">({{ $slip->banque }})</div>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <a href="{{ route('pay-slips.pdf', $slip->id) }}" target="_blank" class="btn btn-sm" style="border: 1px solid #d1d5db; background: transparent; padding: 4px 8px; border-radius: 6px; color: #374151; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.15rem; margin-right: 0.25rem;">
                            PDF
                        </a>
                        <a href="{{ route('pay-slips.solde-tout-compte', $slip->id) }}" target="_blank" class="btn btn-sm" style="border: 1px solid #d1d5db; background: transparent; padding: 4px 8px; border-radius: 6px; color: #b45309; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.15rem; margin-right: 0.25rem;">
                            Solde compte
                        </a>
                        @if($payPeriod->statut === 'ouvert' && !auth()->user()->isReadOnly())
                            <button type="button" class="btn btn-sm btn-primary" onclick="openVariablesModal({{ json_encode($slip) }})" style="padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; background: var(--col-primary, #1a5c45); color: #fff; border: none; cursor: pointer;">
                                Ajuster
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af; padding: 3rem;">
                        Aucun agent actif enregistré pour la paie dans ce centre.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale d'ajustement des variables --}}
<dialog id="modalVariables" class="modal" style="border: none; border-radius: 20px; padding: 0; max-width: 680px; width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0;">
        <h2 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Modifier variables : <span id="varAgentName" style="color: var(--col-primary, #1a5c45);">Agent</span></h2>
        <button onclick="document.getElementById('modalVariables').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af; padding: 0;">&times;</button>
    </div>
    
    <form id="formVariables" method="POST">
        @csrf
        @method('PUT')
        
        <div class="modal-body" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                
                {{-- SECTION TEMPS --}}
                <div class="full-width" style="grid-column: 1 / -1; margin-top: 0.5rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 0.25rem; font-weight: 600; color: #374151; font-size: 0.88rem;">
                    Temps de travail & Absences
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Jours d'absence du mois</label>
                    <input type="number" name="jours_absence" id="varJoursAbsence" class="form-control" min="0" max="30" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Jours de Mise à Pied disciplinaire</label>
                    <input type="number" name="jours_mise_a_pied" id="varJoursMiseAPied" class="form-control" min="0" max="30" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Heures supplémentaires (Quantité)</label>
                    <input type="number" name="heures_supplementaires" id="varHeuresSup" class="form-control" min="0" step="0.5" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Heures d'astreinte (Quantité)</label>
                    <input type="number" name="heures_astreinte" id="varHeuresAstreinte" class="form-control" min="0" step="0.5" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>

                {{-- SECTION INDEMNITES --}}
                <div class="full-width" style="grid-column: 1 / -1; margin-top: 1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 0.25rem; font-weight: 600; color: #374151; font-size: 0.88rem;">
                    Indemnités & Ajustements du Brut
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Indemnité de Logement (FCFA)</label>
                    <input type="number" name="indemnite_logement" id="varIndemLogement" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Indemnité de Transport (FCFA)</label>
                    <input type="number" name="indemnite_transport" id="varIndemTransport" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Autre Indemnité (FCFA)</label>
                    <input type="number" name="autre_indemnite" id="varAutreIndemnite" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Écart d'ajustement Brut (FCFA)</label>
                    <input type="number" name="ecart" id="varEcart" class="form-control" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>

                {{-- SECTION PRIMES --}}
                <div class="full-width" style="grid-column: 1 / -1; margin-top: 1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 0.25rem; font-weight: 600; color: #374151; font-size: 0.88rem;">
                    Primes Exceptionnelles (FCFA)
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Prime de Caisse (uniquement caissiers)</label>
                    <input type="number" name="prime_caisse" id="varPrimeCaisse" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Prime de Risque</label>
                    <input type="number" name="prime_risque" id="varPrimeRisque" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Prime de Responsabilité</label>
                    <input type="number" name="prime_responsabilite" id="varPrimeResponsabilite" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Prime de Garde (personnel de garde)</label>
                    <input type="number" name="prime_garde" id="varPrimeGarde" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Autres Primes</label>
                    <input type="number" name="autre_prime" id="varAutrePrime" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Trop perçu sur salaire Brut (Défalcation)</label>
                    <input type="number" name="trop_percu_brut" id="varTropPercuBrut" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>

                {{-- SECTION AJUSTEMENTS NET --}}
                <div class="full-width" style="grid-column: 1 / -1; margin-top: 1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 0.25rem; font-weight: 600; color: #374151; font-size: 0.88rem;">
                    Ajustements sur Net (FCFA)
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Remboursement de soins médicaux</label>
                    <input type="number" name="frais_medicaux" id="varFraisMedicaux" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Remboursement d'avance sur salaire</label>
                    <input type="number" name="avance_salaire" id="varAvanceSalaire" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Trop perçu sur salaire Net (Défalcation Net)</label>
                    <input type="number" name="trop_percu_net" id="varTropPercuNet" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Moins-perçu à rembourser (Positif)</label>
                    <input type="number" name="moins_percu_rembourse" id="varMoinsPercu" class="form-control" min="0" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>

                {{-- SECTION BANQUE --}}
                <div class="full-width" style="grid-column: 1 / -1; margin-top: 1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 0.25rem; font-weight: 600; color: #374151; font-size: 0.88rem;">
                    Informations de Règlement
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Banque de virement</label>
                    <select name="banque" id="varBanque" class="form-control" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem; background-color:#fff;">
                        <option value="BOA">BOA</option>
                        <option value="Ecobank">Ecobank</option>
                        <option value="Archevêché">Archevêché (Caisse)</option>
                        <option value="Espèces">Espèces</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Mode de règlement</label>
                    <select name="mode_reglement" id="varModeReglement" class="form-control" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem; background-color:#fff;">
                        <option value="Virement">Virement</option>
                        <option value="Espèces">Espèces (Caisse)</option>
                        <option value="Chèque">Chèque</option>
                    </select>
                </div>
                <div class="full-width" style="grid-column: 1 / -1;">
                    <label class="checkbox-label" style="display: block; font-size: 0.8rem; font-weight: 500; color: #4b5563; margin-bottom: 0.25rem;">Numéro de compte / Notes</label>
                    <input type="text" name="numero_compte" id="varNumeroCompte" class="form-control" style="width: 100%; padding: 0.4rem; border-radius: 6px; border: 1px solid #d1d5db; font-size: 0.88rem;">
                </div>
            </div>
        </div>
        
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalVariables').close()" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem;">Annuler</button>
            <button type="submit" class="btn btn-primary" style="background: var(--col-primary, #1a5c45); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-size: 0.88rem;">Recalculer & Enregistrer</button>
        </div>
    </form>
</dialog>

<script>
    function openVariablesModal(slip) {
        document.getElementById('varAgentName').innerText = slip.personnel.nom + ' ' + slip.personnel.prenoms;
        
        // Attribuer l'action du formulaire
        document.getElementById('formVariables').action = '/paie/bulletins/' + slip.id;
        
        // Charger les données dans les inputs
        document.getElementById('varJoursAbsence').value = slip.jours_absence || 0;
        
        // Trouver jours_mise_a_pied en déduisant ou charger s'il est dispo.
        // Puisque nous avons stocké la valeur de mise a pied financière ou les jours travaillés, nous pouvons calculer:
        // jours_mise_a_pied = 30 - jours_travailles - jours_absence
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
