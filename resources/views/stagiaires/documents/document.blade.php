<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Titre de congé — {{ $personnel->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 14px; color: #000; background: #eee; }
        .page { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; padding: 12mm 18mm 22mm; box-shadow: 0 4px 24px rgba(0,0,0,.15); position: relative; }
        /* Letterhead */
        .lh { display: flex; align-items: center; gap: 14px; padding-bottom: 10px; border-bottom: 2px solid #000; margin-bottom: 16px; }
        .lh-img-left  { width: 64px; height: 64px; object-fit: cover; flex-shrink: 0; }
        .lh-img-right { width: 70px; height: 70px; object-fit: contain; flex-shrink: 0; }
        .lh-center { flex: 1; text-align: center; line-height: 1.5; }
        .lh-l1 { font-size: 11px; font-weight: 700; letter-spacing: .04em; }
        .lh-l2 { font-size: 10px; }
        .lh-l3 { font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 2px 0; }
        .lh-l4 { font-size: 11px; font-weight: 700; }
        .lh-l5 { font-size: 9px; color: #444; margin-top: 3px; }
        /* Doc */
        .doc-date-right { text-align: right; font-weight: 700; font-size: 13px; margin-bottom: 8px; }
        .doc-ref  { font-size: 13px; font-style: italic; font-weight: 700; margin: 6px 0 14px; }
        .doc-title { text-align: center; font-size: 16px; font-weight: 700; text-decoration: underline; text-transform: uppercase; margin-bottom: 20px; letter-spacing: .03em; }
        /* Champs style formulaire */
        .champ-row { display: flex; align-items: baseline; margin-bottom: 6px; font-size: 14px; line-height: 1.7; }
        .champ-label { font-weight: 700; white-space: nowrap; min-width: 0; }
        .champ-dots  { flex: 1; border-bottom: 1px dotted #999; margin: 0 4px 2px; min-width: 20px; }
        .champ-val   { font-weight: 700; white-space: nowrap; }
        .champ-dots-end { flex: 1; border-bottom: 1px dotted #999; margin-left: 4px; min-width: 20px; }
        /* NB */
        .nb-block { font-weight: 700; font-size: 13px; margin: 20px 0; line-height: 1.6; }
        /* Signature */
        .sig-area { margin-top: 24px; display: flex; flex-direction: column; align-items: flex-end; padding-right: 10px; }
        .sig-titre { font-size: 14px; font-weight: 700; text-align: center; min-width: 260px; }
        .sig-img   { height: 110px; width: auto; max-width: 280px; object-fit: contain; display: block; }
        .sig-line  { height: 70px; width: 220px; border-bottom: 1.5px solid #000; }
        .sig-nom   { font-weight: 700; text-decoration: underline; font-size: 14px; text-align: center; min-width: 260px; }
        /* Footer */
        .doc-footer { position: absolute; bottom: 10mm; left: 18mm; right: 18mm; border-top: 1.5px solid #000; padding-top: 5px; text-align: center; font-size: 8px; font-weight: 700; }
        .doc-footer .ft2 { display: flex; justify-content: center; gap: 28px; margin-top: 2px; }
        .no-print { max-width: 210mm; margin: 14px auto; display: flex; gap: 10px; justify-content: center; }
        .btn-print { padding: 9px 24px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-back  { padding: 9px 20px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; }
        @media print { body { background: white; } .page { margin: 0; box-shadow: none; } .no-print { display: none !important; } }
    </style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / PDF</button>
    <button class="btn-back" onclick="window.history.back()">Retour</button>
</div>
<div class="page">

    @php
        $lp = public_path('images/letterhead/logo_archidiocese.jpeg');
        $ep = public_path('images/letterhead/photo_eveque.jpeg');
        $logoB64  = file_exists($lp) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($lp)) : asset('images/letterhead/logo_archidiocese.jpeg');
        $evequB64 = file_exists($ep) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($ep)) : asset('images/letterhead/photo_eveque.jpeg');

        $entree    = $personnel->date_embauche_isd ?? $personnel->date_embauche_centre;
        $anciennete = $entree ? $entree->diffInYears(now()) : 0;
        $maj = 0;
        if ($anciennete >= 30) $maj = 6;
        elseif ($anciennete >= 25) $maj = 4;
        elseif ($anciennete >= 20) $maj = 2;
        $base   = $document->type_conge === 'technique' ? 24 : 24;
        $acquis = min($base + $maj, 30);
    @endphp

    {{-- Letterhead --}}
    <div class="lh">
        <img src="{{ $evequB64 }}" alt="" class="lh-img-left">
        <div class="lh-center">
            <div class="lh-l1">ARCHIDIOCESE DE COTONOU</div>
            <div class="lh-l2">DIRECTION DIOCESAINE DE LA SANTE</div>
            <div class="lh-l3">CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC</div>
            <div class="lh-l4">C.S.V.H (ex : H&ocirc;pital Saint LUC)</div>
            <div class="lh-l5">Qtier Miss&egrave;kpl&eacute; Ste Rita - 01 BP 3603 &nbsp;|&nbsp; T&eacute;l : 66 43 44 78 &ndash; 90 07 49 67 &nbsp;|&nbsp; hopitalsaintluc@gmail.com &nbsp;|&nbsp; Cotonou &ndash; BENIN</div>
        </div>
        <img src="{{ $logoB64 }}" alt="" class="lh-img-right">
    </div>

    <div class="doc-date-right">{{ $ville ?? 'Cotonou' }}, le {{ $date_doc }}</div>
    <div class="doc-ref">N/REF : {{ $document->reference ?? '…../'.now()->format('m').'-'.now()->format('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH' }}</div>

    {{-- Titre --}}
    @if($document->type_conge === 'maternite')
        <div class="doc-title">TITRE DE CONG&Eacute; DE MATERNIT&Eacute;</div>
    @elseif($document->type_conge === 'technique')
        <div class="doc-title">TITRE DE CONG&Eacute;S TECHNIQUES</div>
    @else
        <div class="doc-title">TITRE DE CONG&Eacute;S ADMINISTRATIFS</div>
    @endif

    {{-- Champs --}}
    <div class="champ-row">
        <span class="champ-label">NOM ET PR&Eacute;NOM :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">QUALIFICATION :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $personnel->corporation ?: '—' }}</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">SERVICE :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $personnel->service ?: '—' }}</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">ANN&Eacute;E :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $document->annee }}</span>
        <span class="champ-dots-end"></span>
    </div>

    @if($document->type_conge === 'maternite')
    {{-- Maternité --}}
    <div class="champ-row">
        <span class="champ-label">NOMBRE DE SEMAINES :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">14 Semaines</span>
        <span class="champ-dots-end"></span>
    </div>
    @else
    {{-- Administratif / Technique --}}
    <div class="champ-row">
        <span class="champ-label">ANCIENNET&Eacute; :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $anciennete < 20 ? '< 20 ans' : $anciennete . ' ans' }}</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">MAJORATION ANCIENNET&Eacute; :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ str_pad($maj, 2, '0', STR_PAD_LEFT) }} jours</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">MAJORATION JOURS F&Eacute;RI&Eacute;S :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ str_pad($document->majoration_feries ?? 0, 2, '0', STR_PAD_LEFT) }} jours</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">NOMBRE DE JOURS DE CONG&Eacute;S :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $acquis }} jours</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">NOMBRE DE JOURS DEMAND&Eacute;S :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $document->nb_jours_demandes }} jours</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">NOMBRE DE JOURS D&Eacute;FALQU&Eacute;S :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ str_pad($document->nb_jours_deja_pris ?? 0, 2, '0', STR_PAD_LEFT) }} jours</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">NOMBRE DE JOURS ACCORD&Eacute;S :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $document->nb_jours_demandes }} jours ouvrables</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">NOMBRE DE JOURS RESTANTS :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ str_pad($document->nb_jours_restants, 2, '0', STR_PAD_LEFT) }} Jours</span>
        <span class="champ-dots-end"></span>
    </div>
    @endif

    <div class="champ-row">
        <span class="champ-label">DATE DE D&Eacute;PART :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $document->date_debut->isoFormat('DD MMMM YYYY') }}</span>
        <span class="champ-dots-end"></span>
    </div>
    <div class="champ-row">
        <span class="champ-label">DATE DE REPRISE :&nbsp;</span>
        <span class="champ-dots"></span>
        <span class="champ-val">{{ $document->date_fin->isoFormat('DD MMMM YYYY') }}</span>
        <span class="champ-dots-end"></span>
    </div>

    <div class="nb-block">
        NB : Nous vous rappelons qu&apos;il faudra d&eacute;poser au service des Ressources Humaines,
        la fiche de reprise de service d&ucirc;ment remplie et sign&eacute;e par votre chef service
        d&egrave;s votre retour.
    </div>

    {{-- Signature --}}
    <div class="sig-area">
        <div class="sig-titre">Directeur des Ressources Humaines</div>
        @if(!empty($signature_url))
            <img src="{{ $signature_url }}" alt="Signature" class="sig-img">
        @else
            <div class="sig-line"></div>
        @endif
        <div class="sig-nom">{{ $drh_nom }}</div>
    </div>

    <div class="doc-footer">
        <div>AUTORISATION DU MINISTERE N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020</div>
        <div class="ft2"><span>N&deg;INSAE : 2988511276715</span><span>N&deg; IFU 3200800472415</span></div>
    </div>
</div>
</body>
</html>
