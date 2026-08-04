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

    // Durée totale
    $duree = '';
    if ($stagiaire->date_debut_stage && $stagiaire->date_fin_stage) {
        $jours = (int)$stagiaire->date_debut_stage->diffInDays($stagiaire->date_fin_stage) + 1;
        if ($jours >= 30) {
            $mois = round($jours / 30);
            $let  = match((int)$mois) { 1=>'un',2=>'deux',3=>'trois',4=>'quatre',5=>'cinq',6=>'six',default=>(string)$mois };
            $duree = $let . ' (' . str_pad($mois,2,'0',STR_PAD_LEFT) . ') mois';
        } elseif ($jours >= 7 && $jours % 7 === 0) {
            $sem = $jours / 7;
            $let = match((int)$sem) { 1=>'une',2=>'deux',3=>'trois',4=>'quatre',default=>(string)$sem };
            $duree = $let . ' (' . str_pad($sem,2,'0',STR_PAD_LEFT) . ') semaine' . ($sem > 1 ? 's' : '');
        } else {
            $duree = $jours . ' (' . str_pad($jours,2,'0',STR_PAD_LEFT) . ') jours';
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
        nous vous autorisons &agrave; effectuer {{ $typeInPhrase }} au Centre de Sant&eacute;
        &agrave; Vocation Humanitaire (CSVH) Saint Luc.
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
            soit {{ $duree }} (En mois, semaines et jours selon la p&eacute;riode).
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
    du Centre de Sant&eacute; &agrave; Vocation Humanitaire (CSVH) Saint Luc.
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