@php
    use Carbon\Carbon;

    $civilite = $personnel->sexe === 'M' ? 'Monsieur' : 'Madame';

    $fmtDate = fn ($d) => $d ? ucfirst(Carbon::parse($d)->locale('fr')->isoFormat('D MMMM YYYY')) : '………………………';
    $fmtMontant = fn ($m) => $m ? number_format((float) $m, 0, ',', ' ') : '…………';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat de Prestation — {{ $personnel->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 13px; -webkit-font-smoothing: antialiased; }
        body { font-family: 'Times New Roman', Times, serif; background: #f5f4f0; color: #000; }

        .doc-page {
            width: 210mm; min-height: 297mm; margin: 20px auto;
            background: white; position: relative;
            padding: 14mm 18mm 16mm;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
        }

        .letterhead { display: flex; align-items: center; gap: 14px; padding-bottom: 8px; border-bottom: 2.5px solid #000; margin-bottom: 16px; }
        .lh-img-left  { width: 62px; height: 62px; object-fit: cover; flex-shrink: 0; }
        .lh-img-right { width: 66px; height: 66px; object-fit: contain; flex-shrink: 0; }
        .lh-center { flex: 1; text-align: center; line-height: 1.45; }
        .lh-line1 { font-size: .78rem; font-weight: 700; letter-spacing: .03em; }
        .lh-line2 { font-size: .72rem; margin-top: 1px; }
        .lh-line3 { font-size: .84rem; font-weight: 700; text-transform: uppercase; margin: 2px 0; }
        .lh-line4 { font-size: .76rem; font-weight: 700; }
        .lh-line5 { font-size: .64rem; color: #555; margin-top: 2px; }

        .doc-title { text-align: center; margin: 4px 0 6px; }
        .doc-title .t1 { font-size: 1.15rem; font-weight: 700; text-transform: uppercase; }
        .doc-title .t2 { font-size: .85rem; letter-spacing: .3em; margin-top: 2px; }

        .doc-body { font-size: .88rem; line-height: 1.55; text-align: justify; }
        .doc-body p { margin-bottom: 10px; }
        .article-title { font-weight: 700; text-decoration: underline; margin: 14px 0 4px; }
        .partie-label { font-weight: 700; }

        .signature-row { display: flex; justify-content: space-between; margin-top: 34px; gap: 20px; }
        .signature-col { width: 46%; text-align: center; }
        .signature-col .titre { font-weight: 700; margin-bottom: 30px; }
        .signature-col .nom { font-weight: 700; margin-top: 6px; }

        .no-print { margin: 20px auto; max-width: 210mm; display: flex; gap: 10px; justify-content: center; }
        .btn-print { padding: 10px 24px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: 'DM Sans', Arial, sans-serif; }
        .btn-close { padding: 10px 20px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: .875rem; cursor: pointer; font-family: 'DM Sans', Arial, sans-serif; }

        @media print {
            body { background: white; }
            .doc-page { margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / Sauvegarder en PDF</button>
    <button class="btn-close" onclick="window.close()">Fermer</button>
</div>

<div class="doc-page">

    <div class="letterhead">
        <img src="{{ $eveque_b64 ?? $logo_b64 ?? '' }}" alt="Évêque" class="lh-img-left">
        <div class="lh-center">
            <div class="lh-line1">ARCHIDIOCESE DE COTONOU</div>
            <div class="lh-line2">DIRECTION DIOCESAINE DE LA SANTE</div>
            <div class="lh-line3">{{ strtoupper($contrat->centre ?: 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC') }}</div>
            <div class="lh-line4">C.S.V.H (ex : Hôpital Saint LUC)</div>
            <div class="lh-line5">Qtier Missèkplé Ste Rita - 01 BP 3603 | Tél : 66 43 44 78 – 90 07 49 67 | hopitalsaintluc@gmail.com | Cotonou – BENIN</div>
        </div>
        <img src="{{ $logo_b64 ?? '' }}" alt="Logo" class="lh-img-right">
    </div>

    <div class="doc-title">
        <div class="t1">Contrat de prestation de services</div>
        <div class="t2">=====°°°°°=====</div>
    </div>

    <div class="doc-body">
        <p style="text-align:center;font-weight:700">ENTRE LES SOUSSIGNÉS</p>

        <p>{{ strtoupper($employeur_nom) }}, {{ $employeur_adresse }}, représenté par son Excellence Monseigneur {{ $representant_nom }}, {{ $representant_titre }}, agissant au nom et pour le compte du {{ $contrat->centre ?: '…………………………' }} demeurant, domicilié et qualités audit lieu ;</p>
        <p>Ayant la qualité de Client,</p>
        <p style="text-align:center;font-weight:700">D'UNE PART ET,</p>

        <p><span class="partie-label">NOM ET PRENOMS :</span> {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</p>
        <p><span class="partie-label">DATE ET LIEU DE NAISSANCE :</span> {{ $fmtDate($personnel->date_naissance) }} @if($personnel->lieu_naissance) à {{ $personnel->lieu_naissance }} @endif</p>
        <p><span class="partie-label">NATIONALITE :</span> {{ $personnel->nationalite ?: 'Béninoise' }}</p>
        <p><span class="partie-label">RESIDENT HABITUELLEMENT A</span> {{ $personnel->residence ?: '…………………………' }} <span class="partie-label">Tél :</span> {{ $personnel->telephone ?: '…………………………' }}</p>
        <p><span class="partie-label">SITUATION DE FAMILLE :</span> {{ $personnel->situation_matrimoniale ?: '—' }}</p>
        <p><span class="partie-label">TITRES ET DIPLOMES :</span> {{ $contrat->fonction ?: ($personnel->diplome ?: '…………………………') }}</p>
        <p>Ayant la qualité de Prestataire,</p>
        <p style="text-align:center;font-weight:700">D'AUTRE PART</p>

        <div class="article-title">Article 1er : NATURE ET DUREE DU CONTRAT</div>
        <p>Le présent contrat de prestation est conclu pour une durée déterminée de {{ $contrat->duree_libelle ?: '…………' }}. Il prend effet à compter du {{ $fmtDate($contrat->date_debut) }} et expire de plein droit le {{ $fmtDate($contrat->date_fin) }}. Il est renouvelable uniquement par écrit.</p>
        <p>Le Prestataire agit en qualité d'indépendant, sans lien de subordination.</p>

        <div class="article-title">Article 2 : ENGAGEMENT DU CENTRE</div>
        <p>Le Centre s'engage à :</p>
        <p>— faciliter l'accès aux lieux de service au prestataire ;</p>
        <p>— fournir le matériel de travail dans la mesure du possible.</p>

        <div class="article-title">Article 3 : ENGAGEMENT DU PRESTATAIRE</div>
        <p>Le prestataire s'engage à :</p>
        <p>— garder secret tout document, toute information ou dossier dont il aura connaissance dans le cadre de sa prestation ;</p>
        <p>— prendre soin des infrastructures, installations et équipement de l'hôpital.</p>
        <p>Le prestataire exerce en toute indépendance : il organise librement son activité, ne reçoit pas d'instructions hiérarchiques, ne peut être assimilé à un salarié, et supporte seul ses charges fiscales et sociales.</p>

        <div class="article-title">Article 4 : CONTENU DE LA PRESTATION DE SERVICE</div>
        <p>Le contractant, en sa qualité de {{ $contrat->fonction ?: '…………………………' }}, assurera les prestations convenues avec l'administration.</p>

        <div class="article-title">Article 5 : HORAIRES DE TRAVAIL</div>
        <p>Pour des raisons pratiques d'organisation, le prestataire travaillera selon les programmes définis avec l'administration.</p>

        <div class="article-title">Article 6 : HONORAIRES</div>
        <p>Les honoraires de la garde sont fixés à {{ $fmtMontant($contrat->honoraire_garde) }} FCFA et ceux de la permanence à {{ $fmtMontant($contrat->honoraire_permanence) }} FCFA durant la période fixée à l'article 1er.</p>
        <p>Les honoraires perçus sont assujettis à l'Acompte d'Impôt sur le Bénéfice (AIB) 5%. Le paiement des honoraires sera subordonné à la présentation d'une facture normalisée.</p>

        <div class="article-title">Article 7 : RESILIATION</div>
        <p>Le présent contrat de prestation de services pourra être rompu à tout moment sur accord des parties, soit par insatisfaction ou mauvaise prestation, soit par la volonté de l'une des parties.</p>
        <p>En cas de fautes lourdes du prestataire ou en cas de force majeure, le présent contrat est rompu sans besoin d'un préavis.</p>

        <div class="article-title">Article 8 : CLAUSE DE CONFIDENTIALITE</div>
        <p>Le prestataire se garde de divulguer, pendant ou après ses prestations, tous les renseignements de nature confidentielle dont il aurait eu connaissance.</p>

        <div class="article-title">Article 9 : REGLEMENT DES DIFFERENDS</div>
        <p>Pour tout ce qui n'est pas prévu au présent contrat de prestation de services, les parties s'engagent à régler tous leurs différends à l'amiable ou devant les juridictions compétentes.</p>

        <p style="margin-top:18px">Fait en trois (03) exemplaires originaux à {{ $contrat->lieu_signature ?: $ville }}, le {{ $fmtDate($contrat->date_signature) }}</p>
    </div>

    <div class="signature-row">
        <div class="signature-col">
            <div class="titre">L'EMPLOYEUR</div>
            <div style="margin-top:20px">Mgr {{ $representant_nom }} P.O.</div>
            <div class="nom">{{ $delegataire_nom }}</div>
            <div>({{ $delegataire_titre }})</div>
        </div>
        <div class="signature-col">
            <div class="titre">LE PRESTATAIRE</div>
            <div style="height:60px"></div>
            <div class="nom">{{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</div>
        </div>
    </div>

</div>
</body>
</html>
