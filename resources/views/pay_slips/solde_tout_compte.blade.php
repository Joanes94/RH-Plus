<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu pour Solde de Tout Compte - {{ $paySlip->personnel->nom_complet }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
            padding: 30px;
            background-color: #fff;
        }

        .header-container {
            margin-bottom: 25px;
            border-bottom: 3px double var(--col-primary, #1a5c45);
            padding-bottom: 10px;
        }

        .header-branding {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-logo {
            height: 60px;
            object-fit: contain;
        }

        .header-text {
            text-align: center;
            flex: 1;
            padding: 0 15px;
        }

        .header-title-text {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--col-primary, #1a5c45);
            margin: 0 0 4px;
        }

        .header-subtext {
            font-size: 9px;
            color: #4b5563;
            margin: 0;
        }

        .doc-title {
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111827;
            margin: 30px 0;
            border-bottom: 2px solid #111827;
            padding-bottom: 8px;
        }

        .text-content {
            text-align: justify;
            margin-bottom: 25px;
            text-justify: inter-word;
        }

        .table-compte {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table-compte th {
            background-color: #f3f4f6;
            font-weight: 700;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #d1d5db;
        }

        .table-compte td {
            padding: 8px 10px;
            border: 1px solid #d1d5db;
        }

        .signatures-area {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .signature-box {
            width: 250px;
            text-align: center;
        }

        .signature-line {
            margin-top: 70px;
            border-top: 1px dashed #9ca3af;
            padding-top: 5px;
            font-style: italic;
            color: #6b7280;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    {{-- Bouton d'impression --}}
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: var(--col-primary, #1a5c45); color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 12px;">
            Imprimer le reçu
        </button>
    </div>

    {{-- En-tête de document --}}
    <div class="header-container">
        @if($enteteBase64)
            <img src="{{ $enteteBase64 }}" alt="En-tête officiel" style="width: 100%; max-height: 100px; object-fit: contain;">
        @else
            <div class="header-branding">
                <img src="{{ $dioceseLogoBase64 }}" class="header-logo" alt="Logo Diocèse">
                <div class="header-text">
                    <h2 class="header-title-text">Archidiocèse de Cotonou</h2>
                    <p class="header-subtext" style="font-weight: 600;">{{ $paySlip->centre->nom }}</p>
                    <p class="header-subtext">{{ $paySlip->centre->adresse }} | Tél: {{ $paySlip->centre->telephone }}</p>
                </div>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo Centre">
                @else
                    <div style="width: 60px;"></div>
                @endif
            </div>
        @endif
    </div>

    <div class="doc-title">Reçu pour Solde de Tout Compte</div>

    <div class="text-content">
        Je soussigné(e) <strong>{{ $paySlip->personnel->nom_complet }}</strong>, 
        exerçant précédemment la fonction de <strong>{{ $paySlip->poste }}</strong> 
        au sein de l'institution <strong>{{ $paySlip->centre->nom }}</strong>, 
        reconnais avoir reçu de la direction dudit établissement, la somme globale et définitive de :
    </div>

    <div style="text-align: center; font-size: 20px; font-weight: 800; color: var(--col-primary, #1a5c45); margin: 20px 0; background-color: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px solid #bbf7d0;">
        {{ number_format($paySlip->salaire_net, 0, ',', ' ') }} FCFA
    </div>

    <div style="text-align: center; font-style: italic; margin-bottom: 25px; font-weight: bold;">
        (Soit : {{ NumberFormatter::create('fr', NumberFormatter::SPELLOUT)->format($paySlip->salaire_net) }} Francs CFA)
    </div>

    <div class="text-content">
        Cette somme m'est versée à titre de solde de tout compte et de règlement définitif de l'ensemble de mes salaires, indemnités, primes et remboursements de toute nature liés à l'exécution et à la cessation de mon contrat de travail, se détaillant comme suit :
    </div>

    <table class="table-compte">
        <thead>
            <tr>
                <th>Éléments de rémunération / Indemnités de départ</th>
                <th style="text-align: right;">Montants</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dernier salaire mensuel (Salaire de base proratisé)</td>
                <td style="text-align: right;">{{ number_format($paySlip->salaire_base * ($paySlip->jours_travailles / 30), 0, ',', ' ') }} F</td>
            </tr>
            @if($paySlip->indemnite_residence > 0)
            <tr>
                <td>Indemnités de résidence afférente</td>
                <td style="text-align: right;">{{ number_format($paySlip->indemnite_residence, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @if($paySlip->moins_percu_rembourse > 0)
            <tr>
                <td>Remboursements nets et rappels sur salaire</td>
                <td style="text-align: right;">{{ number_format($paySlip->moins_percu_rembourse, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @if($paySlip->indemnite_logement + $paySlip->indemnite_transport + $paySlip->autre_indemnite > 0)
            <tr>
                <td>Autres indemnités acquises</td>
                <td style="text-align: right;">{{ number_format($paySlip->indemnite_logement + $paySlip->indemnite_transport + $paySlip->autre_indemnite, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            @php
                $totalRetenues = $paySlip->cotisation_sociale_salarie + $paySlip->impot_its + $paySlip->frais_medicaux + $paySlip->avance_salaire + $paySlip->trop_percu_net + $paySlip->mise_a_pied;
            @endphp
            @if($totalRetenues > 0)
            <tr>
                <td>Déductions fiscales, sociales et remboursements d'avances</td>
                <td style="text-align: right; color: #ef4444;">-{{ number_format($totalRetenues, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td>Net versé</td>
                <td style="text-align: right; color: var(--col-primary, #1a5c45);">{{ number_format($paySlip->salaire_net, 0, ',', ' ') }} F</td>
            </tr>
        </tbody>
    </table>

    <div class="text-content">
        Par la signature du présent reçu, je reconnais que mon compte d'indemnités et de salaires avec <strong>{{ $paySlip->centre->nom }}</strong> est définitivement et entièrement soldé, et je renonce à toute réclamation ultérieure relative aux éléments énumérés ci-dessus.
    </div>

    <div style="margin-top: 30px; text-align: right;">
        Fait à Cotonou, le {{ date('d/m/Y') }}
    </div>

    <div class="signatures-area">
        <div class="signature-box">
            <strong>Le Salarié</strong>
            <div style="font-size: 9px; color: #9ca3af; margin-top: 2px;">(Signature précédée de la mention manuscrite<br>"Bon pour solde de tout compte et renonciation à tout recours")</div>
            <div class="signature-line">Signature de l'ex-salarié</div>
        </div>
        
        <div class="signature-box">
            <strong>La Direction du Centre</strong>
            <div style="font-size: 9px; color: #9ca3af; margin-top: 2.5rem;">(Cachet & Signature officielle de validation)</div>
            <div class="signature-line">Pour acquit et validation</div>
        </div>
    </div>

</body>
</html>
