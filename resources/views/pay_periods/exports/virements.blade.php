<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État de Paiement - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: 'Courier New', Courier, 'DejaVu Sans Mono', monospace, Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 10px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
            margin-top: 8px;
        }
        .table-virement {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .table-virement th, .table-virement td {
            border: 1.5px solid #000;
            padding: 6px 8px;
        }
        .table-virement th {
            background-color: #d1d5db;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .filter-bar {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    @php
        $selectedBanque = request('banque_filter') ?: 'ECOBANK';
        $availableBanques = ['BOA', 'BIIC', 'ECOBANK', 'UBA', 'ARCHEVECHE'];
    @endphp

    <div class="no-print filter-bar">
        <div>
            <label style="font-weight: bold; margin-right: 8px;">🏦 Choisir la Banque :</label>
            <select onchange="window.location.href = '?banque_filter=' + this.value" style="padding: 4px 10px; border-radius: 6px; border: 1.5px solid #cbd5e1; font-weight: bold;">
                <option value="ALL" {{ $selectedBanque === 'ALL' ? 'selected' : '' }}>-- TOUTES LES BANQUES --</option>
                @foreach($availableBanques as $b)
                    <option value="{{ $b }}" {{ $selectedBanque === $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
            </select>
        </div>
        <button onclick="window.print()" style="padding: 6px 16px; background-color: #1a5c45; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Imprimer cet État
        </button>
    </div>

    @php
        if ($selectedBanque !== 'ALL') {
            $filteredSlips = $slips->filter(function($s) use ($selectedBanque) {
                return mb_strtoupper($s->banque) === mb_strtoupper($selectedBanque);
            });
            $banqueLabel = mb_strtoupper($selectedBanque);
        } else {
            $filteredSlips = $slips;
            $banqueLabel = 'TOUTES BANQUES';
        }
    @endphp

    <div class="header">
        <div class="header-centre">{{ $centre->nom }}</div>
        <div class="header-title">Etat de paiement {{ $banqueLabel }} de {{ mb_strtoupper($payPeriod->label) }}</div>
    </div>

    <table class="table-virement">
        <thead>
            <tr>
                <th style="width: 40%;">Employé</th>
                <th style="width: 20%;">Banque</th>
                <th style="width: 25%;">N° de compte</th>
                <th style="width: 15%; text-align: right;">Montant à payer</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMontant = 0; @endphp
            @forelse($filteredSlips as $slip)
                @php $totalMontant += $slip->salaire_net; @endphp
                <tr>
                    <td class="font-bold">{{ mb_strtoupper($slip->personnel->nom_complet) }}</td>
                    <td>{{ mb_strtoupper($slip->banque ?? 'ECO BANK') }}</td>
                    <td>{{ $slip->numero_compte ?? '-' }}</td>
                    <td class="text-right font-bold">{{ number_format($slip->salaire_net, 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; padding: 15px;">
                        Aucun virement enregistré pour la banque {{ $banqueLabel }} pour ce mois.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #e5e7eb;">
                <td colspan="3" style="text-align: left; padding: 8px;">
                    {{ count($filteredSlips) }} employé(s)
                </td>
                <td class="text-right" style="font-size: 12px;">
                    {{ number_format($totalMontant, 0, ',', ' ') }}
                </td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
