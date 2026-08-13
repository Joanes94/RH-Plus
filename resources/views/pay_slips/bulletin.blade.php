<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Paie - {{ $paySlip->personnel->nom_complet }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        .header-container {
            margin-bottom: 20px;
            border-bottom: 3px double var(--col-primary, #1a5c45);
            padding-bottom: 10px;
        }

        .header-branding {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-logo {
            height: 60px;
            object-fit: contain;
        }

        .header-text {
            text-align: center;
            flex: 1;
            padding: 0 15px;
        }

        .header-title-text {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--col-primary, #1a5c45);
            margin: 0 0 4px;
        }

        .header-subtext {
            font-size: 9px;
            color: #4b5563;
            margin: 0;
        }

        .bulletin-title {
            text-align: center;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111827;
            margin: 15px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-block {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            background-color: #f9fafb;
        }

        .info-block h4 {
            margin: 0 0 6px;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: 500;
            color: #4b5563;
        }

        .info-value {
            font-weight: 700;
            color: #111827;
        }

        /* Table design */
        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .pay-table th {
            background-color: var(--col-primary, #1a5c45);
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid var(--col-primary, #1a5c45);
        }

        .pay-table td {
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
        }

        .pay-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: 700;
        }

        .summary-box {
            border: 2px solid #111827;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 25px;
            background-color: #fff;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            border-top: 1px dashed #e5e7eb;
            padding-top: 6px;
        }

        .net-to-pay {
            font-size: 16px;
            color: var(--col-primary, #1a5c45);
            font-weight: 800;
        }

        /* Employer contributions block */
        .employer-block {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            background-color: #f3f4f6;
            margin-bottom: 30px;
        }

        .employer-block h4 {
            margin: 0 0 6px;
            font-size: 9px;
            text-transform: uppercase;
            color: #4b5563;
        }

        .signatures-area {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 15px;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px dashed #9ca3af;
            padding-top: 5px;
            font-style: italic;
            color: #6b7280;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    {{-- Bouton d'impression --}}
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: var(--col-primary, #1a5c45); color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 12px;">
            Imprimer le bulletin
        </button>
    </div>

    {{-- En-tête de document --}}
    <div class="header-container">
        @if($enteteBase64)
            <img src="{{ $enteteBase64 }}" alt="En-tête officiel" style="width: 100%; max-height: 100px; object-fit: contain;">
        @else
            <div class="header-branding">
                <img src="{{ $dioceseLogoBase64 }}" class="header-logo" alt="Logo Diocèse">
                <div class="header-text">
                    <h2 class="header-title-text">Archidiocèse de Cotonou</h2>
                    <p class="header-subtext" style="font-weight: 600;">{{ $paySlip->centre->nom }}</p>
                    <p class="header-subtext">{{ $paySlip->centre->adresse }} | Tél: {{ $paySlip->centre->telephone }}</p>
                </div>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo Centre">
                @else
                    <div style="width: 60px;"></div>
                @endif
            </div>
        @endif
    </div>

    <div class="bulletin-title">Bulletin de Paie - {{ $paySlip->payPeriod->label }}</div>

    <div class="info-grid">
        {{-- Infos Employeur --}}
        <div class="info-block">
            <h4>Employeur</h4>
            <div class="info-row">
                <span class="info-label">Raison sociale :</span>
                <span class="info-value">{{ $paySlip->centre->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">N° IFU :</span>
                <span class="info-value">{{ $paySlip->centre->ifu ?? 'Non configuré' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">N° CNSS Employeur :</span>
                <span class="info-value">{{ $paySlip->centre->numero_cnss ?? 'Non configuré' }}</span>
            </div>
        </div>

        {{-- Infos Salarié --}}
        <div class="info-block">
            <h4>Salarié</h4>
            <div class="info-row">
                <span class="info-label">Nom & Prénoms :</span>
                <span class="info-value">{{ $paySlip->personnel->nom_complet }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Poste / Fonction :</span>
                <span class="info-value">{{ $paySlip->poste }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">N° CNSS Assuré :</span>
                <span class="info-value">{{ $paySlip->matricule_cnss ?? 'Néant' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Catégorie - Échelon :</span>
                <span class="info-value">{{ $paySlip->categorie ? ($paySlip->categorie . ' - ' . $paySlip->echelon) : 'Hors Grille' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jours Travaillés :</span>
                <span class="info-value">{{ $paySlip->jours_travailles }} / 30</span>
            </div>
        </div>
    </div>

    {{-- Détails de paie --}}
    <table class="pay-table">
        <thead>
            <tr>
                <th>Désignation des Éléments de Salaire</th>
                <th class="text-right">Part salariale (Gains)</th>
                <th class="text-right">Retenues / Déductions</th>
            </tr>
        </thead>
        <tbody>
            {{-- 1. Salaire de base --}}
            <tr>
                <td>Salaire de base (proratisé à {{ $paySlip->jours_travailles }} j)</td>
                <td class="text-right">{{ number_format($paySlip->salaire_base * ($paySlip->jours_travailles / 30), 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>

            {{-- 2. Indemnités --}}
            @if($paySlip->indemnite_residence > 0)
            <tr>
                <td>Indemnité de Résidence (10% de base proratisé)</td>
                <td class="text-right">{{ number_format($paySlip->indemnite_residence, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->indemnite_logement > 0)
            <tr>
                <td>Indemnité de Logement</td>
                <td class="text-right">{{ number_format($paySlip->indemnite_logement, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->indemnite_transport > 0)
            <tr>
                <td>Indemnité de Transport</td>
                <td class="text-right">{{ number_format($paySlip->indemnite_transport, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->autre_indemnite > 0)
            <tr>
                <td>Autre Indemnité</td>
                <td class="text-right">{{ number_format($paySlip->autre_indemnite, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->ecart != 0)
            <tr>
                <td>Écart / Ajustement Brut</td>
                <td class="text-right">{{ $paySlip->ecart > 0 ? '+' : '' }}{{ number_format($paySlip->ecart, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif

            {{-- Heures Sup / Gardes / Astreintes --}}
            @if($paySlip->heures_supplementaires > 0)
            <tr>
                <td>Heures supplémentaires ({{ $paySlip->heures_supplementaires }} h)</td>
                <td class="text-right">{{ number_format($paySlip->heures_supplementaires * ($paySlip->salaire_base / 173.33) * 1.25, 0, ',', ' ') }} F</td> {{-- calcul approximatif pour démo --}}
                <td class="text-right"></td>
            </tr>
            @endif

            {{-- 3. Primes --}}
            @if($paySlip->prime_caisse > 0)
            <tr>
                <td>Prime de Caisse</td>
                <td class="text-right">{{ number_format($paySlip->prime_caisse, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->prime_risque > 0)
            <tr>
                <td>Prime de Risque</td>
                <td class="text-right">{{ number_format($paySlip->prime_risque, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->prime_responsabilite > 0)
            <tr>
                <td>Prime de Responsabilité</td>
                <td class="text-right">{{ number_format($paySlip->prime_responsabilite, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->prime_garde > 0)
            <tr>
                <td>Prime de Garde (Infirmier / Personnel de garde)</td>
                <td class="text-right">{{ number_format($paySlip->prime_garde, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
            @if($paySlip->autre_prime > 0)
            <tr>
                <td>Autre Prime</td>
                <td class="text-right">{{ number_format($paySlip->autre_prime, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif

            {{-- 4. Défalcations sur brut --}}
            @if($paySlip->trop_percu_brut > 0)
            <tr>
                <td>Retenue : Trop perçu sur salaire brut</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->trop_percu_brut, 0, ',', ' ') }} F</td>
            </tr>
            @endif

            {{-- 5. Cotisations Sociales et Impôts --}}
            @if($paySlip->cotisation_sociale_salarie > 0)
            <tr>
                <td>Cotisation Sociale CNSS (Part Ouvrière 3.6%)</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->cotisation_sociale_salarie, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @if($paySlip->impot_its > 0)
            <tr>
                <td>Impôt sur le Traitement des Salaires (ITS progressif)</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->impot_its, 0, ',', ' ') }} F</td>
            </tr>
            @endif

            {{-- 6. Taxes spéciales (ORTB) --}}
            @if($paySlip->taxe_radio > 0)
            <tr>
                <td>Redevance ORTB Radio (Prélèvement Annuel Mars)</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->taxe_radio, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @if($paySlip->taxe_tele > 0)
            <tr>
                <td>Redevance ORTB Télé (Prélèvement Annuel Juin)</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->taxe_tele, 0, ',', ' ') }} F</td>
            </tr>
            @endif

            {{-- 7. Prélèvements amortissements et sanctions --}}
            @if($paySlip->frais_medicaux > 0)
            <tr>
                <td>Remboursement frais médicaux (soins échelonnés)</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->frais_medicaux, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @if($paySlip->avance_salaire > 0)
            <tr>
                <td>Remboursement Avance sur Salaire</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->avance_salaire, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @if($paySlip->trop_percu_net > 0)
            <tr>
                <td>Défalcation : Trop perçu sur salaire net</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->trop_percu_net, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @if($paySlip->mise_a_pied > 0)
            <tr>
                <td>Mise à pied disciplinaire (Retenue jours non travaillés)</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($paySlip->mise_a_pied, 0, ',', ' ') }} F</td>
            </tr>
            @endif

            {{-- 8. Remboursements nets --}}
            @if($paySlip->moins_percu_rembourse > 0)
            <tr>
                <td>Régularisation moins-perçu (Remboursement)</td>
                <td class="text-right">{{ number_format($paySlip->moins_percu_rembourse, 0, ',', ' ') }} F</td>
                <td class="text-right"></td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Synthèse --}}
    <div class="summary-box">
        <div class="summary-row">
            <span class="text-bold">Total salaire brut :</span>
            <span class="text-bold">{{ number_format($paySlip->salaire_brut, 0, ',', ' ') }} F</span>
        </div>
        <div class="summary-row">
            <span>Total retenues salariales & taxes :</span>
            <span>
                @php
                    $totalRetenues = $paySlip->cotisation_sociale_salarie + $paySlip->impot_its + $paySlip->taxe_radio + $paySlip->taxe_tele + $paySlip->frais_medicaux + $paySlip->avance_salaire + $paySlip->trop_percu_net + $paySlip->mise_a_pied;
                @endphp
                {{ number_format($totalRetenues, 0, ',', ' ') }} F
            </span>
        </div>
        <div class="summary-row">
            <span class="net-to-pay">SALAIRE NET À PAYER :</span>
            <span class="net-to-pay">{{ number_format($paySlip->salaire_net, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    {{-- Charges Patronales --}}
    <div class="employer-block">
        <h4>Part Patronale (Non déduite du salaire de l'agent)</h4>
        <div class="info-row" style="margin-bottom: 2px;">
            <span>Cotisation Sociale CNSS (6.4%) :</span>
            <span>{{ number_format($paySlip->cotisation_sociale_patronale, 0, ',', ' ') }} F</span>
        </div>
        <div class="info-row" style="margin-bottom: 2px;">
            <span>Prestations Familiales (9.0%) :</span>
            <span>{{ number_format($paySlip->prestation_familiale_patronale, 0, ',', ' ') }} F</span>
        </div>
        <div class="info-row" style="margin-bottom: 2px;">
            <span>Risque Professionnel (1.0%) :</span>
            <span>{{ number_format($paySlip->risque_professionnel_patronale, 0, ',', ' ') }} F</span>
        </div>
        <div class="info-row" style="border-top: 1px dashed #d1d5db; padding-top: 3px; font-weight: bold; margin-top: 3px;">
            <span>Total charges employeur :</span>
            <span>{{ number_format($paySlip->charges_patronales_totales, 0, ',', ' ') }} F</span>
        </div>
    </div>

    {{-- Mode de règlement --}}
    <div style="font-size: 10px; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">
        Règlement effectué par <strong>{{ $paySlip->mode_reglement }}</strong> 
        @if($paySlip->banque)
            via la banque <strong>{{ $paySlip->banque }}</strong>
        @endif
        @if($paySlip->numero_compte)
            (Compte / Réf : {{ $paySlip->numero_compte }})
        @endif
        le {{ date('d/m/Y') }}
    </div>

    {{-- Signatures --}}
    <div class="signatures-area">
        <div class="signature-box">
            <strong>Le Salarié</strong>
            <div style="font-size: 8px; color: #9ca3af; margin-top: 2px;">(Signature précédée de la mention "Lu et approuvé")</div>
            <div class="signature-line">Pour acquit</div>
        </div>
        
        <div class="signature-box">
            <strong>La Direction</strong>
            <div style="font-size: 8px; color: #9ca3af; margin-top: 2px;">(Cachet & Signature officielle)</div>
            <div class="signature-line">Pour accord</div>
        </div>
    </div>

</body>
</html>
