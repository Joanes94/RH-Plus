@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020';

    $appellation = $est_femme ? 'Mlle' : 'M.';
    if ($personnel->situation_matrimoniale === 'Marié(e)' && $est_femme) {
        $appellation = 'Mme';
    }
@endphp

@section('doc-date-top')
{{ $ville }}, le {{ $date_doc }}
@endsection

@section('doc-dest')
<div class="doc-dest-a">A</div>
<div class="doc-dest-name">{{ $appellation }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</div>
<div class="doc-dest-fn">
    @if($demande->niveau_etude) Étudiant{{ $est_femme ? 'e' : '' }} en {{ $demande->niveau_etude }} @endif
    @if($demande->specialite) — {{ $demande->specialite }} @endif
</div>
@endsection

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-object')
Autorisation de stage
@endsection

@section('doc-body')
<p class="indent-first">
    Suite à votre demande
    @if($demande->created_at) du {{ $demande->created_at->isoFormat('DD MMMM YYYY') }} @endif,
    nous vous autorisons à effectuer un <strong>stage professionnel non rémunéré</strong>
    @if($demande->etablissement_stage)
        au service {{ strtolower($demande->etablissement_stage) }}
    @elseif($personnel->service)
        au service {{ strtolower($personnel->service) }}
    @endif
    du Centre de Santé à Vocation Humanitaire (CSVH) Saint Luc.
</p>

@if($demande->date_debut && $demande->date_fin)
@php
    $jours = $demande->date_debut->diffInDays($demande->date_fin) + 1;
    $mois  = max(1, round($jours / 30));
@endphp
<p>
    Cette autorisation couvre la période du
    <strong>{{ $demande->date_debut->isoFormat('DD MMMM YYYY') }}</strong> au
    <strong>{{ $demande->date_fin->isoFormat('DD MMMM YYYY') }}</strong>,
    soit {{ $mois }} ({{ $mois < 10 ? '0'.$mois : $mois }}) mois.
</p>
@endif

<p>
    Durant votre séjour, vous devez vous conformer aux horaires de service et exécuter
    avec application toutes les tâches qui vous seront confiées.
</p>
@endsection


@section('doc-fait')
@endsection
