@extends('demandes.documents._base')
@php
    $refCode = $demande->reference ?? '…../'.date('m').'-'.date('y').'/AC/DDIS/CSVHHSL/DIR/DRH/ARH';
    $autorisationFooter = 'AUTORISATION  DU MINISTERE  N°3874/MSP/DGM/DPNS/SRC DU 16/12/1988';

    $normalizeProfession = function (?string $value): string {
        return \Illuminate\Support\Str::of((string) $value)
            ->ascii()
            ->upper()
            ->squish()
            ->toString();
    };

    $professionSelonGenre = function (?string $value) use ($normalizeProfession, $est_femme): string {
        $profession = $normalizeProfession($value);
        if ($profession === '') {
            return '____________';
        }

        $specificites = [
            'CHIRURGIEN'               => ['M' => 'CHIRURGIEN', 'F' => 'CHIRURGIENNE'],
            'INFIRMIER'                => ['M' => 'INFIRMIER', 'F' => 'INFIRMIERE'],
            'SECRETAIRE ADMINISTRATIVE'=> ['M' => 'SECRETAIRE ADMINISTRATIF', 'F' => 'SECRETAIRE ADMINISTRATIVE'],
            'SECRETAIRE MEDICAL'       => ['M' => 'SECRETAIRE MEDICAL', 'F' => 'SECRETAIRE MEDICALE'],
            'DIRECTEUR'                => ['M' => 'DIRECTEUR', 'F' => 'DIRECTRICE'],
            'ASSISTANT'                => ['M' => 'ASSISTANT', 'F' => 'ASSISTANTE'],
            'CAISSIER'                 => ['M' => 'CAISSIER', 'F' => 'CAISSIERE'],
            'MAGASINIER'               => ['M' => 'MAGASINIER', 'F' => 'MAGASINIERE'],
            'TECHNICIEN'               => ['M' => 'TECHNICIEN', 'F' => 'TECHNICIENNE'],
            'SURVEILLANT'              => ['M' => 'SURVEILLANT', 'F' => 'SURVEILLANTE'],
        ];

        foreach ($specificites as $base => $formes) {
            if (str_contains($profession, $base)) {
                return $est_femme ? $formes['F'] : $formes['M'];
            }
        }

        if ($est_femme) {
            $suffixes = [
                'IEN'   => 'IENNE',
                'IER'   => 'IERE',
                'TEUR'  => 'TRICE',
                'EUR'   => 'EUSE',
                'ANT'   => 'ANTE',
                'EL'    => 'ELLE',
                'ER'    => 'ERE',
            ];

            foreach ($suffixes as $suffix => $remplacement) {
                if (str_ends_with($profession, $suffix)) {
                    return preg_replace('/' . $suffix . '$/', $remplacement, $profession) ?: $profession;
                }
            }
        } else {
            $suffixes = [
                'IENNE' => 'IEN',
                'IERE'  => 'IER',
                'TRICE' => 'TEUR',
                'EUSE'  => 'EUR',
                'ANTE'  => 'ANT',
                'ELLE'  => 'EL',
                'ERE'   => 'ER',
            ];

            foreach ($suffixes as $suffix => $remplacement) {
                if (str_ends_with($profession, $suffix)) {
                    return preg_replace('/' . $suffix . '$/', $remplacement, $profession) ?: $profession;
                }
            }
        }

        return $profession;
    };
@endphp

@section('doc-ref')
{{ $refCode }}
@endsection

@section('doc-title')
ATTESTATION DE TRAVAIL
@endsection

@section('doc-body')
<p class="indent-first">
    Je soussigné <strong>{{ $drh_nom }}</strong>, Directeur des
    Ressources Humaines du Centre de Santé à Vocation Humanitaire Saint Luc de Cotonou,
    atteste que <strong>{{ $civilite }} {{ strtoupper($personnel->nom) }} {{ $personnel->prenoms }}</strong>,
    né{{ $est_femme ? 'e' : '' }} le <strong>{{ $personnel->date_naissance ? $personnel->date_naissance->format('d/m/Y') : '__/__/____' }}</strong>
    est recruté{{ $est_femme ? 'e' : '' }} depuis le
    <strong>{{ $personnel->date_embauche_centre ? $personnel->date_embauche_centre->isoFormat('DD MMMM YYYY') : '____________' }}</strong>
    en qualité de {{ $professionSelonGenre($personnel->corporation) }},
    sous contrat à
    @if($personnel->type_contrat === 'CDD')
        durée déterminée qui expire le
        <strong>{{ $personnel->date_fin_contrat ? $personnel->date_fin_contrat->isoFormat('DD MMMM YYYY') : '____________' }}</strong>.
    @else
        durée indéterminée.
    @endif
</p>

@if($personnel->type_contrat !== 'CDD' && $personnel->date_depart_retraite)
<p>
    <strong>La date présumée de son départ à la retraite est le
    {{ $personnel->date_depart_retraite->isoFormat('DD MMMM YYYY') }} sauf licenciement.</strong>
</p>
@endif

<p class="indent-first">
    En foi de quoi la présente attestation lui est délivrée pour servir et valoir ce que de droit.
</p>
@endsection
