@extends('stagiaires.documents._base')

@php
    /*
     * Variables :
     * $type_stage   : 'professionnel' | 'academique' | 'decouverte'
     * $services_list: array de services — chaque item peut être :
     *   - string simple "MEDECINE" (service unique ou sans dates séparées)
     *   - array ['nom' => 'MEDECINE', 'debut' => '2026-01-26', 'fin' => '2026-02-25']
     * $stagiaire    : App\Models\Stagiaire
     * $remunere     : bool (false = non rémunéré)
     */
    $civilite = $stagiaire->sexe === 'F' ? 'Mme' : 'M.';
    $est_femme = $stagiaire->sexe === 'F';

    // Objet ligne 2
    $objetLigne2 = match($type_stage ?? 'professionnel') {
        'academique' => 'académique',
        'decouverte' => 'de découverte',
        default      => 'Professionnel' . (!($remunere ?? false) ? ' non rémunéré' : ''),
    };

    // Phrase intro
    $typeInPhrase = match($type_stage ?? 'professionnel') {
        'academique' => 'un stage académique',
        'decouverte' => 'un stage académique',
        default      => 'un stage professionnel' . (!($remunere ?? false) ? ' non rémunéré' : ''),
    };

    // Durée totale en toutes lettres (ex: "trois mois", "deux semaines", "quinze jours")
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
            $let = $chiffresEnLettres[$jours] ?? (string) $jours;
            $duree = $let . ' jour' . ($jours > 1 ? 's' : '');
        }
    }
@endphp

@section('doc-content')

<div class="doc-date-right">{{ $ville ?? 'Cotonou' }}, le {{ $date_doc ?? now()->isoFormat('D MMMM YYYY') }}</div>

<div class="doc-dest">
    <div class="dest-a">A</div>
    <div class="dest-name">{{ $civilite }} {{ strtoupper($stagiaire->nom) }} {{ $stagiaire->prenoms }}</div>
    @if($stagiaire->ecole_formation || $stagiaire->titre)
    <div class="dest-fn">{{ $stagiaire->titre ?: ($stagiaire->ecole_formation ? 'Étudiant(e)' : '') }}{{ $stagiaire->ecole_formation ? ' en ' . $stagiaire->ecole_formation : '' }}</div>
    @endif
</div>

<div class="doc-ref">N/REF : {{ $reference }}</div>

<p style="font-weight:700; font-size:13px; margin-bottom:2px">Objet : Autorisation de stage</p>
<p style="font-weight:700; font-size:13px; margin-bottom:16px">{{ $objetLigne2 }}</p>

<div class="doc-body">
    <p class="doc-greeting">{{ $civilite }},</p>

    @if($multi_service)
    {{-- PLUSIEURS SERVICES --}}
    <p class="indent">
        Suite &agrave; votre demande
        @if($stagiaire->created_at) du {{ $stagiaire->created_at->isoFormat('DD MMMM YYYY') }}@endif,
        nous vous autorisons &agrave; effectuer {{ $typeInPhrase }} au {{ $centre->nom ?? $stagiaire->centre->nom ?? 'Centre de Sant&eacute; &agrave; Vocation Humanitaire Saint Luc' }}.
    </p>

    <p style="margin-bottom: 6px;">Cette autorisation couvre la p&eacute;riode du</p>
    @foreach($service_segments as $i => $svc)
    <p style="margin-left: 24px; margin-bottom: 4px; font-weight: 700;">
        @if($svc['debut'] && $svc['fin'])
            {{ $svc['debut']->isoFormat('DD MMMM YYYY') }}
            au {{ $svc['fin']->isoFormat('DD MMMM YYYY') }}
        @elseif($stagiaire->date_debut_stage && $stagiaire->date_fin_stage)
            {{ $stagiaire->date_debut_stage->isoFormat('DD MMMM YYYY') }}
            au {{ $stagiaire->date_fin_stage->isoFormat('DD MMMM YYYY') }}
        @endif
        au service {{ $svc['article'] }} {{ $svc['label'] }}
        @if($i === count($service_segments) - 1)
            soit {{ $duree }}.
        @else
            ;
        @endif
    </p>
    @endforeach

    @else
    {{-- UN SEUL SERVICE --}}
<p class="indent">
    Suite &agrave; votre demande
    @if($stagiaire->created_at)
        du {{ $stagiaire->created_at->isoFormat('DD MMMM YYYY') }}
    @endif,
    nous vous autorisons &agrave; effectuer {{ $typeInPhrase }}
    {{ $service_phrase }}
    au {{ $centre->nom ?? $stagiaire->centre->nom ?? 'Centre de Sant&eacute; &agrave; Vocation Humanitaire Saint Luc' }}.
</p>

    @if($stagiaire->date_debut_stage && $stagiaire->date_fin_stage)
    <p class="indent">
        Cette autorisation couvre la p&eacute;riode du
        <strong>{{ $stagiaire->date_debut_stage->isoFormat('DD MMMM YYYY') }}</strong>
        au <strong>{{ $stagiaire->date_fin_stage->isoFormat('DD MMMM YYYY') }}</strong>,
        @if($duree) soit <strong>{{ $duree }}</strong>.@endif
    </p>
    @endif
    @endif

    <p class="indent">
        Durant votre s&eacute;jour, vous devez vous conformer aux horaires de service
        et ex&eacute;cuter avec application toutes les t&acirc;ches qui vous seront confi&eacute;es.
    </p>
</div>

@endsection