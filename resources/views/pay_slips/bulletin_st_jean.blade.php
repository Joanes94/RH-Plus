<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BULLETIN DE PAIE - St Jean - {{ $paySlip->personnel->nom_complet }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm 8mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 10.5px;
            line-height: 1.15;
            margin: 0;
            padding: 2px;
            background-color: #fff;
        }
        
        .bulletin-container {
            border: 1.5px solid #000;
            padding: 0;
            width: 100%;
            box-sizing: border-box;
        }

        /* HEADER COMPACT ST JEAN */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
        }
        .header-table td {
            padding: 3px 6px;
            vertical-align: middle;
        }
        .header-left {
            width: 30%;
            border-right: 1.5px solid #000;
            text-align: center;
        }
        .header-title-left {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            text-align: left;
        }
        .photo-box {
            width: 85px;
            height: 85px;
            border: 1px dashed #444;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 50%;
            background-color: #fff;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .header-right {
            width: 70%;
            text-align: center;
        }
        .header-archidiocese {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-centre-nom {
            font-size: 11.5px;
            font-weight: bold;
            margin: 3px 0;
        }

        /* BANDEAU MOIS DE PAIE */
        .month-banner {
            border-bottom: 1.5px solid #000;
            background-color: #e5e7eb;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            padding: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* INFO SALARIE TABLE COMPACT */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
            font-size: 10px;
        }
        .info-table td {
            padding: 2px 6px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
        }

        /* RUBRIQUES TABLE COMPACT */
        .rubriques-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .rubriques-table th {
            border-bottom: 1.5px solid #000;
            border-right: 1px solid #000;
            background-color: #d1d5db;
            padding: 3px 4px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .rubriques-table th:last-child {
            border-right: none;
        }
        .rubriques-table td {
            border-right: 1px solid #000;
            border-bottom: 1px solid #e5e7eb;
            padding: 2px 5px;
        }
        .rubriques-table td:last-child {
            border-right: none;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* RECAPITULATIF ST JEAN (Photo 1) */
        .recap-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 9.5px;
            border: 1.5px solid #000;
        }
        .recap-table th {
            border: 1px solid #000;
            background-color: #d1d5db;
            padding: 3px 4px;
            text-align: center;
            font-weight: bold;
        }
        .recap-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            font-weight: bold;
        }
        .net-box {
            font-size: 12px;
            font-weight: bold;
            color: #000;
        }

        /* SIGNATURES COMPACT */
        .signatures-table {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 10.5px;
            font-weight: bold;
        }
        .signatures-table td {
            vertical-align: top;
            padding: 0 15px;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    {{-- Bouton d'impression --}}
    <div class="no-print" style="margin-bottom: 8px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 16px; background-color: #1a5c45; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 11.5px;">
            🖨️ Imprimer le bulletin
        </button>
    </div>

    @php
        $centre = $centre ?? ($paySlip->centre ?? $paySlip->personnel->centre ?? null);
        $personnel = $paySlip->personnel;
    @endphp

    <div class="bulletin-container">
        {{-- EN-TÊTE ST JEAN (Avec la photo/logo de Saint Jean) --}}
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="header-title-left">BULLETIN DE PAIE</div>
                    <div class="photo-box">
                        @if(!empty($stJeanPhotoBase64))
                            <img src="{{ $stJeanPhotoBase64 }}" alt="Photo Saint Jean">
                        @elseif(!empty($personnelPhotoBase64))
                            <img src="{{ $personnelPhotoBase64 }}" alt="Photo Personnel">
                        @else
                            <img src="{{ asset('images/letterhead/photo_eveque.jpeg') }}" alt="Photo">
                        @endif
                    </div>
                </td>
                <td class="header-right">
                    <div class="header-archidiocese">ARCHIDIOCESE DE COTONOU</div>
                    <div class="header-centre-nom">Centre Médical St Jean</div>
                </td>
            </tr>
        </table>

        {{-- BANDEAU MOIS DE PAIE --}}
        <div class="month-banner">
            SALAIRE DU MOIS DE {{ mb_strtoupper($paySlip->payPeriod->label) }}
        </div>

        {{-- TABLEAU INFORMATIONS SALARIÉ / EMPLOYEUR --}}
        <table class="info-table">
            <tr>
                <td style="width: 50%; border-right: 1px solid #000;">
                    <div><span class="info-label">Matricule</span>: {{ $personnel->id }}</div>
                    <div><span class="info-label">Nom et prénoms</span>: {{ mb_strtoupper($personnel->nom_complet) }}</div>
                    <div><span class="info-label">Date Embauche</span>: {{ $personnel->date_embauche_centre ? \Carbon\Carbon::parse($personnel->date_embauche_centre)->format('d/m/Y') : '-' }}</div>
                    <div><span class="info-label">Sit Matr</span>: {{ $personnel->situation_matrimoniale ?? 'Célibataire' }}</div>
                    <div><span class="info-label">Service</span>: {{ mb_strtoupper($personnel->service ?? 'Service des Affaires Financières') }}</div>
                    <div><span class="info-label">Titre</span>: {{ mb_strtoupper($personnel->corporation ?? $paySlip->poste) }}</div>
                </td>
                <td style="width: 50%;">
                    <div><span class="info-label">Catégorie</span>: {{ $paySlip->categorie ?? 'Hors Grille' }}</div>
                    <div><span class="info-label">Ancienneté</span>: {{ $paySlip->echelon ?? '-' }}</div>
                    <div><span class="info-label">N° CNSS</span>: {{ $paySlip->matricule_cnss ?? '-' }}</div>
                    <div><span class="info-label">Mode de Régl.</span>: {{ mb_strtoupper($paySlip->mode_reglement ?? 'VIREMENT') }}</div>
                    <div><span class="info-label">N° Compte</span>: {{ $paySlip->numero_compte ?? '-' }}</div>
                    <div><span class="info-label">Banque</span>: {{ mb_strtoupper($paySlip->banque ?? 'Bank Of Africa') }}</div>
                </td>
            </tr>
        </table>

        {{-- TABLEAU PRINCIPAL DES RUBRIQUES --}}
        <table class="rubriques-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 32%;">Rubriques</th>
                    <th rowspan="2" style="width: 14%;">Base</th>
                    <th colspan="3" style="width: 30%;">Part ouvrière</th>
                    <th colspan="2" style="width: 24%;">Part patronale</th>
                </tr>
                <tr>
                    <th style="width: 10%;">Taux</th>
                    <th style="width: 10%;">Retenue</th>
                    <th style="width: 10%;">Gains</th>
                    <th style="width: 12%;">Taux</th>
                    <th style="width: 12%;">Retenue</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Base catégorielle</td>
                    <td class="text-right">{{ number_format($paySlip->salaire_base, 0, ',', ' ') }}</td>
                    <td></td><td></td><td></td><td></td><td></td>
                </tr>
                <tr>
                    <td>Prime d'ancienneté</td>
                    <td class="text-right">6 370</td>
                    <td></td><td></td><td></td><td></td><td></td>
                </tr>
                <tr class="font-bold">
                    <td>Salaire de base</td>
                    <td class="text-right">{{ number_format($paySlip->salaire_base, 0, ',', ' ') }}</td>
                    <td class="text-center">1</td>
                    <td></td>
                    <td class="text-right">{{ number_format($paySlip->salaire_base * ($paySlip->jours_travailles / 24), 0, ',', ' ') }}</td>
                    <td></td><td></td>
                </tr>
                @if($paySlip->indemnite_residence > 0)
                <tr>
                    <td>Prime de résidence (10% base)</td>
                    <td></td><td></td><td></td>
                    <td class="text-right">{{ number_format($paySlip->indemnite_residence, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                </tr>
                @endif
                @if($paySlip->indemnite_transport > 0)
                <tr>
                    <td>Prime de transport</td>
                    <td></td><td></td><td></td>
                    <td class="text-right">{{ number_format($paySlip->indemnite_transport, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                </tr>
                @endif
                @if($paySlip->indemnite_logement > 0)
                <tr>
                    <td>Prime de logement</td>
                    <td></td><td></td><td></td>
                    <td class="text-right">{{ number_format($paySlip->indemnite_logement, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                </tr>
                @endif

                @php
                    $totalPrimes = $paySlip->indemnite_residence + $paySlip->indemnite_transport + $paySlip->indemnite_logement + $paySlip->prime_garde + $paySlip->prime_risque + $paySlip->autre_prime;
                @endphp
                <tr class="font-bold">
                    <td>Total primes</td>
                    <td></td><td></td><td></td>
                    <td class="text-right">{{ number_format($totalPrimes, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                </tr>
                <tr class="font-bold" style="background-color: #f3f4f6;">
                    <td>SALAIRE BRUT</td>
                    <td class="text-right">{{ number_format($paySlip->salaire_brut, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                    <td class="text-right">{{ number_format($paySlip->salaire_brut, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                </tr>

                {{-- Cotisations CNSS --}}
                <tr>
                    <td>Cotisation CNSS</td>
                    <td class="text-right">{{ number_format($paySlip->salaire_brut, 0, ',', ' ') }}</td>
                    <td class="text-center">0.036</td>
                    <td class="text-right">{{ number_format($paySlip->cotisation_sociale_salarie, 0, ',', ' ') }}</td>
                    <td></td>
                    <td class="text-center">0.164</td>
                    <td class="text-right">{{ number_format($paySlip->cotisation_sociale_patronale + $paySlip->prestation_familiale_patronale + $paySlip->risque_professionnel_patronale, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>Prestation familiale</td>
                    <td class="text-right">{{ number_format($paySlip->salaire_brut, 0, ',', ' ') }}</td>
                    <td></td><td></td><td></td>
                    <td class="text-center">0.09</td><td></td>
                </tr>
                <tr>
                    <td>Risque professionnel</td>
                    <td class="text-right">{{ number_format($paySlip->salaire_brut, 0, ',', ' ') }}</td>
                    <td></td><td></td><td></td>
                    <td class="text-center">0.01</td><td></td>
                </tr>
                <tr>
                    <td>I.T.S.</td>
                    <td class="text-right">{{ number_format($paySlip->salaire_brut - $paySlip->cotisation_sociale_salarie, 0, ',', ' ') }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($paySlip->impot_its, 0, ',', ' ') }}</td>
                    <td></td><td></td><td></td>
                </tr>

                @php
                    $totalCotisSalarie = $paySlip->cotisation_sociale_salarie + $paySlip->impot_its;
                    $totalCotisPatronale = $paySlip->cotisation_sociale_patronale + $paySlip->prestation_familiale_patronale + $paySlip->risque_professionnel_patronale;
                @endphp
                <tr class="font-bold">
                    <td>Total Cotisations</td>
                    <td></td><td></td>
                    <td class="text-right">{{ number_format($totalCotisSalarie, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                    <td class="text-right">{{ number_format($totalCotisPatronale, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>Taxe Radio</td>
                    <td></td><td></td>
                    <td class="text-right">1 000</td>
                    <td></td><td></td><td></td>
                </tr>
                <tr class="font-bold" style="background-color: #e5e7eb;">
                    <td>SALAIRE NET</td>
                    <td></td><td></td><td></td>
                    <td class="text-right net-box">{{ number_format($paySlip->salaire_net, 0, ',', ' ') }}</td>
                    <td></td><td></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- RECAPITULATIF CONFORME PHOTO 1 --}}
    @php
        $baseImposable = $paySlip->salaire_brut - $paySlip->cotisation_sociale_salarie;
        $masseSalariale = $paySlip->salaire_brut + $totalCotisPatronale;
    @endphp
    <table class="recap-table">
        <thead>
            <tr>
                <th style="width: 20%;">Part / Rubrique</th>
                <th style="width: 15%;">Base impôts</th>
                <th style="width: 12%;">CNSS</th>
                <th style="width: 12%;">IPTS</th>
                <th style="width: 15%;">TOTAL</th>
                <th style="width: 26%; background-color: #9ca3af;">MONTANT A PAYER</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Salaire brut</td>
                <td></td>
                <td></td>
                <td>{{ number_format($baseImposable, 0, ',', ' ') }}</td>
                <td>{{ number_format($paySlip->salaire_brut, 0, ',', ' ') }}</td>
                <td rowspan="4" class="net-box">{{ number_format($paySlip->salaire_net, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Part employé</td>
                <td></td>
                <td></td>
                <td>{{ number_format($paySlip->impot_its, 0, ',', ' ') }}</td>
                <td>{{ number_format($totalCotisSalarie, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Part Patronale</td>
                <td>{{ number_format($baseImposable, 0, ',', ' ') }}</td>
                <td>{{ number_format($paySlip->cotisation_sociale_patronale, 0, ',', ' ') }}</td>
                <td>17 381</td>
                <td>{{ number_format($totalCotisPatronale, 0, ',', ' ') }}</td>
            </tr>
            <tr class="font-bold">
                <td>Masse salariale</td>
                <td></td><td></td><td></td>
                <td>{{ number_format($masseSalariale, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- SIGNATURES --}}
    @php
        $docService = new \App\Services\DocumentService();
        $drhInfo = $docService->resolveDrhCentre($personnel);
        $drhTitre = $drhInfo['titre'] ?? 'Directeur des Ressources Humaines';
        $drhNom   = $drhInfo['nom']   ?? '...';
    @endphp
    <table class="signatures-table" style="width: 100%; margin-top: 25px; font-size: 11px; border-top: 1.5px solid #000; padding-top: 10px;">
        <tr>
            <td style="text-align: left; width: 45%; vertical-align: top;">
                <div style="font-weight: bold; text-decoration: underline; margin-bottom: 5px;">L'EMPLOYÉ</div>
                <br><br>
                <div style="font-weight: bold; font-size: 11px;">{{ $personnel->nom_complet }}</div>
            </td>
            <td style="text-align: right; width: 55%; vertical-align: top;">
                {{-- Titre : Directeur du Centre ou DRH du Centre (propre à chaque centre) --}}
                <div style="font-weight: bold; font-size: 11px; text-decoration: underline; margin-bottom: 2px;">
                    {{ $drhTitre }}
                </div>
                {{-- Espace signature --}}
                <div style="height: 45px; border-bottom: 1px dotted #888; margin: 6px 0 4px 0; min-width: 180px; display: inline-block; width: 70%;">
                    @if(!empty($drhInfo['signature_url']))
                        <img src="{{ $drhInfo['signature_url'] }}" style="max-height: 42px;" alt="Signature">
                    @endif
                </div>
                {{-- Nom du DRH / Directeur --}}
                <div style="font-weight: bold; font-size: 11px; margin-top: 2px;">{{ $drhNom }}</div>
            </td>
        </tr>
    </table>

</body>
</html>

