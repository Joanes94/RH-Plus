<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclaration ITS - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
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
            Imprimer la Déclaration ITS
        </button>
    </div>

    <h2>DÉCLARATION MENSUELLE DES TRAITEMENTS ET SALAIRES (ITS)</h2>
    <div><strong>Établissement :</strong> {{ $centre->nom }}</div>
    <div><strong>N° IFU :</strong> {{ $centre->ifu ?? 'Non configuré' }}</div>
    <div><strong>Mois de déclaration :</strong> {{ $payPeriod->label }}</div>

    <table>
        <thead>
            <tr>
                <th>Nom & Prénoms du Salarié</th>
                <th>Fonction</th>
                <th class="text-right">Salaire Brut</th>
                <th class="text-right">Cotisation Sociale (3.6%)</th>
                <th class="text-right">Base Imposable (ITS)</th>
                <th class="text-right">Impôt ITS retenu</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBrut = 0; $totCnss = 0; $totBase = 0; $totIts = 0;
            @endphp
            @foreach($slips as $slip)
                @php
                    $baseImposable = $slip->salaire_brut - $slip->cotisation_sociale_salarie;
                    
                    $totBrut += $slip->salaire_brut;
                    $totCnss += $slip->cotisation_sociale_salarie;
                    $totBase += $baseImposable;
                    $totIts += $slip->impot_its;
                @endphp
                <tr>
                    <td><strong>{{ $slip->personnel->nom_complet }}</strong></td>
                    <td>{{ $slip->poste }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_brut, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_salarie, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($baseImposable, 0, ',', ' ') }} F</td>
                    <td class="text-right" style="font-weight: 600; color: #ef4444;">{{ number_format($slip->impot_its, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAUX</td>
                <td class="text-right">{{ number_format($totBrut, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totCnss, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($totBase, 0, ',', ' ') }} F</td>
                <td class="text-right" style="font-size: 11px; color: #ef4444;">{{ number_format($totIts, 0, ',', ' ') }} F</td>
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
