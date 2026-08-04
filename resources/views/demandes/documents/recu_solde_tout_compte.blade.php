@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/RSTC/DDIS/'
        . ($personnel->centre ? str_replace(' ', '', $personnel->centre->code) : 'CSVHHSL')
        . '/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N°3874/MSP/DGM/DPNS/SRC DU 16/12/1988';

    // Récupérer le contrat actif ou le dernier contrat
    $contratActif = $personnel->contrat_actif ?? $personnel->contrats()->latest('date_debut')->first();

    // Calculs financiers de base
    $salaireBase   = $contratActif?->salaire_base ?? 0;
    $anciennete    = $personnel->date_embauche_centre
        ? $personnel->date_embauche_centre->diffInYears(now())
        : 0;

    // Données optionnelles depuis les champs de la demande
    $dates  = $demande->dates ?? [];
    $motif  = $demande->motif ?? '';
    $dateFin = !empty($dates[0]) ? \Carbon\Carbon::parse($dates[0]) : now();
@endphp

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-title')
REÇU POUR SOLDE DE TOUT COMPTE
@endsection

@section('doc-body')
<p class="indent-first">
    Je soussigné{{ $est_femme ? 'e' : '' }}
    <strong>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
    né{{ $est_femme ? 'e' : '' }} le
    <strong>{{ $personnel->date_naissance ? $personnel->date_naissance->format('d/m/Y') : '__/__/____' }}</strong>,
    demeurant à ______________________________,
    déclare avoir reçu de <strong>{{ $personnel->centre?->nom ?? 'l\'employeur' }}</strong>,
    pour solde de tout compte, la somme de :
</p>

<div class="tableau-solde" style="margin: 20px 0; border: 1px solid #333; padding: 15px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="border-bottom: 1px solid #ccc;">
            <td style="padding: 6px 10px; width: 60%;">Salaire du mois en cours</td>
            <td style="padding: 6px 10px; text-align: right;"><strong>{{ number_format($salaireBase, 0, ',', ' ') }} FCFA</strong></td>
        </tr>
        <tr style="border-bottom: 1px solid #ccc;">
            <td style="padding: 6px 10px;">Indemnité compensatrice de congés payés</td>
            <td style="padding: 6px 10px; text-align: right;">____________ FCFA</td>
        </tr>
        <tr style="border-bottom: 1px solid #ccc;">
            <td style="padding: 6px 10px;">Indemnité compensatrice de préavis</td>
            <td style="padding: 6px 10px; text-align: right;">____________ FCFA</td>
        </tr>
        <tr style="border-bottom: 1px solid #ccc;">
            <td style="padding: 6px 10px;">Indemnité de licenciement / fin de contrat</td>
            <td style="padding: 6px 10px; text-align: right;">____________ FCFA</td>
        </tr>
        <tr style="border-bottom: 1px solid #ccc;">
            <td style="padding: 6px 10px;">Prime d'ancienneté ({{ $anciennete }} an{{ $anciennete > 1 ? 's' : '' }})</td>
            <td style="padding: 6px 10px; text-align: right;">____________ FCFA</td>
        </tr>
        <tr style="border-bottom: 1px solid #ccc;">
            <td style="padding: 6px 10px;">Autres sommes dues</td>
            <td style="padding: 6px 10px; text-align: right;">____________ FCFA</td>
        </tr>
        <tr style="border-top: 2px solid #333; font-weight: bold;">
            <td style="padding: 8px 10px; font-size: 1.05em;">TOTAL NET À PERCEVOIR</td>
            <td style="padding: 8px 10px; text-align: right; font-size: 1.05em;">____________ FCFA</td>
        </tr>
    </table>
</div>

<p class="indent-first">
    Ce reçu a été établi conformément aux dispositions de l'article 35 de la Convention
    Collective Générale du Travail applicable au Bénin, en triple exemplaire dont un remis
    {{ $est_femme ? 'à l\'intéressée' : 'à l\'intéressé' }}.
</p>

<p class="indent-first" style="margin-top: 15px;">
    <strong>Motif de la cessation d'activité :</strong>
    {{ $motif ?: '________________________________________' }}
</p>

<p class="indent-first" style="margin-top: 15px;">
    <strong>Informations relatives à l'emploi :</strong>
</p>
<ul style="padding-left: 30px; line-height: 1.8;">
    <li><strong>Fonction :</strong> {{ $contratActif?->fonction ?? $personnel->corporation ?? '____________' }}</li>
    <li><strong>Type de contrat :</strong> {{ $contratActif?->type_contrat ?? $personnel->type_contrat ?? '____________' }}</li>
    <li><strong>Date d'embauche :</strong> {{ $personnel->date_embauche_centre ? $personnel->date_embauche_centre->isoFormat('DD MMMM YYYY') : '____________' }}</li>
    <li><strong>Date de fin :</strong> {{ $dateFin->isoFormat('DD MMMM YYYY') }}</li>
    <li><strong>Catégorie / Échelon :</strong> {{ $contratActif?->categorie_echelon ?? $personnel->categorie_echelon ?? '____________' }}</li>
    <li><strong>Salaire de base mensuel :</strong> {{ number_format($salaireBase, 0, ',', ' ') }} FCFA</li>
</ul>

<p class="indent-first" style="margin-top: 20px;">
    {{ $est_femme ? 'L\'intéressée déclare' : 'L\'intéressé déclare' }} n'avoir plus rien à réclamer
    à {{ $personnel->centre?->nom ?? 'l\'employeur' }} au titre de l'exécution et de la cessation
    de son contrat de travail.
</p>

<p style="margin-top: 15px; font-style: italic; color: #555; font-size: 0.85em;">
    Ce reçu peut être dénoncé dans les deux (02) mois de sa signature.
    La dénonciation doit être faite par lettre recommandée.
</p>

<div style="margin-top: 30px; display: flex; justify-content: space-between;">
    <div style="text-align: center; width: 45%;">
        <p><strong>{{ $est_femme ? 'L\'employée' : 'L\'employé' }}</strong></p>
        <p style="margin-top: 5px; font-style: italic;">Lu et approuvé, bon pour solde de tout compte</p>
        <br><br>
        <p>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</p>
    </div>
    <div style="text-align: center; width: 45%;">
        <p><strong>L'employeur</strong></p>
        <br><br><br>
        <p>{{ $drh_nom }}</p>
    </div>
</div>
@endsection
