@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020';
@endphp

@section('doc-date-top')
{{ $ville }}, le {{ $date_doc }}
@endsection

@section('doc-dest')
<div class="doc-dest-a">A</div>
<div class="doc-dest-name">{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</div>
<div class="doc-dest-fn">{{ $personnel->corporation ?: '' }}</div>
@endsection

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-object')
Demande d'explication
@endsection

@section('doc-body')
<p class="doc-greeting">{{ $civilite }},</p>

<p class="indent-first">
    Il a été porté à notre connaissance des faits qui nécessitent des éclaircissements
    de votre part. En conséquence, nous vous demandons de bien vouloir nous fournir,
    dans un délai de <strong>72 heures</strong> à compter de la réception de la présente,
    des explications écrites et circonstanciées sur les faits suivants :
</p>

@if($demande->faits_reproches)
<p style="white-space:pre-line;border-left:3px solid #000;padding-left:14px;font-style:italic">
    {{ $demande->faits_reproches }}
</p>
@endif

@if($demande->date_faits)
<p>Ces faits se sont produits le <strong>{{ $demande->date_faits->isoFormat('DD MMMM YYYY') }}</strong>.</p>
@endif

<p>
    Votre réponse devra nous parvenir par écrit, en main propre ou par tout autre moyen
    permettant d'en accuser réception, avant l'expiration du délai imparti.
</p>

<p>
    À défaut de réponse dans le délai indiqué, nous serons contraints de prendre toutes
    mesures que nous jugerons nécessaires.
</p>

<p>Nous vous prions d'agréer, {{ $civilite }}, l'expression de notre considération.</p>
@endsection


@section('doc-fait')
@endsection
