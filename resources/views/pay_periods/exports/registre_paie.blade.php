<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Registre de Paie - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #e5e7eb;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #d1d5db;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 10px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 12px; background-color: #111827; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Imprimer le registre
        </button>
    </div>

    <h2 style="margin: 5px 0 2px; font-size: 12px;">Registre des Salaires - {{ $payPeriod->label }}</h2>
    <div style="font-weight: bold; margin-bottom: 10px;">{{ $centre->nom }} | IFU: {{ $centre->ifu ?? '-' }} | CNSS: {{ $centre->numero_cnss ?? '-' }}</div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Salarié</th>
                <th>Fonction</th>
                <th>CNSS Assuré</th>
                <th>Jours</th>
                <th class="text-right">Sal. de Base</th>
                <th class="text-right">Logement</th>
                <th class="text-right">Transport</th>
                <th class="text-right">Résidence</th>
                <th class="text-right">Primes</th>
                <th class="text-right">Brut</th>
                <th class="text-right">CNSS (3.6%)</th>
                <th class="text-right">ITS</th>
                <th class="text-right">Taxes Net</th>
                <th class="text-right">Retenues Net</th>
                <th class="text-right">Rembours.</th>
                <th class="text-right">Net</th>
                <th class="text-right">Part Patronale (16.4%)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBase = 0; $totLog = 0; $totTrans = 0; $totRes = 0; $totPrimes = 0;
                $totBrut = 0; $totCnss = 0; $totIts = 0; $totTaxes = 0; $totRet = 0;
                $totRemb = 0; $totNet = 0; $totPatr = 0;
            @endphp
            @foreach($slips as $index => $slip)
                @php
                    $primes = (float)$slip->prime_caisse + (float)$slip->prime_risque + (float)$slip->prime_responsabilite + (float)$slip->prime_garde + (float)$slip->autre_prime + (float)$slip->autre_indemnite + (float)$slip->ecart;
                    $retenuesNet = (float)$slip->frais_medicaux + (float)$slip->avance_salaire + (float)$slip->trop_percu_net + (float)$slip->mise_a_pied;
                    
                    $totBase += $slip->salaire_base * ($slip->jours_travailles / 30);
                    $totLog += $slip->indemnite_logement;
                    $totTrans += $slip->indemnite_transport;
                    $totRes += $slip->indemnite_residence;
                    $totPrimes += $primes;
                    $totBrut += $slip->salaire_brut;
                    $totCnss += $slip->cotisation_sociale_salarie;
                    $totIts += $slip->impot_its;
                    $totTaxes += $slip->taxe_radio + $slip->taxe_tele;
                    $totRet += $retenuesNet;
                    $totRemb += $slip->moins_percu_rembourse;
                    $totNet += $slip->salaire_net;
                    $totPatr += $slip->charges_patronales_totales;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $slip->personnel->nom_complet }}</strong></td>
                    <td>{{ $slip->poste }}</td>
                    <td>{{ $slip->matricule_cnss ?? '-' }}</td>
                    <td>{{ $slip->jours_travailles }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_base * ($slip->jours_travailles / 30), 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->indemnite_logement, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->indemnite_transport, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->indemnite_residence, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($primes, 0, ',', ' ') }} F</td>
                    <td class="text-right"><strong>{{ number_format($slip->salaire_brut, 0, ',', ' ') }} F</strong></td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_salarie, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->impot_its, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->taxe_radio + $slip->taxe_tele, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($retenuesNet, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->moins_percu_rembourse, 0, ',', ' ') }} F</td>
                    <td class="text-right"><strong>{{ number_format($slip->salaire_net, 0, ',', ' ') }} F</strong></td>
                    <td class="text-right">{{ number_format($slip->charges_patronales_totales, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5">TOTAL GÉNÉRAL</td>
                <td class="text-right">{{ number_format($totBase, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totLog, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totTrans, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totRes, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totPrimes, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totBrut, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totCnss, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totIts, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totTaxes, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totRet, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totRemb, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totNet, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totPatr, 0, ',', ' ') }} F</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
