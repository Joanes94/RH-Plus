<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclaration Mensuelle CNSS - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        body {
            font-family: 'Courier New', Courier, 'DejaVu Sans Mono', monospace, Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 10px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .header-centre {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-title {
            font-size: 15px;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .table-cnss {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .table-cnss th, .table-cnss td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .table-cnss th {
            background-color: #d1d5db;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()" style="padding: 7px 16px; background-color: #1a5c45; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Imprimer Déclaration CNSS
        </button>
    </div>

    <div class="header">
        <div class="header-centre">{{ $centre->nom }}</div>
        <div class="header-title">DECLARATION MENSUELLE CNSS de {{ mb_strtoupper($payPeriod->label) }}</div>
    </div>

    <table class="table-cnss">
        <thead>
            <tr>
                <th style="width: 22%;">Employé</th>
                <th style="width: 12%;">N° S.S.</th>
                <th style="width: 9%;">Date d'embauch.</th>
                <th style="width: 9%;">Date de sortie</th>
                <th style="width: 11%;">Salaire de base</th>
                <th style="width: 11%;">Salaire brut</th>
                <th style="width: 7%;">Sécurité Sociale</th>
                <th style="width: 7%;">C.N.S.S. Patronale</th>
                <th style="width: 7%;">Prestation familiale</th>
                <th style="width: 7%;">Risque professionnel</th>
                <th style="width: 8%;">TOTAL CNSS</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBase = 0; $totBrut = 0; $totSecu = 0; $totPat = 0; $totPf = 0; $totRp = 0; $totCnss = 0;
            @endphp
            @foreach($slips as $slip)
                @php
                    $p = $slip->personnel;
                    $totalCnssLine = $slip->cotisation_sociale_salarie + $slip->cotisation_sociale_patronale + $slip->prestation_familiale_patronale + $slip->risque_professionnel_patronale;
                    
                    $totBase += $slip->salaire_base;
                    $totBrut += $slip->salaire_brut;
                    $totSecu += $slip->cotisation_sociale_salarie;
                    $totPat += $slip->cotisation_sociale_patronale;
                    $totPf += $slip->prestation_familiale_patronale;
                    $totRp += $slip->risque_professionnel_patronale;
                    $totCnss += $totalCnssLine;
                @endphp
                <tr>
                    <td class="font-bold">{{ mb_strtoupper($p->nom_complet) }}</td>
                    <td class="text-center">{{ $slip->matricule_cnss ?? '-' }}</td>
                    <td class="text-center">{{ $p->date_embauche_centre ? \Carbon\Carbon::parse($p->date_embauche_centre)->format('d/m/Y') : '' }}</td>
                    <td class="text-center">{{ $p->date_debauchage ? \Carbon\Carbon::parse($p->date_debauchage)->format('d/m/Y') : '' }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_base, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_brut, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_salarie, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_patronale, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->prestation_familiale_patronale, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->risque_professionnel_patronale, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($totalCnssLine, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #e5e7eb;">
                <td colspan="4" class="text-center">TOTAUX GENERAL</td>
                <td class="text-right">{{ number_format($totBase, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totBrut, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totSecu, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totPat, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totPf, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totRp, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totCnss, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
