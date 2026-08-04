<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Personnel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #1a1916; background: #ddd; }

        .page {
            width: 210mm; min-height: 297mm;
            margin: 24px auto; background: white;
            padding: 16mm 18mm 22mm;
            box-shadow: 0 4px 32px rgba(0,0,0,0.15);
            position: relative;
        }

        /* LETTERHEAD */
        .lh { display: flex; align-items: center; gap: 16px; padding-bottom: 12px; border-bottom: 2px solid #000; margin-bottom: 20px; }
        .lh-img-left  { width: 64px; height: 64px; object-fit: cover; flex-shrink: 0; }
        .lh-img-right { width: 68px; height: 68px; object-fit: contain; flex-shrink: 0; }
        .lh-center { flex: 1; text-align: center; line-height: 1.5; }
        .lh-l1 { font-size: 11px; font-weight: 700; letter-spacing: .04em; }
        .lh-l2 { font-size: 10px; }
        .lh-l3 { font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 2px 0; }
        .lh-l4 { font-size: 11px; font-weight: 700; }
        .lh-l5 { font-size: 9px; color: #555; margin-top: 3px; }

        /* TITRE */
        .rapport-header { text-align: center; margin-bottom: 22px; }
        .rapport-title-box { display: inline-block; border: 2px solid #000; padding: 6px 32px; margin-bottom: 6px; }
        .rapport-title { font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
        .rapport-filtre { font-size: 11px; color: #555; font-style: italic; margin-bottom: 2px; }
        .rapport-date { font-size: 10px; color: #777; }

        /* BANDEAU FILTRES */
        .filters-banner {
            background: #f8f7f5;
            border: 1px solid #e0ddd5;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 10.5px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px 14px;
        }
        .filter-tag {
            display: inline-block;
            background: white;
            padding: 2px 10px;
            border-radius: 12px;
            border: 1px solid #ddd;
            font-size: 9.5px;
        }
        .filter-tag strong { color: #1a1916; }

        /* SYNTHÈSE */
        .synthese { display: flex; gap: 10px; margin-bottom: 22px; }
        .syn-card { flex: 1; border: 1px solid #ddd; border-radius: 4px; padding: 10px 8px; text-align: center; }
        .syn-val { font-size: 22px; font-weight: 700; display: block; line-height: 1.1; }
        .syn-label { font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: #666; margin-top: 3px; }
        .syn-sub { font-size: 9px; color: #999; margin-top: 2px; }
        .c-blue  { color: #1d4ed8; }
        .c-pink  { color: #be185d; }
        .c-green { color: #059669; }
        .c-amber { color: #d97706; }
        .hf-bar { height: 4px; border-radius: 2px; overflow: hidden; margin-top: 6px; background: #eee; display: flex; }
        .hf-bar-h { background: #1d4ed8; }
        .hf-bar-f { background: #be185d; }

        /* SECTION TITLE */
        .section-title {
            font-size: 9.5px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .07em; border-bottom: 1.5px solid #000;
            padding-bottom: 4px; margin-bottom: 10px; margin-top: 22px; color: #222;
        }

        /* TABLEAU PRINCIPAL */
        table.main-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.main-table thead tr { background: #1a1916; color: white; }
        table.main-table th { padding: 5px 6px; font-size: 8.5px; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; text-align: left; }
        table.main-table td { padding: 4px 6px; border-bottom: 1px solid #eee; vertical-align: middle; }
        table.main-table tbody tr:nth-child(even) td { background: #fafafa; }
        .num { color: #bbb; font-size: 8px; text-align: center; }
        .nom { font-weight: 700; }
        .badge-h { display: inline-block; padding: 1px 6px; background: #dbeafe; color: #1d4ed8; border-radius: 3px; font-size: 8px; font-weight: 700; }
        .badge-f { display: inline-block; padding: 1px 6px; background: #fce7f3; color: #be185d; border-radius: 3px; font-size: 8px; font-weight: 700; }
        .s-actif    { color: #059669; font-weight: 600; }
        .s-inactif  { color: #dc2626; font-weight: 600; }
        .s-en_conge { color: #d97706; font-weight: 600; }
        .s-retraite { color: #7c3aed; font-weight: 600; }

        /* TABLEAUX RÉPARTITION */
        .rep-cols { display: flex; gap: 20px; }
        .rep-col  { flex: 1; }
        .rep-sub-title { font-size: 10px; font-weight: 700; margin-bottom: 6px; color: #555; }
        table.rep-table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        table.rep-table th { background: #f0f0ee; padding: 4px 6px; border: 1px solid #ccc; font-size: 8px; text-transform: uppercase; font-weight: 700; text-align: left; }
        table.rep-table th.tc { text-align: center; }
        table.rep-table td { padding: 4px 6px; border: 1px solid #e8e8e8; }
        table.rep-table td.tc { text-align: center; }
        table.rep-table td.th { text-align: center; color: #1d4ed8; font-weight: 600; }
        table.rep-table td.tf { text-align: center; color: #be185d; font-weight: 600; }
        table.rep-table td.pct { text-align: center; color: #999; font-size: 8px; }
        table.rep-table tr.total-row td { background: #f0f0ee; font-weight: 700; border-top: 1.5px solid #999; }

        /* SIGNATURE */
        .signature-block { margin-top: 28px; text-align: right; padding-right: 10px; }
        .sig-lieu  { font-style: italic; font-size: 11px; margin-bottom: 6px; }
        .sig-titre { font-weight: 700; font-size: 11px; }
        .sig-line  { width: 180px; border-bottom: 1px solid #000; margin: 28px 0 4px auto; }
        .sig-nom   { font-weight: 700; text-decoration: underline; font-size: 11px; }

        /* FOOTER */
        .doc-footer { position: absolute; bottom: 10mm; left: 18mm; right: 18mm; border-top: 1.5px solid #000; padding-top: 5px; text-align: center; font-size: 8px; font-weight: 700; }
        .ft2 { display: flex; justify-content: center; gap: 30px; margin-top: 2px; }

        /* NO-PRINT */
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

    {{-- LETTERHEAD --}}
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

    {{-- TITRE --}}
    <div class="rapport-header">
        <div class="rapport-title-box">
            <div class="rapport-title">📊 Rapport du Personnel</div>
        </div>
        <div class="rapport-filtre">{{ $titreFiltre }}</div>
        <div class="rapport-date">📅 Généré le {{ $date_rapport }}</div>
    </div>

    {{-- BANDEAU DES FILTRES APPLIQUÉS --}}
    <div class="filters-banner">
        <span style="font-weight:700;color:#1a1916;">🔍 Filtres :</span>
        @if(empty(array_filter($filters)))
            <span class="filter-tag"><strong>Tous les agents</strong></span>
        @else
            @if(!empty($filters['service']))
                <span class="filter-tag">Service : <strong>{{ $filters['service'] }}</strong></span>
            @endif
            @if(!empty($filters['corporation']))
                <span class="filter-tag">Corporation : <strong>{{ $filters['corporation'] }}</strong></span>
            @endif
            @if(!empty($filters['sexe']))
                <span class="filter-tag">Sexe : <strong>{{ $filters['sexe'] === 'M' ? 'Hommes' : 'Femmes' }}</strong></span>
            @endif
            @if(!empty($filters['type_contrat']))
                <span class="filter-tag">Contrat : <strong>{{ $filters['type_contrat'] }}</strong></span>
            @endif
            @if(!empty($filters['statut']))
                <span class="filter-tag">Statut : <strong>{{ ucfirst(str_replace('_', ' ', $filters['statut'])) }}</strong></span>
            @endif
        @endif
        <span style="margin-left:auto;font-weight:700;color:#1a1916;">
            Total : {{ $stats['total'] }} agent{{ $stats['total'] > 1 ? 's' : '' }}
        </span>
    </div>

    {{-- SYNTHÈSE --}}
    <div class="synthese">
        <div class="syn-card">
            <span class="syn-val">{{ $stats['total'] }}</span>
            <div class="syn-label">Total agents</div>
            <div class="hf-bar">
                @if($stats['total'] > 0)
                <div class="hf-bar-h" style="width:{{ round($stats['hommes'] / $stats['total'] * 100) }}%"></div>
                <div class="hf-bar-f" style="width:{{ round($stats['femmes'] / $stats['total'] * 100) }}%"></div>
                @endif
            </div>
        </div>
        <div class="syn-card">
            <span class="syn-val c-blue">👨 {{ $stats['hommes'] }}</span>
            <div class="syn-label">Hommes</div>
            @if($stats['total'] > 0)
            <div class="syn-sub">{{ round($stats['hommes'] / $stats['total'] * 100) }}%</div>
            @endif
        </div>
        <div class="syn-card">
            <span class="syn-val c-pink">👩 {{ $stats['femmes'] }}</span>
            <div class="syn-label">Femmes</div>
            @if($stats['total'] > 0)
            <div class="syn-sub">{{ round($stats['femmes'] / $stats['total'] * 100) }}%</div>
            @endif
        </div>
        <div class="syn-card">
            <span class="syn-val c-green">✅ {{ $stats['actifs'] }}</span>
            <div class="syn-label">Actifs</div>
            <div class="syn-sub">{{ $stats['en_conge'] }} en congé</div>
        </div>
        <div class="syn-card">
            <span class="syn-val">📄 {{ $stats['cdi'] }}</span>
            <div class="syn-label">CDI</div>
            <div class="syn-sub">{{ $stats['cdd'] }} CDD</div>
        </div>
    </div>

    {{-- LISTE DU PERSONNEL --}}
    <div class="section-title">👥 Liste du personnel ({{ $stats['total'] }} agent{{ $stats['total'] > 1 ? 's' : '' }})</div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width:18px">#</th>
                <th>Nom et Prénoms</th>
                <th style="width:32px;text-align:center">Sexe</th>
                <th>Service</th>
                <th>Corporation / Fonction</th>
                <th>Contrat</th>
                <th style="width:70px">Embauche</th>
                <th style="width:60px">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personnels as $i => $p)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td><span class="nom">{{ strtoupper($p->nom) }}</span> {{ $p->prenoms }}</td>
                <td style="text-align:center">
                    @if($p->sexe === 'F')
                        <span class="badge-f">F</span>
                    @else
                        <span class="badge-h">H</span>
                    @endif
                </td>
                <td>{{ $p->service ?: '—' }}</td>
                <td>{{ $p->corporation ?: '—' }}</td>
                <td>{{ $p->type_contrat_actuel ?: '—' }}</td>
                <td style="white-space:nowrap;font-size:9px">{{ $p->date_embauche_centre ? $p->date_embauche_centre->format('d/m/Y') : '—' }}</td>
                <td class="s-{{ $p->statut }}">{{ $p->statut_label }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- RÉPARTITIONS --}}
    @if($parService->count() > 0 || $parCorp->count() > 0)
    <div class="section-title">📊 Répartitions</div>
    <div class="rep-cols">

        @if($parService->count() > 0)
        <div class="rep-col">
            <div class="rep-sub-title">Par service</div>
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th class="tc">Total</th>
                        <th class="tc">👨 H</th>
                        <th class="tc">👩 F</th>
                        <th class="tc">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parService as $svc => $d)
                    <tr>
                        <td>{{ $svc ?: '—' }}</td>
                        <td class="tc">{{ $d['total'] }}</td>
                        <td class="th">{{ $d['hommes'] }}</td>
                        <td class="tf">{{ $d['femmes'] }}</td>
                        <td class="pct">@if($stats['total'] > 0){{ round($d['total'] / $stats['total'] * 100) }}@else 0 @endif%</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td class="tc"><strong>{{ $stats['total'] }}</strong></td>
                        <td class="th"><strong>{{ $stats['hommes'] }}</strong></td>
                        <td class="tf"><strong>{{ $stats['femmes'] }}</strong></td>
                        <td class="pct">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        @if($parCorp->count() > 0)
        <div class="rep-col">
            <div class="rep-sub-title">Par corporation</div>
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>Corporation</th>
                        <th class="tc">Total</th>
                        <th class="tc">👨 H</th>
                        <th class="tc">👩 F</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parCorp->take(15) as $corp => $d)
                    <tr>
                        <td>{{ $corp ?: '—' }}</td>
                        <td class="tc">{{ $d['total'] }}</td>
                        <td class="th">{{ $d['hommes'] }}</td>
                        <td class="tf">{{ $d['femmes'] }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td class="tc"><strong>{{ $stats['total'] }}</strong></td>
                        <td class="th"><strong>{{ $stats['hommes'] }}</strong></td>
                        <td class="tf"><strong>{{ $stats['femmes'] }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

    </div>
    @endif

    {{-- SIGNATURE --}}
    @if($drh_nom)
    <div class="signature-block">
        <div class="sig-lieu">{{ $ville ?? 'Cotonou' }}, le {{ $date_rapport }}</div>
        <div class="sig-titre">Directeur des Ressources Humaines</div>
        <div class="sig-line"></div>
        <div class="sig-nom">{{ $drh_nom }}</div>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="doc-footer">
        <div>NOUVELLE AUTORISATION MINISTERIELLE N°071/MS/DC/SGM/CJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020</div>
        <div class="ft2">
            <span>N°INSAE : 2988511276715</span>
            <span>N° IFU 3200800472415</span>
        </div>
    </div>

</div>
</body>
</html>