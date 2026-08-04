@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('Y').'/AC/VECI/DDIS/CRH/SA';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020';
    $drh_titre = 'Le Conseiller aux Ressources Humaines';

    $dureeJours = (int) ($demande->nb_jours ?? 0);
    if ($dureeJours <= 0 && $demande->date_debut && $demande->date_fin) {
        $dureeJours = $demande->date_debut->diffInDays($demande->date_fin) + 1;
    }

    $nombreEnLettres = [
        1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq', 6 => 'six', 7 => 'sept',
        8 => 'huit', 9 => 'neuf', 10 => 'dix', 11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze',
        15 => 'quinze', 16 => 'seize', 17 => 'dix-sept', 18 => 'dix-huit', 19 => 'dix-neuf', 20 => 'vingt',
        21 => 'vingt et un', 22 => 'vingt-deux', 23 => 'vingt-trois', 24 => 'vingt-quatre', 25 => 'vingt-cinq',
        26 => 'vingt-six', 27 => 'vingt-sept', 28 => 'vingt-huit', 29 => 'vingt-neuf', 30 => 'trente', 31 => 'trente et un',
    ];

    $dureeTexte = $nombreEnLettres[$dureeJours] ?? (string) $dureeJours;
@endphp

@section('doc-date-top')
{{ $ville }}, le {{ $date_doc }}
@endsection

@section('doc-dest')
<div class="doc-dest-a">A</div>
<div class="doc-dest-name">{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</div>
<div class="doc-dest-fn">Employé{{ $est_femme ? 'e' : '' }} au CSVH Saint Luc</div>
<div class="doc-dest-fn">{{ $ville }}</div>
@endsection

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-object')
Congé maladie
@endsection

@section('doc-body')
<p class="doc-greeting">{{ $civilite }},</p>

<p class="indent-first">
    Suite à votre demande @if($demande->created_at)du <strong>{{ $demande->created_at->isoFormat('DD MMMM YYYY') }}</strong>,@endif
    vous êtes autorisé{{ $est_femme ? 'e' : '' }} à prendre un congé maladie
    @if($demande->date_debut)
        à compter du <strong>{{ $demande->date_debut->isoFormat('DD MMMM YYYY') }}</strong>
    @endif
    @if($dureeJours > 0)
        pour une durée de <strong>{{ $dureeTexte }} ({{ $dureeJours }}) jours</strong>
    @endif.
</p>

<p>
    Veuillez agréer, {{ $civilite }}, l’expression de nos salutations distinguées.
</p>
@endsection

@push('doc-styles')
<style>
    .doc-signature-block .sig-title {
        text-transform: none;
        letter-spacing: 0;
    }
</style>
@endpush
