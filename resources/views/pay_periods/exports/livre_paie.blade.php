<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livre de Paie - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #999;
            padding: 5px;
            text-align: left;
            white-space: nowrap;
        }
        th {
            background-color: #f2f2f2;
            font-size: 8px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #e5e5e5;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #1a5c45; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Imprimer le livre de paie
        </button>
    </div>

    <div class="header">
        <div class="title">Livre de Paie</div>
        <div>Période : {{ $payPeriod->label }}</div>
        <div>Établissement : {{ $centre->nom }}</div>
    </div>

    <div class="meta-info">
        <div>N° IFU : {{ $centre->ifu ?? 'Néant' }}</div>
        <div>N° CNSS Employeur : {{ $centre->numero_cnss ?? 'Néant' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Agent</th>
                <th>Fonction</th>
                <th>CNSS Assuré</th>
                <th>Jours</th>
                <th class="text-right">Sal. de Base</th>
                <th class="text-right">Indem. Résidence</th>
                <th class="text-right">Primes & Indem.</th>
                <th class="text-right">Salaire Brut</th>
                <th class="text-right">CNSS Ouv. (3.6%)</th>
                <th class="text-right">ITS</th>
                <th class="text-right">Taxes ORTB</th>
                <th class="text-right">Retenues Net (Avances/Soins/Mise à Pied)</th>
                <th class="text-right">Remboursements</th>
                <th class="text-right">Net à payer</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBase = 0; $totRes = 0; $totPrimes = 0; $totBrut = 0;
                $totCnss = 0; $totIts = 0; $totTaxes = 0; $totRet = 0;
                $totRemb = 0; $totNet = 0;
            @endphp
            @foreach($slips as $slip)
                @php
                    $primesEtIndem = (float)$slip->indemnite_logement + (float)$slip->indemnite_transport + (float)$slip->autre_indemnite + (float)$slip->ecart + (float)$slip->prime_caisse + (float)$slip->prime_risque + (float)$slip->prime_responsabilite + (float)$slip->prime_garde + (float)$slip->autre_prime;
                    $retenuesNet = (float)$slip->frais_medicaux + (float)$slip->avance_salaire + (float)$slip->trop_percu_net + (float)$slip->mise_a_pied;
                    
                    $totBase += $slip->salaire_base * ($slip->jours_travailles / 30);
                    $totRes += $slip->indemnite_residence;
                    $totPrimes += $primesEtIndem;
                    $totBrut += $slip->salaire_brut;
                    $totCnss += $slip->cotisation_sociale_salarie;
                    $totIts += $slip->impot_its;
                    $totTaxes += $slip->taxe_radio + $slip->taxe_tele;
                    $totRet += $retenuesNet;
                    $totRemb += $slip->moins_percu_rembourse;
                    $totNet += $slip->salaire_net;
                @endphp
                <tr>
                    <td><strong>{{ $slip->personnel->nom_complet }}</strong></td>
                    <td>{{ $slip->poste }}</td>
                    <td>{{ $slip->matricule_cnss ?? '-' }}</td>
                    <td>{{ $slip->jours_travailles }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_base * ($slip->jours_travailles / 30), 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->indemnite_residence, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($primesEtIndem, 0, ',', ' ') }} F</td>
                    <td class="text-right"><strong>{{ number_format($slip->salaire_brut, 0, ',', ' ') }} F</strong></td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_salarie, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->impot_its, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->taxe_radio + $slip->taxe_tele, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($retenuesNet, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->moins_percu_rembourse, 0, ',', ' ') }} F</td>
                    <td class="text-right"><strong>{{ number_format($slip->salaire_net, 0, ',', ' ') }} F</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4">TOTAL GÉNÉRAL</td>
                <td class="text-right">{{ number_format($totBase, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totRes, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totPrimes, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totBrut, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totCnss, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totIts, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totTaxes, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totRet, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totRemb, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totNet, 0, ',', ' ') }} F</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
        <div style="text-align: center; width: 200px;">
            <strong>Le Directeur du Centre</strong>
            <div style="margin-top: 60px; border-top: 1px dashed #333; padding-top: 5px;">Signature & Cachet</div>
        </div>
    </div>

</body>
</html>
