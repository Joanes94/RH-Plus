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
        <div class="letterhead">
            <img src="data:image/jpeg;base64,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" alt="" class="letterhead-logo-left">
            <div class="lh-center">
                <div class="lh-line1">ARCHIDIOCESE DE COTONOU</div>
                <div class="lh-line2">DIRECTION DIOCESAINE DE LA SANTE</div>
                <div class="lh-line3">{{ $centre?->nom ?? 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC' }}</div>
                @if($centre && $centre->code !== 'ST_LUC')
                    <div class="lh-line4">{{ $centre->reference_suffix }}</div>
                @else
                    <div class="lh-line4">C.S.V.H (ex : Hôpital Saint LUC)</div>
                @endif
                <div class="lh-line5">
                    {!! nl2br(e($entete_texte ?? 'Qtier Missèkplé Ste Rita - 01 BP 3603 Tél : 66 43 44 78 – 90 07 49 67 / Email : hopitalsaintluc@gmail.com / Cotonou – BENIN')) !!}
                </div>
            </div>
            @if(!empty($centre_logo))
                <img src="{{ $centre_logo }}" alt="" class="letterhead-logo-right" style="max-height: 70px; width: auto; object-fit: contain;">
            @else
                <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEA3ADcAAD/2wBDAAIBAQEBAQIBAQECAgICAgQDAgICAgUEBAMEBgUGBgYFBgYGBwkIBgcJBwYGCAsICQoKCgoKBggLDAsKDAkKCgr/2wBDAQICAgICAgUDAwUKBwYHCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgr/wAARCAC3ALcDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKGkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9/KKKKACiiigAooooAKKKf/Z" alt="" class="letterhead-logo-right">
            @endif
        </div>
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

    {{-- ═══ PIED DE PAGE ═══ --}}
    <div class="doc-footer">
        <div class="ft-line1">{{ $autorisationFooter ?? 'AUTORISATION  DU MINISTERE  N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020' }}</div>
        <div class="ft-line2">
            <span>N°INSAE : 2988511276715</span>
            <span>N° IFU 3200800472415</span>
        </div>
    </div>

</div>
</body>
</html>
