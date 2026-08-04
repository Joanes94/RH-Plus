{{--
    _letterhead.blade.php
    Inclusion : @include('partials._letterhead')
    Variables attendues : $logoB64, $evequB64 (data URI base64)
    Si absentes, utilise asset() comme fallback.
--}}
@php
    // Résolution des images du letterhead en base64
    // (garantit l'affichage à l'impression et hors connexion)
    if (!isset($logoB64)) {
        $lp = public_path('images/letterhead/logo_archidiocese.jpeg');
        $logoB64 = file_exists($lp)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($lp))
            : asset('images/letterhead/logo_archidiocese.jpeg');
    }
    if (!isset($evequB64)) {
        $ep = public_path('images/letterhead/photo_eveque.jpeg');
        $evequB64 = file_exists($ep)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($ep))
            : asset('images/letterhead/photo_eveque.jpeg');
    }
@endphp
<div class="lh">
    <img src="{{ $evequB64 }}" alt="" class="lh-img-left">
    <div class="lh-center">
        <div class="lh-l1">ARCHIDIOCESE DE COTONOU</div>
        <div class="lh-l2">DIRECTION DIOCESAINE DE LA SANTE</div>
        <div class="lh-l3">CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC</div>
        <div class="lh-l4">C.S.V.H (ex : H&ocirc;pital Saint LUC)</div>
        <div class="lh-l5">Qtier Miss&egrave;kpl&eacute; Ste Rita - 01 BP 3603 &nbsp;|&nbsp; T&eacute;l : 66 43 44 78 &ndash; 90 07 49 67 &nbsp;|&nbsp; hopitalsaintluc@gmail.com &nbsp;|&nbsp; Cotonou &ndash; BENIN</div>
    </div>
    <img src="{{ $logoB64 }}" alt="" class="lh-img-right">
</div>
