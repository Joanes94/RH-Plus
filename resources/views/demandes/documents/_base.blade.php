{{--
    _base.blade.php — Template de base reproduisant fidèlement
    le format des documents CSVH Saint Luc (Archidiocèse de Cotonou).

    Variables disponibles :
      $demande, $personnel, $est_femme, $civilite, $le_la, $du_de_la,
      $nomme_e, $employe_e, $drh_nom, $drh_titre, $organisation,
      $ville, $signature_url, $date_doc, $refCode, $autorisationFooter

    Sections à fournir par les vues filles :
      @section('doc-ref')   → référence document
      @section('doc-title') → titre du document
      @section('doc-body')  → corps de la lettre
      @section('doc-object') → objet (optionnel)
      @section('doc-dest')   → destinataire (optionnel)
      @section('doc-date-top') → date/lieu en haut à droite (optionnel)
      @section('doc-fait')   → "Fait à ... le ..." personnalisé (optionnel)
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('doc-title', 'Document') — {{ $personnel->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 13px; -webkit-font-smoothing: antialiased; }
        body { font-family: 'Calibri', 'Segoe UI', Arial, sans-serif; font-size: 14px;
            background: #f5f4f0; color: #000;
        }
         

        .doc-page {
            width: 210mm; min-height: 297mm; margin: 20px auto;
            background: white; position: relative;
            padding: 12mm 18mm 16mm;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
        }

        /* ── EN-TÊTE ─────────────────────────────────────────────── */
        .letterhead {
            position: relative;
            text-align: center;
            padding-bottom: 8px;
            border-bottom: 2.5px solid #000;
            margin-bottom: 18px;
            min-height: 90px;
        }
        .letterhead-logo-left {
            position: absolute; left: -4px; top: -6px;
            width: 70px; height: auto;
        }
        .letterhead-logo-right {
            position: absolute; right: 4px; top: -6px;
            width: 78px; height: auto;
        }
        .lh-line1 { font-size: 1.05rem; font-weight: 700; letter-spacing: .02em; margin-bottom: 1px; }
        .lh-line2 { font-size: .95rem; font-weight: 400; margin-bottom: 3px; }
        .lh-line3 { font-size: 1.05rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0px; }
        .lh-line4 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
        .lh-line5 { font-size: .82rem; line-height: 1.5; padding: 0 75px; }

        /* ── RÉFÉRENCE ───────────────────────────────────────────── */
        .doc-ref-line {
            font-size: .92rem; font-style: italic; font-weight: 700;
            margin: 14px 0 8px;
        }

        /* ── TITRE ───────────────────────────────────────────────── */
        .doc-title-line {
            text-align: center; font-size: 1.05rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .03em;
            margin: 8px 0 18px;
        }

        /* ── CORPS ───────────────────────────────────────────────── */
        .doc-body {
            font-size: .98rem; line-height: 2;
            text-align: justify;
        }
        .doc-body p {
            margin-bottom: 12px;
            text-align: justify;
        }
        .doc-body p.indent-first { text-indent: 50px; }
        .doc-body .doc-greeting {
            text-align: center;
            text-indent: 0;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .doc-body strong { font-weight: 700; }
        .indent-first { text-indent: 50px; }

        /* Destinataire (lettres "À ...") */
        .doc-dest {
            margin: 10px 0 16px auto;
            text-align: right;
            padding-right: 40px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 1px;
            width: fit-content;
            max-width: 100%;
        }
        .doc-dest-a { font-weight: 700; text-align: right; line-height: 1; margin-right: 0; }
        .doc-dest-name { font-weight: 700; text-align: right; }
        .doc-dest-fn   { text-align: right; font-style: italic; }

        .doc-date-lieu { text-align: right; font-style: italic; margin-bottom: 16px; }

        .doc-object { font-weight: 700; margin-bottom: 14px; }

        /* ── SIGNATURE ───────────────────────────────────────────── */
        .doc-signature-block { margin-top: 0; width: 260px; margin-left: auto; margin-right: 40px; text-align: center; }
        .doc-fait-le { text-align: right; font-style: italic; margin-top: 48px; margin-bottom: 20px; padding-right: 30px; }
        .sig-title { font-weight: 700; text-align: right; margin-bottom: 4px; font-size: 1rem; }
        .sig-image-wrap { text-align: right; margin: 0 auto; line-height: 0; height: 78px; width: 260px; overflow: hidden; display: flex; align-items: right; justify-content: right; }
        .sig-image { height: 120px; width: auto; max-width: none; object-fit: contain; display: block; }
        .sig-line  { height: 80px; width: 220px; border-bottom: 1.5px solid #000; margin: 0 auto; }
        .sig-name  { font-weight: 700; text-align: right; margin-top: 0; text-decoration: underline; font-size: 1rem; }

        /* ── PIED DE PAGE ────────────────────────────────────────── */
        .doc-footer {
            position: absolute; bottom: 10mm; left: 18mm; right: 18mm;
            border-top: 1.5px solid #000;
            padding-top: 6px;
            text-align: center;
            font-size: .72rem; font-weight: 700;
        }
        .doc-footer .ft-line1 { margin-bottom: 2px; }
        .doc-footer .ft-line2 { display: flex; justify-content: center; gap: 30px; }

        /* ── ACTIONS (non imprimées) ─────────────────────────────── */
        .no-print { margin: 20px auto; max-width: 210mm; display: flex; gap: 10px; justify-content: center; }
        .btn-print { padding: 10px 24px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: 'DM Sans', Arial, sans-serif; }
        .btn-close { padding: 10px 20px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: .875rem; cursor: pointer; font-family: 'DM Sans', Arial, sans-serif; }

        @media print {
            body { background: white; }
            .doc-page { margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
    @stack('doc-styles')
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / Sauvegarder en PDF</button>
    <button class="btn-close" onclick="window.close()">Fermer</button>
</div>

<div class="doc-page">

    {{-- En-tête dynamique ou Image d'en-tête --}}
    @if(!empty($entete_image_url))
        <div class="letterhead-image-only" style="text-align: center; margin-bottom: 18px; border-bottom: 2.5px solid #000; padding-bottom: 8px;">
            <img src="{{ $entete_image_url }}" alt="En-tête" style="width: 100%; height: auto; max-height: 150px; display: block; object-fit: contain;">
        </div>
    @else
        @include('partials._letterhead')
    @endif
{{-- ═══ DATE / LIEU EN HAUT (pour lettres avec en-tête à droite) ═══ --}}
    @hasSection('doc-date-top')
    <div class="doc-date-lieu">@yield('doc-date-top')</div>
    @endif

    {{-- ═══ DESTINATAIRE (pour lettres nominatives) ═══ --}}
    @hasSection('doc-dest')
    <div class="doc-dest">@yield('doc-dest')</div>
    @endif

    {{-- ═══ RÉFÉRENCE ═══ --}}
    @hasSection('doc-ref')
    <div class="doc-ref-line">
        N/REF : @yield('doc-ref')
    </div>
    @endif

    {{-- ═══ OBJET ═══ --}}
    @hasSection('doc-object')
    <div class="doc-object">Objet : @yield('doc-object')</div>
    @endif

    {{-- ═══ TITRE ═══ --}}
    @hasSection('doc-title')
    <div class="doc-title-line">@yield('doc-title')</div>
    @endif

    {{-- ═══ CORPS ═══ --}}
    <div class="doc-body">
        @yield('doc-body')
    </div>

    {{-- ═══ FAIT À / DATE ═══ --}}
    @hasSection('doc-fait')
        <div class="doc-fait-le">@yield('doc-fait')</div>
    @else
        <div class="doc-fait-le">Fait à {{ $ville }}, le {{ $date_doc }}</div>
    @endif

    {{-- ═══ SIGNATURE ═══ --}}
    <div class="doc-signature-block">
        <div class="sig-title">{{ $drh_titre }}</div>
        <div class="sig-image-wrap">
            @if($signature_url)
                <img src="{{ $signature_url }}" alt="Signature" class="sig-image">
            @else
                <div class="sig-line"></div>
            @endif
        </div>
        <div class="sig-name">{{ $drh_nom }}</div>
    </div>

    {{-- ═══ PIED DE PAGE DYNAMIQUE ═══ --}}
    @include('partials._footer')

</div>
</body>
</html>
