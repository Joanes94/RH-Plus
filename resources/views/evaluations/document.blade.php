{{-- resources/views/evaluations/document.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche d'évaluation - {{ $stagiaire->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 13px; -webkit-font-smoothing: antialiased; }
        body { font-family: 'Times New Roman', Times, serif; background: #f5f4f0; color: #000; }

        .doc-page {
            width: 210mm; min-height: 297mm; margin: 20px auto;
            background: white; position: relative;
            padding: 12mm 18mm 16mm;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
        }

        /* En-tête */
        .letterhead { display: flex; align-items: center; gap: 14px; padding-bottom: 10px; border-bottom: 2.5px solid #000; margin-bottom: 18px; }
        .lh-img-left  { width: 70px; height: 70px; object-fit: cover; flex-shrink: 0; }
        .lh-img-right { width: 76px; height: 76px; object-fit: contain; flex-shrink: 0; }
        .lh-center { flex: 1; text-align: center; line-height: 1.5; }
        .lh-line1 { font-size: 1.05rem; font-weight: 700; letter-spacing: .02em; margin-bottom: 1px; }
        .lh-line2 { font-size: .95rem; font-weight: 400; margin-bottom: 3px; }
        .lh-line3 { font-size: 1.05rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0; }
        .lh-line4 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
        .lh-line5 { font-size: .82rem; line-height: 1.5; color: #444; margin-top: 3px; }

        .doc-title {
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 20px 0 30px;
            letter-spacing: .04em;
        }

        .doc-body { font-size: .98rem; line-height: 1.6; text-align: justify; }
        .doc-body .field-label {
            font-weight: 700;
            display: block;
            margin-top: 18px;
            margin-bottom: 6px;
            font-size: 1rem;
        }
        .doc-body .field-value {
            padding: 10px 14px;
            min-height: 90px;
            border: 1.3px solid #000;
            margin-bottom: 4px;
            white-space: pre-wrap;
        }

        .signature-block {
            margin-top: 40px;
            text-align: right;
            padding-right: 30px;
        }
        .sig-fait { font-style: italic; font-size: .85rem; margin-bottom: 10px; }
        .sig-titre { font-weight: 700; font-size: 11px; }
        .sig-image-wrap { text-align: right; margin: 0; line-height: 0; }
        .sig-image { height: 130px; width: auto; max-width: 300px; object-fit: contain; display: inline-block; }
        .sig-line { height: 80px; width: 240px; border-bottom: 1.5px solid #000; margin: 0 0 0 auto; }
        .sig-blank { height: 90px; }
        .sig-name { font-weight: 700; text-align: right; margin-top: 0; text-decoration: underline; font-size: 1rem; }

        .doc-footer {
            position: absolute; bottom: 10mm; left: 18mm; right: 18mm;
            border-top: 1.5px solid #000; padding-top: 6px;
            text-align: center; font-size: .72rem; font-weight: 700;
        }
        .doc-footer .ft-line2 { display: flex; justify-content: center; gap: 30px; margin-top: 2px; }

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

    {{-- En-tête --}}
    <div class="letterhead">
        <img src="{{ $eveque_b64 ?? '' }}" alt="" class="lh-img-left">
        <div class="lh-center">
            <div class="lh-line1">ARCHIDIOCESE DE COTONOU</div>
            <div class="lh-line2">DIRECTION DIOCESAINE DE LA SANTE</div>
            <div class="lh-line3">CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC</div>
            <div class="lh-line4">C.S.V.H (ex : Hôpital Saint LUC)</div>
            <div class="lh-line5">
                Qtier Missèkplé Ste Rita - 01 BP 3603 | Tél : 66 43 44 78 – 90 07 49 67 | hopitalsaintluc@gmail.com | Cotonou – BENIN
            </div>
        </div>
        <img src="{{ $logo_b64 ?? '' }}" alt="" class="lh-img-right">
    </div>

    {{-- Titre --}}
    <div class="doc-title">FICHE D'ÉVALUATION INDIVIDUELLE DES STAGIAIRES</div>

    {{-- Corps --}}
    <div class="doc-body">

        {{-- Identité --}}
        <p style="font-weight:700;font-size:1rem;margin-bottom:10px;">
            NOM : <span style="font-weight:400;">{{ strtoupper($stagiaire->nom) }}</span>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; PRENOMS : <span style="font-weight:400;">{{ $stagiaire->prenoms }}</span>
        </p>
        <p style="font-weight:700;font-size:1rem;margin-bottom:20px;">
            SERVICE : <span style="font-weight:400;">{{ $stagiaire->service ?: '—' }}</span>
        </p>

        {{-- I. Qualités --}}
        <div class="field-label">I- &nbsp;QUALITES</div>
        <div class="field-value">{{ $evaluation->qualites ?: '' }}</div>

        {{-- II. Défauts --}}
        <div class="field-label">II- &nbsp;DEFAUTS</div>
        <div class="field-value">{{ $evaluation->defauts ?: '' }}</div>

        {{-- III. Maîtrise de la pratique --}}
        <div class="field-label">III- &nbsp;MAITRISE DE LA PRATIQUE</div>
        <div class="field-value">{{ $evaluation->maitrise_pratique ?: '' }}</div>

        {{-- IV. Appréciation personnelle --}}
        <div class="field-label">IV- &nbsp;APPRECIATION PERSONNELLE</div>
        <div class="field-value">{{ $evaluation->appreciation_personnelle ?: '' }}</div>
    </div>

    {{-- Date et signature --}}
    <div class="signature-block">
        <div class="sig-fait">Cotonou le <span style="text-decoration:underline;">{{ $date_doc }}</span></div>
        <div style="font-weight:700;margin-bottom:10px;text-decoration:underline;">Nom, Prénoms et Signature du chef service</div>
        <div class="sig-blank"></div>
    </div>

    {{-- Pied de page --}}
    <div class="doc-footer">
        <div>NOUVELLE AUTORISATION MINISTERIELLE N°071/MS/DC/SGM/CJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020</div>
        <div class="ft-line2">
            <span>N°INSAE : 2988511276715</span>
            <span>N° IFU 3200800472415</span>
        </div>
    </div>

</div>
</body>
</html>