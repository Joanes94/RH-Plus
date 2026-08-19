{{--
    partials/_footer.blade.php
    Rendu dynamique du pied de page du document.
    N'affiche rien si le centre n'a pas de pied de page défini.
--}}
@php
    $piedPageText = $centre?->pied_page_texte;
@endphp

@if(!empty($piedPageText))
    <style>
        .doc-footer-container {
            position: absolute;
            bottom: 10mm;
            left: 18mm;
            right: 18mm;
            border-top: 1.5px solid #000;
            padding-top: 5px;
            text-align: center;
            font-size: 8px;
            font-weight: 700;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.35;
        }
    </style>
    <div class="doc-footer-container">
        {!! nl2br(e($piedPageText)) !!}
    </div>
@endif
