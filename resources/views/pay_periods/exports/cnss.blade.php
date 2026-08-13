<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclaration CNSS - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
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
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #1a5c45; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Imprimer la Déclaration
        </button>
    </div>

    <h2>BORDEREAU NOMINATIF DE DÉCLARATION DES COTISATIONS - CNSS</h2>
    <div><strong>Établissement :</strong> {{ $centre->nom }}</div>
    <div><strong>N° CNSS Employeur :</strong> {{ $centre->numero_cnss ?? 'Non configuré' }}</div>
    <div><strong>Mois de déclaration :</strong> {{ $payPeriod->label }}</div>

    <table>
        <thead>
            <tr>
                <th>N° Assuré CNSS</th>
                <th>Nom & Prénoms du Salarié</th>
                <th>Temps (Jours)</th>
                <th class="text-right">Salaire Brut (Assurable)</th>
                <th class="text-right">Part Ouvrière (3.6%)</th>
                <th class="text-right">Part Patronale (6.4%)</th>
                <th class="text-right">Prest. Familiales (9.0%)</th>
                <th class="text-right">Accident Travail (1.0%)</th>
                <th class="text-right">Total Cotisation Due (20.0%)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBrut = 0; $totOuv = 0; $totPatr = 0; $totFam = 0; $totAcc = 0; $totGlobal = 0;
            @endphp
            @foreach($slips as $slip)
                @php
                    $totalSlipDue = $slip->cotisation_sociale_salarie + $slip->cotisation_sociale_patronale + $slip->prestation_familiale_patronale + $slip->risque_professionnel_patronale;
                    
                    $totBrut += $slip->salaire_brut;
                    $totOuv += $slip->cotisation_sociale_salarie;
                    $totPatr += $slip->cotisation_sociale_patronale;
                    $totFam += $slip->prestation_familiale_patronale;
                    $totAcc += $slip->risque_professionnel_patronale;
                    $totGlobal += $totalSlipDue;
                @endphp
                <tr>
                    <td><strong>{{ $slip->matricule_cnss ?? 'Néant / En cours' }}</strong></td>
                    <td>{{ $slip->personnel->nom_complet }}</td>
                    <td>{{ $slip->jours_travailles }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_brut, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_salarie, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_patronale, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->prestation_familiale_patronale, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->risque_professionnel_patronale, 0, ',', ' ') }} F</td>
                    <td class="text-right" style="font-weight: 600;">{{ number_format($totalSlipDue, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">TOTAUX</td>
                <td class="text-right">{{ number_format($totBrut, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totOuv, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totPatr, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totFam, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totAcc, 0, ',', ' ') }} F</td>
                <td class="text-right" style="font-size: 11px;">{{ number_format($totGlobal, 0, ',', ' ') }} F</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: space-between;">
        <div>
            <strong>Date :</strong> {{ date('d/m/Y') }}
        </div>
        <div style="text-align: center; width: 250px;">
            <strong>Le Directeur du Centre</strong>
            <div style="margin-top: 60px; border-top: 1px dashed #333; padding-top: 5px;">Cachet et Signature</div>
        </div>
    </div>

</body>
</html>
