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
    <title>Accord de bonification — {{ $personnel->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; background: #f5f4f0; color: #000; font-size: 13px; }
        .doc-page { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; padding: 14mm 18mm 20mm; box-shadow: 0 4px 24px rgba(0,0,0,0.12); position: relative; overflow: hidden; }
        .letterhead { text-align: center; padding-bottom: 8px; border-bottom: 2.5px solid #000; margin-bottom: 24px; }
        .lh-line1 { font-size: 1.05rem; font-weight: 700; }
        .lh-line2 { font-size: .82rem; margin-top: 4px; }
        .doc-date { text-align: right; margin-bottom: 12px; font-style: italic; }
        .doc-intro { margin-bottom: 18px; font-weight: 700; }
        .doc-body { font-size: 1rem; line-height: 1.8; text-align: justify; }
        .doc-body p { margin-bottom: 14px; }
        .dest { width: 280px; margin-left: auto; text-align: left; margin: 18px 0 22px; font-size: 0.95rem; }
        .dest .qui { font-weight: 700; }
        .ref { font-weight: 700; font-style: italic; margin-bottom: 6px; }
        .objet { font-weight: 700; margin-bottom: 22px; }
        .signature { margin-top: 35px; width: 300px; margin-left: auto; text-align: center; }
        .signature .titre { font-weight: 700; margin-bottom: 4px; font-size: 0.95rem; }
        .sig-image-wrap { text-align: center; margin: 6px auto; line-height: 0; }
        .sig-image { height: 100px; width: auto; max-width: 280px; object-fit: contain; display: block; margin: 0 auto; }
        .sig-line { height: 60px; width: 220px; border-bottom: 1.5px solid #000; margin: 0 auto; }
        .signature .nom { font-weight: 700; text-decoration: underline; margin-top: 4px; font-size: 0.95rem; }
        .no-print { margin: 20px auto; max-width: 210mm; display: flex; gap: 10px; justify-content: center; }
        .btn-print { padding: 10px 24px; background: #1a5c45; color: white; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: Arial, sans-serif; }
        .btn-alt   { padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: Arial, sans-serif; text-decoration: none; }
        .btn-close { padding: 10px 20px; background: #e8e6e0; color: #1a1916; border: none; border-radius: 8px; font-size: .875rem; cursor: pointer; font-family: Arial, sans-serif; }
        @media print { body { background: white; } .doc-page { margin: 0; box-shadow: none; width: 100%; } .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimer / Sauvegarder en PDF</button>
    <a class="btn-alt" href="{{ route('avancements.document', $avancement) }}?doc=directeur">Voir la lettre au Directeur du centre</a>
    <button class="btn-close" onclick="window.close()">Fermer</button>
</div>

<div class="doc-page">
    @include('partials._letterhead', ['centre' => $centre])

    <div class="doc-date" style="margin-top: 25px;">{{ $ville }}, le {{ $fmtDate($avancement->date_effet ?? now()) }}</div>
    <div class="doc-intro">Le Directeur Diocésain de la Santé</div>

    <div class="dest">
        A<br>
        <span class="qui">{{ $civilite }} {{ $personnel->prenoms }} {{ strtoupper($personnel->nom) }}</span><br>
        Employé{{ $personnel->sexe === 'F' ? 'e' : '' }} au {{ $centre->nom ?? 'Centre de Santé' }}
    </div>

    <div class="ref">N/RÉF : {{ $avancement->numero_reference }}</div>
    <div class="objet">Objet : Accord de bonification</div>

    <div class="doc-body" style="margin-top: 20px;">
        <p>{{ $civilite }},</p>
        <p>Nous vous informons par la présente que la Direction Diocésaine de la Santé (DDIS), après étude de votre dossier et en application de l'article 88 de l'Accord d'Établissement applicable aux personnels des Institutions Sanitaires Diocésaines de Cotonou du 11 Décembre 2019, vous avance en échelon.</p>
        <p>A cet effet, pour compter du <strong>{{ $fmtDate($avancement->date_effet) }}</strong>, vous bénéficiez d'un coefficient de <strong>{{ number_format((float) $avancement->coefficient_applique, 1) }}</strong>.</p>
        <p>Votre salaire de base devient donc <strong>{{ $fmtArgent($avancement->nouveau_salaire) }} FCFA</strong>.</p>
        <p>Tout en vous adressant nos félicitations, Recevez, {{ $civilite }}, nos meilleures salutations.</p>
    </div>

    <div class="signature" style="margin-top: 40px;">
        <div class="titre">Pour la Direction Diocésaine de la Santé (DDIS),</div>
        <div class="sous-titre" style="font-size:0.85rem; font-weight:600; color:#4b5563;">{{ $signataire_titre ?? 'Le Directeur Diocésain de la Santé' }}</div>
        @if($avancement->statut === 'valide')
            <div class="sig-image-wrap" style="margin-top:10px;">
                @if(!empty($signature_url ?? $signature_directeur_url))
                    <img src="{{ $signature_url ?? $signature_directeur_url }}" alt="Signature DDIS" class="sig-image">
                @else
                    <div class="sig-line"></div>
                @endif
            </div>
            <div class="nom" style="font-weight:700; text-decoration:underline; margin-top:5px;">{{ $signataire_nom ?? $directeur_diocesain_nom ?? 'La DDIS' }}</div>
        @else
            <div style="margin-top: 20px; border: 2px dashed #d97706; background: #fffbe6; color: #b45309; padding: 12px; font-weight: 700; text-align: center; border-radius: 8px;">
                ⚠️ Document préalable sans signature — En attente de validation officielle par la DDIS
            </div>
        @endif
    </div>
</div>
</body>
</html>