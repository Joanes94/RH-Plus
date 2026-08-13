<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordres de Virement - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .bank-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .bank-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #1a5c45;
            padding-bottom: 4px;
            color: #1a5c45;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #1a5c45; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Imprimer l'état des virements
        </button>
    </div>

    <h2 style="text-align: center; margin-bottom: 5px;">État des Virements et Règlements par Banque</h2>
    <div style="text-align: center; font-weight: bold; margin-bottom: 25px;">
        Période : {{ $payPeriod->label }} | Établissement : {{ $centre->nom }}
    </div>

    @forelse($groupedSlips as $bankName => $bankSlips)
        <div class="bank-section">
            <div class="bank-title">Règlements / Banque : {{ $bankName ?: 'Non spécifiée / Caisse' }}</div>
            <table>
                <thead>
                    <tr>
                        <th>Bénéficiaire (Salarié)</th>
                        <th>Poste</th>
                        <th>N° de Compte / Réf. règlement</th>
                        <th>Mode</th>
                        <th class="text-right" style="width: 150px;">Montant Net à virer</th>
                    </tr>
                </thead>
                <tbody>
                    @php $bankTotal = 0; @endphp
                    @foreach($bankSlips as $slip)
                        @php $bankTotal += $slip->salaire_net; @endphp
                        <tr>
                            <td><strong>{{ $slip->personnel->nom_complet }}</strong></td>
                            <td>{{ $slip->poste }}</td>
                            <td>{{ $slip->numero_compte ?? 'Paiement direct caisse' }}</td>
                            <td>{{ $slip->mode_reglement }}</td>
                            <td class="text-right" style="font-weight: 600;">{{ number_format($slip->salaire_net, 0, ',', ' ') }} F</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4">SOUS-TOTAL {{ $bankName ?: 'Caisse/Espèces' }}</td>
                        <td class="text-right" style="font-size: 12px; color: #1a5c45;">{{ number_format($bankTotal, 0, ',', ' ') }} F</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align: center; color: #9ca3af; padding: 3rem;">
            Aucun virement à afficher pour cette période.
        </div>
    @endforelse

</body>
</html>
