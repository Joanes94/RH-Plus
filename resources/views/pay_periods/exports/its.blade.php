<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclaration Mensuelle ITS - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        @page {
            size: A4 portrait;
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
        .table-its {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .table-its th, .table-its td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .table-its th {
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
            🖨️ Imprimer Déclaration ITS
        </button>
    </div>

    <div class="header">
        <div class="header-centre">{{ $centre->nom }}</div>
        <div class="header-title">DECLARATION MENSUELLE ITS de {{ mb_strtoupper($payPeriod->label) }}</div>
    </div>

    <table class="table-its">
        <thead>
            <tr>
                <th style="width: 14%;">N° S.S.</th>
                <th style="width: 10%;">Date d'embauch.</th>
                <th style="width: 10%;">Date de sortie</th>
                <th style="width: 20%;">Service</th>
                <th style="width: 11%;">Salaire de base</th>
                <th style="width: 11%;">Salaire brut</th>
                <th style="width: 8%;">Total CNSS</th>
                <th style="width: 8%;">I.T.S.</th>
                <th style="width: 8%;">Salaire net</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBase = 0; $totBrut = 0; $totCnss = 0; $totIts = 0; $totNet = 0;
            @endphp
            @foreach($slips as $slip)
                @php
                    $p = $slip->personnel;
                    $totBase += $slip->salaire_base;
                    $totBrut += $slip->salaire_brut;
                    $totCnss += $slip->cotisation_sociale_salarie;
                    $totIts += $slip->impot_its;
                    $totNet += $slip->salaire_net;
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $slip->matricule_cnss ?? '-' }}</td>
                    <td class="text-center">{{ $p->date_embauche_centre ? \Carbon\Carbon::parse($p->date_embauche_centre)->format('d/m/Y') : '' }}</td>
                    <td class="text-center">{{ $p->date_debauchage ? \Carbon\Carbon::parse($p->date_debauchage)->format('d/m/Y') : '' }}</td>
                    <td>{{ mb_strtoupper($p->service ?? 'MEDECINE GENERALE') }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_base, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_brut, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_salarie, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($slip->impot_its, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($slip->salaire_net, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #e5e7eb;">
                <td colspan="4" class="text-center">TOTAUX GENERAL</td>
                <td class="text-right">{{ number_format($totBase, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totBrut, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totCnss, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totIts, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totNet, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
