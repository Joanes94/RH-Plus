@php
    use Carbon\Carbon;

    $estCdd = $contrat->type_contrat === 'CDD';

    $civilite       = $personnel->sexe === 'M' ? 'Monsieur' : 'Madame';
    $qualiteEmploye = $personnel->sexe === 'M' ? "L'EMPLOYÉ" : "L'EMPLOYÉE";
    $pronomSujet    = $personnel->sexe === 'M' ? 'Il' : 'Elle';
    $pronomSujetMin = $personnel->sexe === 'M' ? 'il' : 'elle';
    $possessif      = $personnel->sexe === 'M' ? 'qu\'il' : 'qu\'elle';

    $situationFamille = $personnel->situation_matrimoniale ?: '—';
    if ($personnel->enfants_ayants_droit->isNotEmpty() && !str_contains($situationFamille, 'enfant')) {
        $situationFamille .= ' avec enfant(s)';
    }

    $fmtDate = fn ($d) => $d ? ucfirst(Carbon::parse($d)->locale('fr')->isoFormat('D MMMM YYYY')) : '………………………';
    $fmtMontant = fn ($m) => $m ? number_format((float) $m, 0, ',', ' ') : '…………';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat {{ $contrat->type_contrat }} — {{ $personnel->nom_complet }}</title>
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

        .doc-title { text-align: center; margin: 14px 0 20px; }
        .doc-title .t1 { font-size: 1.15rem; font-weight: 700; text-transform: uppercase; }
        .doc-title .t2 { font-size: 1rem; font-weight: 700; }

        .doc-body { font-size: .88rem; line-height: 1.55; text-align: justify; }
        .doc-body p { margin-bottom: 10px; }
        .article-title { font-weight: 700; text-decoration: underline; margin: 14px 0 4px; }
        .partie-label { font-weight: 700; }
        .champ { display: inline-block; min-width: 160px; }

        .signature-row { display: flex; justify-content: space-between; margin-top: 34px; gap: 20px; }
        .signature-col { width: 46%; text-align: center; }
        .signature-col .titre { font-weight: 700; margin-bottom: 30px; }
        .signature-col .nom { font-weight: 700; margin-top: 6px; }

        .visa-block { margin-top: 40px; font-size: .82rem; text-align: center; }
        .visa-block .visa-titre { font-weight: 700; margin-top: 6px; }

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
        <div class="t1">CONTRAT DE TRAVAIL A DUREE {{ $estCdd ? 'DETERMINEE' : 'INDETERMINEE' }}</div>
        <div class="t2">({{ $contrat->type_contrat }})</div>
    </div>

    <div class="doc-body">
        <p style="text-align:center;font-weight:700">ENTRE LES SOUSSIGNÉS</p>

        <p>{{ strtoupper($employeur_nom) }}, {{ $employeur_adresse }}, représenté par son Excellence Monseigneur {{ $representant_nom }}, {{ $representant_titre }}, agissant au nom et pour le compte de {{ $contrat->centre ?: '…………………………' }} demeurant, domicilié et qualifié audit lieu ;</p>
        <p>Ayant la qualité d'Employeur,</p>
        <p style="text-align:center;font-weight:700">D'UNE PART ET,</p>

        <p><span class="partie-label">{{ $civilite }} :</span> {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</p>
        <p><span class="partie-label">TÉL :</span> {{ $personnel->telephone ?: '…………………………' }}</p>
        <p><span class="partie-label">DATE ET LIEU DE NAISSANCE :</span> {{ $fmtDate($personnel->date_naissance) }} @if($personnel->lieu_naissance) à {{ $personnel->lieu_naissance }} @endif</p>
        <p><span class="partie-label">NATIONALITÉ :</span> {{ $personnel->nationalite ?: 'Béninoise' }}</p>
        <p><span class="partie-label">RÉSIDANT HABITUELLEMENT À :</span> {{ $personnel->residence ?: '…………………………' }}</p>
        <p><span class="partie-label">LIEU DE CONGÉ :</span> {{ $contrat->lieu_conge ?: ($personnel->residence ?: '…………………………') }}</p>
        <p><span class="partie-label">SITUATION DE FAMILLE :</span> {{ $situationFamille }}</p>
        <p><span class="partie-label">TITRES ET DIPLÔMES :</span> {{ $personnel->diplome ?: '…………………………' }}</p>
        <p>Ayant la qualité d'Employé,</p>
        <p style="text-align:center;font-weight:700">D'AUTRE PART</p>

        <p>Qui déclare être libre de tout engagement, a été établi le présent contrat régi par la Loi N°98-004 du 27-01-98 portant Code du Travail en République du Bénin et par la Convention Collective Générale du Travail applicable aux entreprises relevant des secteurs privés et para-publics en République du Bénin, et la loi 2017-05 fixant les conditions et la procédure d'embauche, de placement de la main d'œuvre et de résiliation du contrat de travail en République du Bénin ainsi que par les textes subséquents.</p>

        <div class="article-title">Article 1er : NATURE DU CONTRAT</div>
        @if($estCdd)
            <p>Le présent contrat est conclu pour une durée déterminée de {{ $contrat->duree_mois ? sprintf('%02d', $contrat->duree_mois) : '……' }} mois. Il prend effet à compter du {{ $fmtDate($contrat->date_debut) }} et expire de plein droit le {{ $fmtDate($contrat->date_fin) }}.</p>
        @else
            <p>Le présent contrat est conclu pour une durée indéterminée et prend effet à compter du {{ $fmtDate($contrat->date_debut) }}.</p>
        @endif

        <div class="article-title">Article 2 : FONCTIONS ET DURÉE HEBDOMADAIRE DE TRAVAIL</div>
        <p>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }} a été engagé{{ $personnel->sexe === 'F' ? 'e' : '' }} pour exercer sous le contrôle de ses supérieurs hiérarchiques les fonctions {{ $contrat->fonction ? 'de ' . $contrat->fonction : '…………………………' }} au {{ $contrat->centre ?: '…………………………' }}. {{ $pronomSujet }} peut être muté{{ $personnel->sexe === 'F' ? 'e' : '' }} sur toute paroisse ou institution de {{ $employeur_nom }} en cas de besoin.</p>
        <p>La durée hebdomadaire de travail est de 40 heures conformément au Décret n°98-368 du 04 septembre 1998, fixant les heures d'équivalence dans les entreprises régies par le Code du travail.</p>
        <p>{{ $pronomSujet }} s'engage à s'acquitter avec zèle et fidélité des travaux ou missions qui lui seront confiés et à se rendre en tous lieux où l'employeur aura besoin de ses services, toujours dans le cadre de ses activités.</p>
        <p>Le présent contrat est valable pour la RÉPUBLIQUE DU BÉNIN.</p>

        <div class="article-title">Article 3 : REMUNERATION</div>
        <p>Le salaire de base mensuel est de {{ $fmtMontant($contrat->salaire_base) }} Francs CFA.</p>
        <p>Les heures supplémentaires effectuées dans le cadre des dérogations prévues par le Code du Travail en vigueur seront rémunérées conformément aux dispositions de l'article 147 dudit Code.</p>

        <div class="article-title">Article 4 : TAXES ET IMPÔTS</div>
        <p>Les impôts et les régimes sociaux obligatoires seront retenus à la source sur le salaire.</p>

        <div class="article-title">Article 5 : SECURITE SOCIALE</div>
        <p>{{ $contrat->centre ?: "L'employeur" }} s'engage à affilier {{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }} à la Caisse Nationale de Sécurité Sociale (C.N.S.S.).</p>

        <div class="article-title">Article 6 : CONGÉ</div>
        <p>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }} a droit à deux jours ouvrables de congé par mois de service effectif.</p>

        <div class="article-title">Article 7 : SOINS MÉDICAUX</div>
        <p>Conformément à l'article 73 de la Convention Collective Générale du Travail applicable aux entreprises relevant des secteurs privés et para-publics en République du Bénin, {{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }} et les membres de sa famille bénéficient gratuitement de consultations en cas d'urgence suivies de soins dans les services de santé au travail de l'Archevêché de Cotonou ou dans les formations sanitaires agréées.</p>
        <p>En dehors du service de santé au travail de l'Archevêché de Cotonou, {{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }} et les membres de sa famille bénéficient des remboursements par l'employeur, et dans la limite de 60 % des frais occasionnés par une hospitalisation et facturés par les hôpitaux publics ou les formations sanitaires agréées par l'entreprise.</p>

        <div class="article-title">Article 8 : RESILIATION</div>
        <p>Le présent contrat pourra être rompu dans les conditions prévues à l'article 37 de la loi 2017-05 fixant les conditions et la procédure d'embauche, de placement de la main d'œuvre et de résiliation du contrat de Travail en République du Bénin.</p>

        <div class="article-title">Article 9 : CLAUSES DE NON CONCURRENCE</div>
        <p>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }} s'interdit de divulguer, pendant ou après son emploi, tout renseignement de nature confidentielle {{ $possessif }} aura pu recueillir.</p>
        <p>{{ $pronomSujet }} s'engage à consacrer tout son temps dans les limites des règlements en vigueur au service de l'Employeur et s'interdit sans autorisation écrite de celui-ci, même en période de repos, tout travail rémunéré ou non susceptible de concurrencer l'emploi occupé ou de nuire à l'exécution normale des services occupés.</p>

        <div class="article-title">Article 10 : DIFFERENDS</div>
        <p>Le tribunal compétent pour connaître de tous différends liés à l'exécution du présent contrat sera le tribunal du lieu de travail ou de la résidence habituelle de {{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}.</p>

        <div class="article-title">Article 11 : DIVERS</div>
        <p>Pour tout ce qui n'est pas précisé au présent contrat, les parties s'en remettent aux dispositions légales, réglementaires ou conventionnelles en vigueur en République du Bénin.</p>

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
            <div class="titre">{{ $qualiteEmploye }}</div>
            <div style="height:60px"></div>
            <div class="nom">{{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</div>
        </div>
    </div>

    <div class="visa-block">
        <div>VISÉ ET ENREGISTRÉ SOUS LE N° {{ $contrat->numero_visa ?: '…………/MTFP/SGM/DDTFP-LITT/SITPS/SD' }} DU {{ $contrat->date_visa ? $fmtDate($contrat->date_visa) : '……………' }}</div>
        <div class="visa-titre">La Directrice Départementale du Travail et de la Fonction Publique du Littoral</div>
        <div>{{ $directrice_travail }}</div>
    </div>

</div>
</body>
</html>
