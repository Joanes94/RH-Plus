<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('doc-title') — {{ $stagiaire->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; background: #eee; }
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
        /* Ref / Titre */
        .doc-ref  { font-size: 11px; font-style: italic; font-weight: 700; margin: 12px 0 6px; }
        .doc-title-box { text-align: center; margin: 10px 0 18px; }
        .doc-title-box h1 { display: inline-block; border: 2px solid #000; padding: 5px 28px; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        /* Corps */
        .doc-body { font-size: 12px; line-height: 2; text-align: justify; }
        .doc-body p { margin-bottom: 12px; }
        .doc-body strong { font-weight: 700; }
        .indent { text-indent: 48px; }
        .doc-greeting { text-align: center; text-indent: 0; font-weight: 700; margin-bottom: 14px; }
        /* Destinataire */
        .doc-date-right { text-align: right; font-style: italic; font-size: 11px; margin-bottom: 14px; }
        .doc-dest {
            text-align: right;
            padding-right: 30px;
            margin-bottom: 16px;
            line-height: 1.7;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .doc-dest .dest-a { font-weight: 700; line-height: 1; }
        .doc-dest .dest-name { font-weight: 700; }
        .doc-dest .dest-fn { font-style: italic; }
        /* Objet */
        .doc-object { font-weight: 700; font-size: 12px; margin-bottom: 14px; }
        /* Signature */
        .doc-sig-area { margin-top: 36px; text-align: right; padding-right: 20px; }
        .doc-fait { font-style: italic; font-size: 11px; margin-bottom: 8px; }
        .sig-titre { font-weight: 700; font-size: 12px; margin-bottom: 4px; }
        .sig-img { height: 130px; width: auto; max-width: 300px; object-fit: contain; display: block; margin-left: auto; }
        .sig-line { height: 80px; width: 220px; border-bottom: 1.5px solid #000; margin: 0 0 4px auto; }
        .sig-nom { font-weight: 700; text-decoration: underline; font-size: 12px; }
        /* Footer */
        .doc-footer { position: absolute; bottom: 10mm; left: 18mm; right: 18mm; border-top: 1.5px solid #000; padding-top: 5px; text-align: center; font-size: 8px; font-weight: 700; }
        .doc-footer .ft2 { display: flex; justify-content: center; gap: 28px; margin-top: 2px; }
        /* No-print */
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

    {{-- LETTERHEAD --}}
    @php
        $lp = public_path('images/letterhead/logo_archidiocese.jpeg');
        $ep = public_path('images/letterhead/photo_eveque.jpeg');
        $logoB64  = file_exists($lp) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($lp)) : asset('images/letterhead/logo_archidiocese.jpeg');
        $evequB64 = file_exists($ep) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($ep)) : asset('images/letterhead/photo_eveque.jpeg');
    @endphp
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

    {{-- CONTENU --}}
    @yield('doc-content')

    {{-- SIGNATURE --}}
    <div class="doc-sig-area">
        <div class="sig-titre">{{ $drh_titre ?? 'Directeur des Ressources Humaines' }}</div>
        @if(!empty($signature_url))
            <img src="{{ $signature_url }}" alt="Signature" class="sig-img">
        @else
            <div class="sig-line"></div>
        @endif
        <div class="sig-nom">{{ $drh_nom ?? '' }}</div>
    </div>

    {{-- FOOTER --}}
    <div class="doc-footer">
        <div>NOUVELLE AUTORISATION MINISTERIELLE N&deg;071/MS/DC/SGM/CJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020</div>
        <div class="ft2">
            <span>N&deg;INSAE : 2988511276715</span>
            <span>N&deg; IFU 3200800472415</span>
        </div>
    </div>

</div>
</body>
</html>