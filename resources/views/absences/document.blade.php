<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorisation d'absence — {{ $personnel->nom_complet }}</title>
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
        .letterhead { position: relative; text-align: center; padding-bottom: 8px; border-bottom: 2.5px solid #000; margin-bottom: 35px; min-height: 90px; }
        .letterhead-logo-left  { position: absolute; left: -4px; top: -6px; width: 70px; height: auto; }
        .letterhead-logo-right { position: absolute; right: 4px; top: -6px; width: 78px; height: auto; }
        .lh-line1 { font-size: 1.05rem; font-weight: 700; letter-spacing: .02em; margin-bottom: 1px; }
        .lh-line2 { font-size: .95rem; font-weight: 400; margin-bottom: 3px; }
        .lh-line3 { font-size: 1.05rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0; }
        .lh-line4 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
        .lh-line5 { font-size: .82rem; line-height: 1.5; padding: 0 75px; }

        .doc-date-lieu { text-align: right; font-style: italic; margin-bottom: 16px; }
        .doc-dest { margin: 10px 0 16px; text-align: right; padding-right: 40px; }
        .doc-dest-a { font-weight: 700; text-align: right; }
        .doc-dest-name { font-weight: 700; text-align: right; }
        .doc-dest-fn   { text-align: right; font-style: italic; }

        .doc-ref-line { font-size: .92rem; font-style: italic; font-weight: 700; margin: 14px 0 8px; }
        .doc-object   { font-weight: 700; margin-bottom: 14px; }

        .doc-body { font-size: .98rem; line-height: 2; text-align: justify; }
        .doc-body p { margin-bottom: 12px; }
        .doc-body strong { font-weight: 700; }
        .indent-first { text-indent: 50px; }

        .doc-signature-block { margin-top: 0; width: 260px; margin-left: auto; margin-right: 40px; text-align: center; }
        .sig-title { font-weight: 700; text-align: center; margin-bottom: 4px; font-size: 1rem; }
        .sig-image-wrap { text-align: center; margin: 0 auto; line-height: 0; height: 78px; width: 260px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .sig-image { height: 120px; width: auto; max-width: none; object-fit: contain; display: block; }
        .sig-line  { height: 80px; width: 240px; border-bottom: 1.5px solid #000; margin: 0 0 0 auto; }
        .sig-name  { font-weight: 700; text-align: center; margin-top: 0; text-decoration: underline; font-size: 1rem; }

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

    {{-- En-tête dynamique ou Image d'en-tête --}}
    @if(!empty($entete_image_url))
        <div class="letterhead-image-only" style="text-align: center; margin-bottom: 18px; border-bottom: 2.5px solid #000; padding-bottom: 8px;">
            <img src="{{ $entete_image_url }}" alt="En-tête" style="width: 100%; height: auto; max-height: 150px; display: block; object-fit: contain;">
        </div>
    @else
        @include('partials._letterhead')
    @endif
{{-- Date / lieu --}}
    <div class="doc-date-lieu">{{ $ville }}, le {{ $date_doc }}</div>

    {{-- Destinataire --}}
    @php
        $civiliteDest = $personnel->sexe === 'F' ? 'Madame' : 'Monsieur';
    @endphp
    <div class="doc-dest">
        <div class="doc-dest-a">A</div>
        <div class="doc-dest-name">{{ $civiliteDest }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</div>
        @if($personnel->corporation)
        <div class="doc-dest-fn">{{ $personnel->corporation }}</div>
        @endif
    </div>

    {{-- Référence --}}
    <div class="doc-ref-line">
        N/REF : {{ $document->reference ?? '…../'.now()->format('m').'-'.now()->format('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH' }}
    </div>

    {{-- Objet --}}
    <div class="doc-object">Objet : Autorisation d'absence</div>

    {{-- Corps --}}
    <div class="doc-body">
        <p>{{ $civiliteDest }},</p>

        @php
            $decesTypes = ['deces_conjoint', 'deces_parent_enfant', 'deces_frere_soeur_beau'];
            $estAbsenceDeces = in_array($document->type_absence, $decesTypes, true);
        @endphp

        @php
            // Construire la liste des jours au format "06, 07 et 10 Novembre 2025"
            $debut = $document->date_debut;
            $fin   = $document->date_fin;
            $joursListe = '';
            if ($debut && $fin) {
                $jours = [];
                $cur = $debut->copy();
                while ($cur->lte($fin)) {
                    $jours[] = $cur->format('d');
                    $cur->addDay();
                }
                if (count($jours) > 1) {
                    $last = array_pop($jours);
                    $joursListe = implode(', ', $jours) . ' et ' . $last;
                } else {
                    $joursListe = $jours[0];
                }
                $joursListe .= ' ' . $debut->isoFormat('MMMM YYYY');
            }
        @endphp

        <p class="indent-first">
            Suite à votre demande
            @if($document->created_at) du {{ $document->created_at->isoFormat('DD MMMM YYYY') }} @endif,
            il vous est accordé une autorisation d'absence
            @if($joursListe) les <strong>{{ $joursListe }}</strong> @endif
            @if($document->motif), pour {{ $document->motif }} @endif.
        </p>

        <p>
            @if($estAbsenceDeces)
                En effet, ces jours seront déduits de votre prochain congé administratif.
            @elseif($document->deductible)
                En effet, ces jours seront déduits de votre prochain congé administratif.
            @else
                Ces jours ne seront pas déduits de votre prochain congé administratif.
            @endif
        </p>

        @if($estAbsenceDeces)
        <p>
            Nous vous présentons nos sincères condoléances. Que le Seigneur accueille votre proche en sa demeure.
        </p>
        @endif

        @if($fin)
           <p>
              Vous reprendrez service le <strong>{{ $document->date_reprise->isoFormat('dddd DD MMMM YYYY') }}</strong>.
           </p>
        @endif

        <p>
            <strong>Nous vous rappelons qu'il faudra déposer au service des Ressources Humaines, la fiche
            de reprise de service dûment remplie et signée par votre chef service dès votre retour.</strong>
        </p>
    </div>

    {{-- Signature --}}
    <div class="doc-signature-block" style="margin-top: 40px;">
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

    {{-- Pied de page DYNAMIQUE --}}
    @include('partials._footer')

</div>
</body>
</html>
