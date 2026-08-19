{{--
    partials/_letterhead.blade.php
    Rendu dynamique et fidèle de l'en-tête selon le centre sélectionné.
--}}
@php
    $code = strtoupper($centre?->code ?? 'ST_LUC');

    // Base64 helper
    $getImgB64 = function($pathRelative) {
        $fullPath = storage_path('app/public/' . $pathRelative);
        if (file_exists($fullPath)) {
            $ext = str_ends_with($pathRelative, '.png') ? 'png' : 'jpeg';
            return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fullPath));
        }
        $publicPath = public_path('images/letterhead/' . basename($pathRelative));
        if (file_exists($publicPath)) {
            $ext = str_ends_with($publicPath, '.png') ? 'png' : 'jpeg';
            return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($publicPath));
        }
        return null;
    };

    $logoArchidiocese = $getImgB64('letterhead/logo_archidiocese.jpeg');
    $photoEveque      = $getImgB64('letterhead/photo_eveque.jpeg');
    $logoDdis         = $getImgB64('letterhead/logo_ddis.jpeg');
    $logoPadrePio     = $getImgB64('letterhead/logo_padre_pio.jpg');
    $logoSeyon        = $getImgB64('letterhead/logo_seyon.png');
    $logoStJoseph     = $getImgB64('letterhead/logo_st_joseph.png');
    $logoStJeanImage  = $getImgB64('letterhead/logo_st_jean_maria_gleta.png') ?? $logoArchidiocese;
@endphp

<style>
    .letterhead-container {
        border-bottom: 2px solid #000;
        padding-bottom: 8px;
        margin-bottom: 16px;
        font-family: 'Times New Roman', Times, serif;
    }
    .lh-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .lh-img {
        max-height: 75px;
        width: auto;
        object-fit: contain;
        flex-shrink: 0;
    }
    .lh-center-text {
        flex: 1;
        text-align: center;
        line-height: 1.4;
    }
    .lh-title-bold {
        font-weight: 700;
        text-transform: uppercase;
    }
    .lh-separators {
        font-size: 0.85rem;
        color: #444;
        margin: 2px 0;
    }

    .lh-maria-gleta-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .lh-st-jean-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }
    .lh-st-jean-right-imgs {
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>

<div class="letterhead-container">

    @if($code === 'ST_JEAN')
        {{-- SAINT JEAN COTONOU : Même disposition visuelle que Maria Gléta pour la hiérarchie des titres, placé dans le coin supérieur gauche --}}
        <div class="lh-st-jean-header">
            <div style="flex:1; line-height:1.45; text-align:left;">
                <div style="font-size:0.85rem; font-weight:700; text-transform:uppercase;">REPUBLIQUE DU BENIN</div>
                <div style="font-size:0.88rem; font-weight:700; text-transform:uppercase;">ARCHIDIOCESE DE COTONOU</div>
                <div style="font-size:0.92rem; font-weight:700; text-transform:uppercase;">DIRECTION DIOCESAINE DE LA SANTE (DDIS)</div>
                <div style="font-size:0.96rem; font-weight:700; text-transform:uppercase;">CENTRE DE SANTE A VOCATION HUMANITAIRE</div>
                <div style="font-size:1.05rem; font-weight:700; text-transform:uppercase; color:#000;">"SAINT JEAN" DE COTONOU</div>
                <div style="font-weight:400; font-size:0.82rem; font-style:italic; margin-top:4px;">Gbégamey, Cotonou — Tél : 21 30 36 22 / 53 30 69 23</div>
                <div style="font-weight:400; font-size:0.82rem; font-style:italic;">Email : cm_stjean@yahoo.fr</div>
            </div>
            <div class="lh-st-jean-right-imgs">
                @if($logoArchidiocese)<img src="{{ $logoArchidiocese }}" alt="Archidiocèse" class="lh-img" style="max-height:65px;">@endif
                @if($logoDdis)<img src="{{ $logoDdis }}" alt="DDIS" class="lh-img" style="max-height:65px;">@endif
                @if($logoStJeanImage)<img src="{{ $logoStJeanImage }}" alt="Saint Jean" class="lh-img" style="max-height:65px;">@endif
            </div>
        </div>

    @elseif($code === 'MARIA_GLETA')
        {{-- MARIA GLETA : Photo St Jean à gauche, Titres superposés au centre, Coordonnées à droite --}}
        <div class="lh-maria-gleta-header">
            @if($logoStJeanImage)
                <img src="{{ $logoStJeanImage }}" alt="Saint Jean" class="lh-img" style="max-height:85px; width:auto;">
            @endif
            
            <div style="flex:1; text-align:center; line-height:1.45;">
                <div style="font-size:0.88rem; font-weight:700; text-transform:uppercase;">ARCHIDIOCESE DE COTONOU</div>
                <div style="font-size:0.92rem; font-weight:700; text-transform:uppercase;">DIRECTION DIOCESAINE DE LA SANTE</div>
                <div style="font-size:0.96rem; font-weight:700; text-transform:uppercase;">CENTRE DE SANTE A VOCATION HUMANITAIRE</div>
                <div style="font-size:1.05rem; font-weight:700; text-transform:uppercase; color:#000;">SAINT JEAN MARIA GLETA</div>
                <div style="font-size:0.82rem; font-style:italic; margin-top:4px;">
                    E-mail : cmariagleta@gmail.com
                </div>
            </div>

            <div style="text-align:right; font-size:0.88rem; line-height:1.45; flex-shrink:0;">
                <div style="font-weight:700;">02 B.P 1306</div>
                <div>TEL. 69 27 91 10</div>
                <div>Abomey-Calavi</div>
                <div style="font-weight:700;">République du Bénin</div>
            </div>
        </div>

    @elseif($code === 'SEYON')
        {{-- SÊYON : Logo Archidiocèse à gauche, Logo Sêyon à droite, séparateurs ..oo0oo.. au centre --}}
        <div class="lh-flex">
            @if($logoArchidiocese)<img src="{{ $logoArchidiocese }}" alt="" class="lh-img">@endif
            <div class="lh-center-text">
                <div style="font-size:0.85rem;font-weight:700;">RÉPUBLIQUE DU BÉNIN</div>
                <div class="lh-separators">..oo0oo..</div>
                <div class="lh-title-bold" style="font-size:0.95rem;">ARCHIDIOCÈSE DE COTONOU</div>
                <div class="lh-separators">..oo0oo..</div>
                <div style="font-weight:700;font-size:0.9rem;">DIRECTION DIOCESAINE DE LA SANTE</div>
                <div class="lh-separators">....oo0oo....</div>
                <div class="lh-title-bold" style="font-size:1.05rem;">CENTRE DE SANTE A VOCATION HUMANITAIRE ''SÊYON''</div>
                <div style="font-size:0.78rem;font-style:italic;line-height:1.2;margin-top:2px;">
                    Centre de Soins, de Recherches en Médecines Naturelles, d'Accompagnement Spirituel et de Clinique Psychologique.
                </div>
                <div class="lh-separators">..oo0oo..</div>
            </div>
            @if($logoSeyon)<img src="{{ $logoSeyon }}" alt="" class="lh-img">@endif
        </div>

    @elseif($code === 'SO_TCHANHOUE')
        {{-- SO-TCHANHOUÉ : Logo St Joseph à gauche, Logo Archidiocèse à droite --}}
        <div class="lh-flex">
            @if($logoStJoseph)<img src="{{ $logoStJoseph }}" alt="" class="lh-img">@endif
            <div class="lh-center-text">
                <div style="font-size:0.9rem;">Archidiocèse de Cotonou</div>
                <div style="font-size:0.95rem;font-weight:700;">Direction Diocésaine de la Santé</div>
                <div class="lh-title-bold" style="font-size:1.05rem;margin:3px 0;">Centre de Santé à Vocation Humanitaire Saint Joseph de Sô-Tchanhoué</div>
                <div style="font-size:0.82rem;">Email: csvhsotchanhoue@gmail.com</div>
                <div style="font-size:0.82rem;">Tél : 66 62 25 62</div>
            </div>
            @if($logoArchidiocese)<img src="{{ $logoArchidiocese }}" alt="" class="lh-img">@endif
        </div>

    @elseif($code === 'GLO')
        {{-- PADRE PIO GLO-DJIGBÉ : Logo Padre Pio à gauche --}}
        <div class="lh-flex">
            @if($logoPadrePio)<img src="{{ $logoPadrePio }}" alt="" class="lh-img" style="max-height:80px;">@endif
            <div class="lh-center-text">
                <div style="font-size:0.9rem;">Archidiocèse de Cotonou</div>
                <div style="font-weight:700;font-size:0.95rem;">DIRECTION DIOCESAINE DE LA SANTE</div>
                <div class="lh-title-bold" style="font-size:1rem;margin:2px 0;">CENTRE DE SANTE A VOCATION HUMANITAIRE PADRE PIO GLO DJIGBE</div>
                <div style="font-size:0.82rem;">TÉL : 0169813953 / Whatsapp : 93374043 BP : 0322 GLO DJIGBE</div>
                <div style="font-size:0.82rem;">Csvhpadrepioglo@gmail.com</div>
                <div style="font-size:0.85rem;font-weight:700;margin-top:2px;">REPUBLIQUE DU BENIN</div>
            </div>
        </div>

    @elseif($code === 'CAFSC')
        {{-- CAFSC : En-tête texte seul centré --}}
        <div class="lh-center-text" style="font-size:0.88rem;line-height:1.45;">
            <div class="lh-title-bold">ARCHIDIOCESE DE COTONOU</div>
            <div>01 BP 491 Cotonou (RB)</div>
            <div style="font-weight:700;">DIRECTION DIOCESAINE DE LA SANTE (DDIS)</div>
            <div class="lh-title-bold" style="font-size:0.95rem;margin:3px 0;">CENTRALE D'APPROVISIONNEMENT DES FORMATIONS SANITAIRES CATHOLIQUES (CAFSC)</div>
            <div>02 BP : 1306 COTONOU   ;    Tél: 21 31 85 26   ;    Email: phciediocesaine@gmail.com</div>
            <div style="font-style:italic;">Tokpa-hoho (Ganhi)   COTONOU</div>
        </div>

    @elseif($code === 'DDIS')
        {{-- DDIS : Logo DDIS à gauche, Logo Archidiocèse à droite --}}
        <div class="lh-flex">
            @if($logoDdis)<img src="{{ $logoDdis }}" alt="" class="lh-img">@endif
            <div class="lh-center-text">
                <div style="font-size:0.85rem;font-weight:700;">REPUBLIQUE DU BENIN</div>
                <div class="lh-title-bold" style="font-size:0.95rem;">ARCHIDIOCESE DE COTONOU</div>
                <div class="lh-title-bold" style="font-size:1.05rem;margin:2px 0;">DIRECTION DIOCESAINE DE LA SANTE (DDIS)</div>
                <div style="font-size:0.85rem;">www.ddiscotonou.org    contact@ddiscotonou.org</div>
            </div>
            @if($logoArchidiocese)<img src="{{ $logoArchidiocese }}" alt="" class="lh-img">@endif
        </div>

    @else
        {{-- ST_LUC (Par défaut) : Photo Évêque à gauche, Logo Archidiocèse à droite --}}
        <div class="lh-flex">
            @if($photoEveque)<img src="{{ $photoEveque }}" alt="" class="lh-img">@endif
            <div class="lh-center-text">
                <div class="lh-title-bold" style="font-size:0.95rem;">ARCHIDIOCESE DE COTONOU</div>
                <div style="font-weight:700;font-size:0.9rem;">DIRECTION DIOCESAINE DE LA SANTE</div>
                <div class="lh-title-bold" style="font-size:1.05rem;margin:2px 0;">CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC</div>
                <div style="font-size:0.85rem;font-weight:700;">C.S.V.H (ex : Hôpital Saint LUC)</div>
                <div style="font-size:0.78rem;margin-top:2px;">
                    Qtier Missèkplé Ste Rita- 01 BP 3603 Tél : 66 43 44 78 - 90 07 49 67 / Email : hopitalsaintluc@gmail.com / Cotonou - BENIN
                </div>
            </div>
            @if($logoArchidiocese)<img src="{{ $logoArchidiocese }}" alt="" class="lh-img">@endif
        </div>
    @endif

</div>
