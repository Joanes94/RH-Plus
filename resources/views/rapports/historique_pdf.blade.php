<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport historique du personnel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #1a1916; background: #ddd; }

        .page { width: 210mm; min-height: 297mm; margin: 24px auto; background: white; padding: 16mm 18mm 22mm; box-shadow: 0 4px 32px rgba(0,0,0,0.15); position: relative; }

        .lh { display: flex; align-items: center; gap: 16px; padding-bottom: 12px; border-bottom: 2px solid #000; margin-bottom: 20px; }
        .lh-img-left  { width: 64px; height: 64px; object-fit: cover; flex-shrink: 0; }
        .lh-img-right { width: 68px; height: 68px; object-fit: contain; flex-shrink: 0; }
        .lh-center { flex: 1; text-align: center; line-height: 1.5; }
        .lh-l1 { font-size: 11px; font-weight: 700; letter-spacing: .04em; }
        .lh-l2 { font-size: 10px; }
        .lh-l3 { font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 2px 0; }
        .lh-l4 { font-size: 11px; font-weight: 700; }
        .lh-l5 { font-size: 9px; color: #555; margin-top: 3px; }

        .rapport-header { text-align: center; margin-bottom: 22px; }
        .rapport-title-box { display: inline-block; border: 2px solid #000; padding: 6px 32px; margin-bottom: 6px; }
        .rapport-title { font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
        .rapport-filtre { font-size: 11px; color: #555; font-style: italic; margin-bottom: 2px; }
        .rapport-date { font-size: 10px; color: #777; }

        .filters-banner { background: #f8f7f5; border: 1px solid #e0ddd5; border-radius: 4px; padding: 10px 14px; margin-bottom: 18px; font-size: 10.5px; display: flex; flex-wrap: wrap; gap: 6px 14px; align-items: center; }
        .filter-tag { display: inline-block; background: white; padding: 2px 10px; border-radius: 12px; border: 1px solid #ddd; font-size: 9.5px; }
        .filter-tag strong { color: #1a1916; }

        .synthese { display: flex; gap: 10px; margin-bottom: 22px; }
        .syn-card { flex: 1; border: 1px solid #ddd; border-radius: 4px; padding: 10px 8px; text-align: center; }
        .syn-val { font-size: 22px; font-weight: 700; display: block; line-height: 1.1; }
        .syn-label { font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: #666; margin-top: 3px; }
        .c-blue { color: #1d4ed8; } .c-pink { color: #be185d; }

        .section-title { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; border-bottom: 1.5px solid #000; padding-bottom: 4px; margin-bottom: 10px; margin-top: 22px; color: #222; }

        table.main-table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        table.main-table thead tr { background: #1a1916; color: white; }
        table.main-table th { padding: 5px 6px; font-size: 8.5px; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; text-align: left; }
        table.main-table td { padding: 4px 6px; border-bottom: 1px solid #eee; vertical-align: middle; }
        table.main-table tbody tr:nth-child(even) td { background: #fafafa; }
        .nom { font-weight: 700; }

        .doc-footer { position: absolute; bottom: 10mm; left: 18mm; right: 18mm; border-top: 1.5px solid #000; padding-top: 5px; text-align: center; font-size: 8px; font-weight: 700; }

        .no-print { max-width: 210mm; margin: 16px auto; display: flex; gap: 10px; justify-content: center; }
        .btn-print { padding: 10px 28px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: Arial, sans-serif; }
        .btn-back  { padding: 10px 22px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-family: Arial, sans-serif; }

        @media print {
            body { background: white; }
            .page { margin: 0; box-shadow: none; padding: 14mm 16mm 20mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Sauvegarder en PDF</button>
    <button class="btn-back" onclick="window.history.back()">⬅️ Retour</button>
</div>

<div class="page">

    <div class="lh">
        <img src="{{ $eveque_b64 ?? $logo_b64 ?? '' }}" alt="Évêque" class="lh-img-left">
        <div class="lh-center">
            <div class="lh-l1">ARCHIDIOCESE DE COTONOU</div>
            <div class="lh-l2">DIRECTION DIOCESAINE DE LA SANTE</div>
            <div class="lh-l3">{{ strtoupper($organisation ?? 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC') }}</div>
            <div class="lh-l4">C.S.V.H (ex : Hôpital Saint LUC)</div>
            <div class="lh-l5">Qtier Missèkplé Ste Rita - 01 BP 3603 | Tél : 66 43 44 78 – 90 07 49 67 | hopitalsaintluc@gmail.com | Cotonou – BENIN</div>
        </div>
        <img src="{{ $logo_b64 ?? '' }}" alt="Logo" class="lh-img-right">
    </div>

    <div class="rapport-header">
        <div class="rapport-title-box">
            <div class="rapport-title">🕒 Rapport historique du personnel</div>
        </div>
        <div class="rapport-filtre">Période : {{ $periode_label }}</div>
        <div class="rapport-date">📅 Généré le {{ $date_rapport }}</div>
    </div>

    <div class="filters-banner">
        <span style="font-weight:700;color:#1a1916;">🔍 Filtres :</span>
        <span class="filter-tag">Période : <strong>{{ $periode_label }}</strong></span>
        @if(!empty($filters['service']))
            <span class="filter-tag">Service : <strong>{{ $filters['service'] }}</strong></span>
        @endif
        @if(!empty($filters['type_contrat']))
            <span class="filter-tag">Contrat : <strong>{{ $filters['type_contrat'] }}</strong></span>
        @endif
        <span style="margin-left:auto;font-weight:700;color:#1a1916;">
            {{ $stats['total_personnes'] }} personne{{ $stats['total_personnes'] > 1 ? 's' : '' }} · {{ $stats['total_contrats'] }} contrat{{ $stats['total_contrats'] > 1 ? 's' : '' }}
        </span>
    </div>

    <div class="synthese">
        <div class="syn-card">
            <span class="syn-val">{{ $stats['total_personnes'] }}</span>
            <div class="syn-label">Personnes en poste</div>
        </div>
        <div class="syn-card">
            <span class="syn-val c-blue">{{ $stats['hommes'] }}</span>
            <div class="syn-label">Hommes</div>
        </div>
        <div class="syn-card">
            <span class="syn-val c-pink">{{ $stats['femmes'] }}</span>
            <div class="syn-label">Femmes</div>
        </div>
        <div class="syn-card">
            <span class="syn-val">{{ $stats['total_contrats'] }}</span>
            <div class="syn-label">Contrats actifs</div>
        </div>
    </div>

    <div class="section-title">Liste détaillée — {{ $periode_label }}</div>
    <table class="main-table">
        <thead>
            <tr>
                <th>#</th><th>Agent</th><th>Service</th><th>Fonction</th><th>Centre</th><th>Contrat</th><th>Période du contrat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contrats as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="nom">{{ $c->personnel?->nom_complet ?? '—' }}</td>
                <td>{{ $c->service ?: '—' }}</td>
                <td>{{ $c->fonction ?: '—' }}</td>
                <td>{{ $c->centre ?: '—' }}</td>
                <td>{{ $c->type_contrat }}</td>
                <td>Du {{ $c->date_debut->format('d/m/Y') }} {{ $c->date_fin ? 'au ' . $c->date_fin->format('d/m/Y') : '(en cours)' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="doc-footer">
        {{ strtoupper($organisation ?? '') }} — Rapport historique généré automatiquement le {{ $date_rapport }}
    </div>

</div>
</body>
</html>
