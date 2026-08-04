@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N071/MS/DC/SGMCJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020';

    $normalize = function (?string $value): string {
        return \Illuminate\Support\Str::of((string) $value)
            ->ascii()
            ->lower()
            ->squish()
            ->toString();
    };

    // ── Fonction pour l'article devant la qualité (poste) ──
    $articleAvantQualite = function (?string $value) use ($normalize): string {
        $word = $normalize($value);
        if ($word === '') {
            return 'de ';
        }

        return in_array(mb_substr($word, 0, 1), ['a', 'e', 'i', 'o', 'u', 'y', 'h'], true)
            ? "d'"
            : 'de ';
    };

    // ── Fonction pour l'article devant le service ──
    $articleAvantService = function (?string $value) use ($normalize): string {
        $service = $normalize($value);
        if ($service === '') {
            return 'du ';
        }

        // Services féminins (on dit "de la")
        $feminins = [
            'medecine', 'pharmacie', 'comptabilite', 'caisse', 'facturation',
            'chirurgie', 'maternite', 'pediatrie', 'radiologie', 'kinesitherapie',
            'gastro-enterologie', 'gastroenterologie', 'direction',
            'direction des ressources humaines', 'ressources humaines',
            'ophtalmologie', 'dermatologie', 'cardiologie', 'neurologie',
            'psychologie', 'nutrition', 'diabetologie', 'endocrinologie',
            'geriatrie', 'hematologie', 'immunologie', 'infectiologie',
            'hopital', 'hopital de jour', 'medecine interne', 'medecine generale'
        ];

        // Services masculins (on dit "du")
        $masculins = [
            'secretariat', 'laboratoire', 'magasin', 'service general',
            'service des soins infirmiers', 'urgence', 'urgences',
            'soins infirmiers', 'secrétariat', 'bloc operatoire',
            'imagerie', 'scanner', 'irm', 'echographie', 'endoscopie',
            'accueil', 'standard', 'archives', 'bureau', 'guichet'
        ];

        // Vérifier si le service est féminin
        foreach ($feminins as $needle) {
            if (str_contains($service, $needle)) {
                return 'de la ';
            }
        }

        // Vérifier si le service est masculin
        foreach ($masculins as $needle) {
            if (str_contains($service, $needle)) {
                return 'du ';
            }
        }

        // Par défaut, si le service commence par une voyelle ou 'h' muet
        return in_array(mb_substr($service, 0, 1), ['a', 'e', 'i', 'o', 'u', 'y', 'h'], true)
            ? "de l'"
            : 'du ';
    };

    // ── CORRECTION DU POSTE SELON LE GENRE ──
    $posteCorrige = $personnel->corporation;
    if ($personnel->corporation && !$est_femme) {
        // Si c'est un homme, corriger les terminaisons féminines
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
        $posteCorrige = str_replace(
            ['INFIRMIERE', 'Infirmière', 'infirmière'],
            'INFIRMIER',
            $posteCorrige
        );
        $posteCorrige = str_replace(
            ['TECHNICIENNE', 'Technicienne', 'technicienne'],
            'TECHNICIEN',
            $posteCorrige
        );
        $posteCorrige = str_replace(
            ['ASSISTANTE', 'Assistante', 'assistante'],
            'ASSISTANT',
            $posteCorrige
        );
        $posteCorrige = str_replace(
            ['COMPTABLE', 'comptable'],
            'COMPTABLE',
            $posteCorrige
        );
        // Ajoutez d'autres corrections ici selon vos besoins
        // Exemple: $posteCorrige = str_replace(['PSYCHOLOGUE'], 'PSYCHOLOGUE', $posteCorrige);
    }

    // ── CORRECTION DU NOM DU SERVICE ──
    $serviceCorrige = $personnel->service;
    if ($personnel->service) {
        // Mettre en majuscules et corriger les fautes courantes
        $serviceCorrige = strtoupper($serviceCorrige);
        $serviceCorrige = str_replace(
            ['SECRETARIAT', 'SECRETARIAT GENERAL'],
            'SECRETARIAT',
            $serviceCorrige
        );
        $serviceCorrige = str_replace(
            ['RESSOURCES HUMAINES', 'DRH'],
            'RESSOURCES HUMAINES',
            $serviceCorrige
        );
        $serviceCorrige = str_replace(
            ['MEDECINE', 'MEDECINE INTERNE', 'MEDECINE GENERALE'],
            'MEDECINE',
            $serviceCorrige
        );
    }
@endphp

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-title')
ATTESTATION DE PRESTATION DE SERVICES
@endsection

@section('doc-body')
<p class="indent-first">
    Je soussigné <strong>{{ $drh_nom }}</strong>, Directeur des Ressources Humaines du Centre de Santé
    à Vocation Humanitaire Saint Luc de Cotonou, atteste que
    {{ $civilite }} <strong>{{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
    {{ $posteCorrige ?: '____________' }}
    a exercé{{ $est_femme ? 'é' : '' }} sous contrat de prestation de
    services dans ledit Centre
    @if($personnel->service)
        au service {{ $articleAvantService($serviceCorrige) }}<strong>{{ $serviceCorrige }}</strong>
    @endif
    @if($demande->date_debut && $demande->date_fin)
        du <strong>{{ $demande->date_debut->isoFormat('DD MMMM YYYY') }}</strong> au
        <strong>{{ $demande->date_fin->isoFormat('DD MMMM YYYY') }}</strong> inclus.
    @endif
</p>

<p>
    Pendant cette période, {{ $est_femme ? 'elle' : 'il' }} a fait preuve de professionnalisme,
    d'assiduité, de dévouement et de respect dans l'exécution des tâches qui lui ont été confiées.
</p>

<p class="indent-first">
    En foi de quoi, la présente attestation lui est délivrée pour servir et valoir ce que de droit.
</p>
@endsection