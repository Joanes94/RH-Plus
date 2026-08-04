@php
    use Carbon\Carbon;
    $civilite  = $personnel->sexe === 'M' ? 'Monsieur' : 'Madame';
    $fmtDate   = fn ($d) => $d ? ucfirst(Carbon::parse($d)->locale('fr')->isoFormat('D MMMM YYYY')) : '…………';
    $fmtArgent = fn ($m) => $m ? number_format((float) $m, 0, ',', ' ') : '…………';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accord de bonification (centre) — {{ $personnel->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; background: #f5f4f0; color: #000; font-size: 13px; }
        .doc-page { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; padding: 16mm 20mm; box-shadow: 0 4px 24px rgba(0,0,0,0.12); position: relative; }
        .letterhead { text-align: center; padding-bottom: 8px; border-bottom: 2.5px solid #000; margin-bottom: 24px; }
        .lh-line1 { font-size: 1.05rem; font-weight: 700; }
        .lh-line2 { font-size: .82rem; margin-top: 4px; }
        .doc-date { text-align: right; margin-bottom: 6px; }
        .doc-intro { margin-bottom: 18px; }
        .doc-body { font-size: .95rem; line-height: 1.7; text-align: justify; }
        .doc-body p { margin-bottom: 12px; }
        .dest { margin: 18px 0; }
        .dest .qui { font-weight: 700; }
        .ref { font-weight: 700; margin-bottom: 4px; }
        .objet { font-weight: 700; margin-bottom: 18px; }
        .pj { margin-top: 20px; font-size: .88rem; }
        .pj .titre-pj { text-decoration: underline; }
        .signature { margin-top: 30px; text-align: right; }
        .signature .titre { font-weight: 700; }
        .signature .nom { font-weight: 700; margin-top: 40px; }
        .no-print { margin: 20px auto; max-width: 210mm; display: flex; gap: 10px; justify-content: center; }
        .btn-print { padding: 10px 24px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: Arial, sans-serif; }
        .btn-alt   { padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: Arial, sans-serif; text-decoration: none; }
        .btn-close { padding: 10px 20px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: .875rem; cursor: pointer; font-family: Arial, sans-serif; }
        @media print { body { background: white; } .doc-page { margin: 0; box-shadow: none; } .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / Sauvegarder en PDF</button>
    <a class="btn-alt" href="{{ route('avancements.document', $avancement) }}">Voir la lettre à l'employé</a>
    <button class="btn-close" onclick="window.close()">Fermer</button>
</div>

<div class="doc-page">
    <div class="letterhead">
        <div class="lh-line1">{{ strtoupper($organisation) }}</div>
        <div class="lh-line2">Direction Diocésaine de la Santé (DDIS)</div>
    </div>

    <div class="doc-date">{{ $ville }}, le {{ $fmtDate(now()) }}</div>
    <div class="doc-intro">Le Directeur Diocésain de la Santé</div>

    <div class="dest">
        Au<br>
        <span class="qui">Révérend Père Directeur du {{ $contrat->centre ?? '—' }}</span><br>
        {{ strtoupper($ville) }}
    </div>

    <div class="ref">N/RÉF : {{ $avancement->numero_reference }}</div>
    <div class="objet">
        Objet : Accord de bonification<br>
        à {{ $civilite }} {{ $personnel->prenoms }} {{ strtoupper($personnel->nom) }}
    </div>

    <div class="doc-body">
        <p>Révérend Père Directeur,</p>
        <p>Conformément à l'Accord d'Établissement applicable aux personnels des Institutions Sanitaires Diocésaines de Cotonou du 11 Décembre 2019, en son article 88, {{ $civilite }} {{ $personnel->prenoms }} {{ strtoupper($personnel->nom) }}, bénéficie d'un coefficient de {{ number_format((float) $avancement->coefficient_applique, 1) }} du salaire de sa catégorie à 24 mois de sa retraite.</p>
        <p>Donc à 24 mois de sa retraite, son salaire de base passe de {{ $fmtArgent($avancement->ancien_salaire) }} FCFA, à {{ $fmtArgent($avancement->nouveau_salaire) }} FCFA.</p>
        <p>Vous trouverez ci-joint, une lettre lui notifiant cet accord.</p>
        <p>Veuillez recevoir, Révérend Père Directeur, nos meilleures salutations.</p>
    </div>

    <div class="pj">
        <div class="titre-pj">Pièce jointe :</div>
        <div>Accord de bonification 01</div>
    </div>

    <div class="signature">
        <div class="titre">Le Directeur Diocésain,</div>
        <div class="nom">{{ $directeur_diocesain_nom }}</div>
    </div>
</div>
</body>
</html>
