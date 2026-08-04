@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N°3874/MSP/DGM/DPNS/SRC DU 16/12/1988';

    $normalize = function (?string $value): string {
        return \Illuminate\Support\Str::of((string) $value)
            ->ascii()
            ->lower()
            ->squish()
            ->toString();
    };

    $articleAvantQualite = function (?string $value) use ($normalize): string {
        $word = $normalize($value);
        if ($word === '') {
            return 'de ';
        }

        return in_array(mb_substr($word, 0, 1), ['a', 'e', 'i', 'o', 'u', 'y', 'h'], true)
            ? "d'"
            : 'de ';
    };

    $articleAvantService = function (?string $value) use ($normalize): string {
        $service = $normalize($value);
        if ($service === '') {
            return 'du ';
        }

        $feminins = [
            'medecine', 'pharmacie', 'comptabilite', 'caisse', 'facturation',
            'chirurgie', 'maternite', 'pediatrie', 'radiologie', 'kinesitherapie',
            'gastro-enterologie', 'gastroenterologie', 'direction', 'direction des ressources humaines',
        ];

        $masculins = [
            'secretariat', 'laboratoire', 'magasin', 'service general', 'service des soins infirmiers',
            'urgence', 'urgences', 'soins infirmiers', 'secrétariat', 'secretariat',
        ];

        foreach ($feminins as $needle) {
            if (str_contains($service, $needle)) {
                return 'de la ';
            }
        }

        foreach ($masculins as $needle) {
            if (str_contains($service, $needle)) {
                return 'du ';
            }
        }

        return in_array(mb_substr($service, 0, 1), ['a', 'e', 'i', 'o', 'u', 'y', 'h'], true)
            ? "de l'"
            : 'du ';
    };

    // ── CORRECTION DU POSTE SELON LE GENRE ──
    $posteCorrige = $personnel->corporation;
    if ($personnel->corporation && !$est_femme) {
        // Corrections pour les postes au masculin
        $posteCorrige = str_replace(
            ['ADMINISTRATIVE', 'Administrative', 'administrative'],
            'ADMINISTRATIF',
            $posteCorrige
        );
        $posteCorrige = str_replace(
            ['SECRETAIRE', 'secrétaire'],
            'SECRETAIRE',
            $posteCorrige
        );
        // Ajoutez d'autres corrections ici selon vos besoins
        // Exemple:
        // $posteCorrige = str_replace(['INFIRMIERE', 'Infirmière', 'infirmière'], 'INFIRMIER', $posteCorrige);
        // $posteCorrige = str_replace(['SAGE-FEMME', 'Sage-femme', 'sage-femme'], 'SAGE-FEMME', $posteCorrige); // Ne pas corriger pour les femmes
    }
@endphp

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-title')
ATTESTATION DE PRESENCE AU POSTE
@endsection

@section('doc-body')
<p class="indent-first">
    Je soussigné <strong>{{ $drh_nom }}</strong>, Directeur des
    Ressources Humaines du Centre de Santé à Vocation Humanitaire Saint Luc de Cotonou,
    atteste que {{ $civilite }} <strong>{{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
    né{{ $est_femme ? 'e' : '' }} le <strong>{{ $personnel->date_naissance ? $personnel->date_naissance->format('d/m/Y') : '__/__/____' }}</strong>
    est recruté{{ $est_femme ? 'e' : '' }} depuis le
    <strong>{{ $personnel->date_embauche_centre ? $personnel->date_embauche_centre->isoFormat('DD MMMM YYYY') : '____________' }}</strong>
    en qualité {{ $articleAvantQualite($posteCorrige) }}{{ $posteCorrige ?: '____________' }}.
</p>

<p>
    {{ $est_femme ? "L'intéressée" : "L'intéressé" }} a été présent{{ $est_femme ? 'e' : '' }} à son poste
    {{ $posteCorrige ? $articleAvantQualite($posteCorrige) : 'de ' }}{{ $posteCorrige ?: 'travail' }}
    @if($personnel->service)
        au service {{ $articleAvantService($personnel->service) }}{{ $personnel->service }}
    @endif
    jusqu'à ce jour.
</p>

<p class="indent-first">
    En foi de quoi, la présente attestation de présence au poste, lui est délivrée pour servir
    et valoir ce que de droit.
</p>
@endsection