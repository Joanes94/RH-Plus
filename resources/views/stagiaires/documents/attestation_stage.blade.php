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

    // Durée en lettres
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
            $let   = match((int)$jours) { 1=>'un',2=>'deux',3=>'trois',4=>'quatre',5=>'cinq',6=>'six',7=>'sept',8=>'huit',9=>'neuf',10=>'dix',default=>(string)$jours };
            $duree = $let . ' (' . str_pad($jours,2,'0',STR_PAD_LEFT) . ') jours';
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
        Directeur des Ressources Humaines du Centre de Sant&eacute; &agrave; Vocation Humanitaire
        Saint Luc de Cotonou, atteste que
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