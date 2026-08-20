<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Registre de Paie - {{ $payPeriod->label }} - {{ $centre->nom }}</title>
    <style>
        @page {
            size: A3 landscape;
            margin: 5mm;
        }
        body {
            font-family: 'Courier New', Courier, 'DejaVu Sans Mono', monospace, Arial, sans-serif;
            font-size: 8.5px;
            color: #000;
            margin: 0;
            padding: 5px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .header-title {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-centre {
            font-size: 14px;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .header-mois {
            font-size: 13px;
            text-transform: uppercase;
            margin-top: 4px;
            background-color: #e5e7eb;
            padding: 3px;
            display: inline-block;
            border: 1px solid #000;
        }
        .table-registre {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .table-registre th, .table-registre td {
            border: 1px solid #000;
            padding: 2px 3px;
            white-space: nowrap;
        }
        .table-registre th {
            background-color: #d1d5db;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .bg-highlight { background-color: #fef08a; }
        .bg-summary { background-color: #e5e7eb; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 10px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 14px; background-color: #1a5c45; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 11px;">
            🖨️ Imprimer le Registre de Paie
        </button>
    </div>

    <div class="header">
        <div class="header-title">ARCHIDIOCESE DE COTONOU</div>
        <div class="header-centre">{{ $centre->nom }}</div>
        <div class="header-mois">REGISTRE DE PAIE DU MOIS DE: {{ mb_strtoupper($payPeriod->label) }}</div>
    </div>

    <table class="table-registre">
        <thead>
            <tr>
                <th>Matri-cule</th>
                <th>Code / Nom et Prénoms de l'employés</th>
                <th>Date Embauch</th>
                <th>Catégorie / Echelon</th>
                <th>Salaire de base</th>
                <th>Indemn. Résidenc</th>
                <th>Indem. Transport</th>
                <th>Prime de Garde</th>
                <th>Prime de Caisse</th>
                <th>Prime de Respons.</th>
                <th>Prime de Risque</th>
                <th>Prime de Spécialité</th>
                <th>Prime de Logement</th>
                <th>Autres indemnité</th>
                <th>Rappel</th>
                <th>Prime BRUT</th>
                <th>SALAIRE BRUT</th>
                <th>CNSS Employé</th>
                <th>CNSS Patronale</th>
                <th>Prestation Familiale</th>
                <th>Risque Professionnel</th>
                <th>ITS</th>
                <th>VPS</th>
                <th class="bg-highlight">Total Cot C.N.S.</th>
                <th class="bg-highlight">Salaire Net</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBase = 0; $totRes = 0; $totTrans = 0; $totGarde = 0; $totCaisse = 0;
                $totResp = 0; $totRisque = 0; $totSpec = 0; $totLog = 0; $totAutreIndem = 0;
                $totRappel = 0; $totPrimeBrut = 0; $totBrut = 0; $totCnssSal = 0;
                $totCnssPat = 0; $totPf = 0; $totRp = 0; $totIts = 0; $totVps = 0;
                $totCotCns = 0; $totNet = 0;
            @endphp

            @foreach($slips as $slip)
                @php
                    $p = $slip->personnel;
                    $primesFixes = $slip->indemnite_residence + $slip->indemnite_transport + $slip->prime_garde + $slip->prime_caisse + $slip->prime_responsabilite + $slip->prime_risque + $slip->prime_specialite + $slip->indemnite_logement + $slip->autre_indemnite + $slip->autre_prime;
                    $totalCotCns = $slip->cotisation_sociale_salarie + $slip->cotisation_sociale_patronale + $slip->prestation_familiale_patronale + $slip->risque_professionnel_patronale;
                    
                    $totBase += $slip->salaire_base;
                    $totRes += $slip->indemnite_residence;
                    $totTrans += $slip->indemnite_transport;
                    $totGarde += $slip->prime_garde;
                    $totCaisse += $slip->prime_caisse;
                    $totResp += $slip->prime_responsabilite;
                    $totRisque += $slip->prime_risque;
                    $totSpec += $slip->prime_specialite;
                    $totLog += $slip->indemnite_logement;
                    $totAutreIndem += $slip->autre_indemnite;
                    $totPrimeBrut += $primesFixes;
                    $totBrut += $slip->salaire_brut;
                    $totCnssSal += $slip->cotisation_sociale_salarie;
                    $totCnssPat += $slip->cotisation_sociale_patronale;
                    $totPf += $slip->prestation_familiale_patronale;
                    $totRp += $slip->risque_professionnel_patronale;
                    $totIts += $slip->impot_its;
                    $totCotCns += $totalCotCns;
                    $totNet += $slip->salaire_net;
                @endphp
                <tr>
                    <td class="text-center">{{ $p->id }}</td>
                    <td class="font-bold">{{ mb_strtoupper($p->nom_complet) }}</td>
                    <td class="text-center">{{ $p->date_embauche_centre ? \Carbon\Carbon::parse($p->date_embauche_centre)->format('d/m/Y') : '' }}</td>
                    <td class="text-center">{{ $slip->categorie ? ($slip->categorie . '-' . sprintf('%02d', $slip->echelon)) : '-' }}</td>
                    <td class="text-right">{{ number_format($slip->salaire_base, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ $slip->indemnite_residence > 0 ? number_format($slip->indemnite_residence, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->indemnite_transport > 0 ? number_format($slip->indemnite_transport, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->prime_garde > 0 ? number_format($slip->prime_garde, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->prime_caisse > 0 ? number_format($slip->prime_caisse, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->prime_responsabilite > 0 ? number_format($slip->prime_responsabilite, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->prime_risque > 0 ? number_format($slip->prime_risque, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->prime_specialite > 0 ? number_format($slip->prime_specialite, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->indemnite_logement > 0 ? number_format($slip->indemnite_logement, 0, ',', ' ') : '' }}</td>
                    <td class="text-right">{{ $slip->autre_indemnite > 0 ? number_format($slip->autre_indemnite, 0, ',', ' ') : '' }}</td>
                    <td class="text-right"></td>
                    <td class="text-right font-bold">{{ number_format($primesFixes, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($slip->salaire_brut, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_salarie, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->cotisation_sociale_patronale, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->prestation_familiale_patronale, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->risque_professionnel_patronale, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($slip->impot_its, 0, ',', ' ') }}</td>
                    <td class="text-right">0</td>
                    <td class="text-right font-bold bg-highlight">{{ number_format($totalCotCns, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold bg-highlight">{{ number_format($slip->salaire_net, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold bg-summary">
                <td colspan="4" class="text-center">TOTAUX GÉNÉRAUX</td>
                <td class="text-right">{{ number_format($totBase, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totRes, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totTrans, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totGarde, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totCaisse, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totResp, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totRisque, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totSpec, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totLog, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totAutreIndem, 0, ',', ' ') }}</td>
                <td></td>
                <td class="text-right">{{ number_format($totPrimeBrut, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totBrut, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totCnssSal, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totCnssPat, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totPf, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totRp, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totIts, 0, ',', ' ') }}</td>
                <td class="text-right">0</td>
                <td class="text-right">{{ number_format($totCotCns, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totNet, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
