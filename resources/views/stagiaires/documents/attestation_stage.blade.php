@extends('stagiaires.documents._base')

@php
    $est_femme = $stagiaire->sexe === 'F';
    $civilite  = $est_femme ? 'Madame' : 'Monsieur';
    if ($stagiaire->sexe === 'F' && ($stagiaire->situation_matrimoniale ?? '') !== 'Marié(e)') {
        $civilite = 'Mme';
    }
    $il_elle = $est_femme ? 'Elle' : 'Il';

    $typeLabel = match($type_stage ?? 'professionnel') {
        'academique' => 'académique',
        'decouverte' => 'académique',
        default      => 'professionnel',
    };

    // Durée en lettres sans parenthèses numériques (ex: "trois mois", "deux semaines", "quinze jours")
    $chiffresEnLettres = [
        1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq',
        6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix',
        11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze', 15 => 'quinze',
        16 => 'seize', 17 => 'dix-sept', 18 => 'dix-huit', 19 => 'dix-neuf', 20 => 'vingt',
        21 => 'vingt-et-un', 22 => 'vingt-deux', 23 => 'vingt-trois', 24 => 'vingt-quatre',
        25 => 'vingt-cinq', 26 => 'vingt-six', 27 => 'vingt-sept', 28 => 'vingt-huit',
        29 => 'vingt-neuf', 30 => 'trente', 31 => 'trente-et-un',
    ];

    $duree = '';
    if ($stagiaire->date_debut_stage && $stagiaire->date_fin_stage) {
        $jours = (int)$stagiaire->date_debut_stage->diffInDays($stagiaire->date_fin_stage) + 1;
        if ($jours >= 28) {
            $mois = (int) round($jours / 30);
            $mois = max(1, $mois);
            $let  = $chiffresEnLettres[$mois] ?? (string) $mois;
            $duree = $let . ' mois';
        } elseif ($jours >= 7 && $jours % 7 === 0) {
            $sem = (int) ($jours / 7);
            $let = $sem === 1 ? 'une' : ($chiffresEnLettres[$sem] ?? (string) $sem);
            $duree = $let . ' semaine' . ($sem > 1 ? 's' : '');
        } else {
            $let   = $chiffresEnLettres[$jours] ?? (string) $jours;
            $duree = $let . ' jour' . ($jours > 1 ? 's' : '');
        }
    }
@endphp

@section('doc-content')

<div class="doc-ref">N/REF : {{ $reference }}</div>

<div class="doc-title-box">
    <h1>ATTESTATION DE STAGE</h1>
</div>

<div class="doc-body">
    <p class="indent doc-greeting">
        {{ $civilite }},
    </p>

    <p class="indent">
        Je soussign&eacute; <strong>{{ $drh_nom ?? 'Le Directeur des Ressources Humaines' }}</strong>,
        {{ $drh_titre ?? 'Directeur des Ressources Humaines' }} du {{ $centre->nom ?? $stagiaire->centre->nom ?? 'Centre de Sant&eacute; &agrave; Vocation Humanitaire Saint Luc' }}, atteste que
        <strong>{{ $civilite }} {{ strtoupper($stagiaire->nom) }} {{ $stagiaire->prenoms }}</strong>,
        a effectu&eacute; un stage {{ $typeLabel }}
        @if($duree) de <strong>{{ $duree }}</strong>@endif
        dans ledit centre.
        {{ $il_elle }} a servi notamment {{ $service_phrase }} du centre
        @if($stagiaire->date_debut_stage && $stagiaire->date_fin_stage)
            du <strong>{{ $stagiaire->date_debut_stage->isoFormat('DD MMMM YYYY') }}</strong>
            au <strong>{{ $stagiaire->date_fin_stage->isoFormat('DD MMMM YYYY') }}</strong> inclus.
        @endif
    </p>

    <p class="indent">
        Pendant cette p&eacute;riode, {{ strtolower($il_elle) }} a fait preuve de professionnalisme,
        d&apos;assiduit&eacute;, de d&eacute;vouement et de respect dans l&apos;ex&eacute;cution
        des t&acirc;ches qui lui ont &eacute;t&eacute; confi&eacute;es.
    </p>

    <p class="indent">
        En foi de quoi, la pr&eacute;sente attestation lui est d&eacute;livr&eacute;e pour servir
        et valoir ce que de droit.
    </p>
</div>

<p style="text-align:right; font-style:italic; font-size:12px; margin-top:20px; padding-right:10px;">
    Fait &agrave; {{ $ville ?? 'Cotonou' }}, le {{ $date_doc ?? now()->isoFormat('D MMMM YYYY') }}
</p>

@endsection