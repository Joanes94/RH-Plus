@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020';
@endphp

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-title')
{{ strtoupper($demande->type_label) }}
@endsection

@section('doc-body')
<p class="indent-first">
    @if(!empty($is_crh_autonome) && $is_crh_autonome)
        Je soussigné <strong>{{ $approuvePar->nom_complet }}</strong>, {{ $approuvePar->titre_effectif ?: 'Conseiller aux Ressources Humaines' }} des Institutions Sanitaires Diocésaines,
        atteste ce qui suit concernant {{ $civilite }} <strong>{{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
        du <strong>{{ $centre->nom ?? 'Centre' }}</strong>,
    @else
        Je soussigné <strong>{{ $drh_nom }}</strong>, {{ $drh_titre ?? 'Directeur des Ressources Humaines' }}
        du {{ $centre->nom ?? 'Centre' }}, atteste ce qui suit
        concernant {{ $civilite }} <strong>{{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
    @endif

    {{ $personnel->corporation ?: '—' }}
    @if($personnel->service) au service {{ strtolower($personnel->service) }} @endif.
</p>

@if($demande->date_debut)
<p>Date de début : <strong>{{ $demande->date_debut->isoFormat('DD MMMM YYYY') }}</strong></p>
@endif
@if($demande->date_fin)
<p>Date de fin : <strong>{{ $demande->date_fin->isoFormat('DD MMMM YYYY') }}</strong></p>
@endif
@if($demande->motif)
<p>Motif : {{ $demande->motif }}</p>
@endif
@if($demande->observations)
<p>{{ $demande->observations }}</p>
@endif

<p class="indent-first">
    La présente est délivrée à {{ $du_de_la }} intéressé{{ $est_femme ? 'e' : '' }}
    pour servir et valoir ce que de droit.
</p>
@endsection
