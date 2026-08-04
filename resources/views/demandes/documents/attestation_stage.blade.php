@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'NOUVELLE AUTORISATION MINISTERIELLE N°071/MS/DC/SGM/CJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020';

    // ── Durée du stage ────────────────────────────────────────────
    $dureeTexte = '';
    if ($demande->date_debut && $demande->date_fin) {
        $jours = (int)$demande->date_debut->diffInDays($demande->date_fin) + 1;

        if ($jours >= 30) {
            // En mois
            $mois       = round($jours / 30);
            $moisLettre = match((int)$mois) {
                1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre',
                5 => 'cinq', 6 => 'six', default => (string)$mois,
            };
            $padded     = str_pad($mois, 2, '0', STR_PAD_LEFT);
            $dureeTexte = $moisLettre . ' (' . $padded . ') mois';
        } elseif ($jours >= 7 && $jours % 7 === 0) {
            // En semaines
            $sem        = $jours / 7;
            $semLettre  = match((int)$sem) {
                1 => 'une', 2 => 'deux', 3 => 'trois', 4 => 'quatre',
                default => (string)$sem,
            };
            $padded     = str_pad($sem, 2, '0', STR_PAD_LEFT);
            $dureeTexte = $semLettre . ' (' . $padded . ') semaine' . ($sem > 1 ? 's' : '');
        } else {
            // En jours
            $joursLettre = match((int)$jours) {
                1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq',
                6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix',
                default => (string)$jours,
            };
            $padded      = str_pad($jours, 2, '0', STR_PAD_LEFT);
            $dureeTexte  = $joursLettre . ' (' . $padded . ') jour' . ($jours > 1 ? 's' : '');
        }
    }

    // ── Accord de genre strict ────────────────────────────────────
    // Il / Elle (sans "(elle)" — on choisit clairement selon le sexe)
    $il_elle  = $est_femme ? 'Elle' : 'Il';
    $son_sa   = $est_femme ? 'sa' : 'son';

    // ── Service d'accueil (établissement_stage = service dans le centre)
    $serviceAccueil = $demande->etablissement_stage ?: ($personnel->service ?: null);
@endphp

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-title')
ATTESTATION DE STAGE
@endsection

@section('doc-body')
<p class="indent-first">
    Je soussigné <strong>{{ $drh_nom }}</strong>, Directeur des Ressources Humaines
    du Centre de Santé à Vocation Humanitaire Saint Luc de Cotonou, atteste que
    <strong>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
    a effectué un stage de découverte
    @if($dureeTexte) de <strong>{{ $dureeTexte }}</strong> @endif
    dans ledit centre.
    {{ $il_elle }} a servi notamment
    @if($serviceAccueil)
        au service de <strong>{{ strtoupper($serviceAccueil) }}</strong>
    @endif
    du centre
    @if($demande->date_debut && $demande->date_fin)
        du <strong>{{ $demande->date_debut->isoFormat('DD') }}</strong>
        au <strong>{{ $demande->date_fin->isoFormat('DD MMMM YYYY') }}</strong> inclus.
    @endif
</p>

<p>
    Pendant cette période, {{ $il_elle }} a fait preuve de professionnalisme,
    d'assiduité, de dévouement et de respect dans l'exécution des tâches qui lui ont été confiées.
</p>

<p class="indent-first">
    En foi de quoi, la présente attestation lui est délivrée pour servir et valoir ce que de droit.
</p>
@endsection
