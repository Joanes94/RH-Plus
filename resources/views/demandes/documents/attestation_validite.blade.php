@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020';
@endphp

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-title')
ATTESTATION DE VALIDITE DE TRAVAIL
@endsection

@section('doc-body')
<p class="indent-first">
    @if(!empty($approuvePar) && $approuvePar->isCRH())
        Je soussigné <strong>{{ $approuvePar->nom_complet }}</strong>, {{ $approuvePar->titre_effectif ?: 'Conseiller aux Ressources Humaines' }} des Institutions Sanitaires Diocésaines,
        atteste que <strong>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
        du <strong>{{ $centre->nom ?? 'Centre' }}</strong>,
    @else
        Je soussigné <strong>{{ $drh_nom }}</strong>, {{ $drh_titre ?? 'Directeur des Ressources Humaines' }}
        du {{ $centre->nom ?? 'Centre' }}, atteste que
        <strong>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
    @endif
    né{{ $est_femme ? 'e' : '' }} le
    {{ $personnel->date_naissance ? $personnel->date_naissance->isoFormat('DD MMMM YYYY') : '____________' }},
    est embauché{{ $est_femme ? 'e' : '' }} le
    {{ $personnel->date_embauche_centre ? $personnel->date_embauche_centre->isoFormat('DD MMMM YYYY') : '____________' }}</strong>.
</p>

@if($personnel->date_depart_retraite)
<p>
    <strong>La date présumée de son départ à la retraite est le
    {{ $personnel->date_depart_retraite->isoFormat('DD MMMM YYYY') }} sauf licenciement.</strong>
</p>
@endif

<p class="indent-first">
    La présente attestation est délivrée pour servir et valoir ce que de droit.
</p>
@endsection
