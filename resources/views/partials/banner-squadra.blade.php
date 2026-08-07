{{-- Banner della scheda squadra / squadra-anno: replica il banner dei tornei
     (sfondo sfumato e ruotato + titolo grande) ma con la bandiera al posto
     del manifesto. Parametri: $flag (url immagine, può essere null), $titolo. --}}
<div class="sq-banner">
    @if (!empty($flag))
        <div class="sq-banner-bg" style="background-image:url('{{ $flag }}')"></div>
    @endif
    <h1 class="sq-banner-title">{{ $titolo }}</h1>
</div>

<style>
    .sq-banner{position:relative;overflow:hidden;margin:-24px -16px 14px;padding:38px 18px 10px;
        min-height:130px;display:flex;align-items:flex-end;justify-content:flex-end;}
    .sq-banner-bg{position:absolute;z-index:0;top:-30px;left:-5%;width:110%;height:150%;
        background-size:cover;background-position:center;transform:rotate(3deg);
        opacity:.28;filter:saturate(1.1);pointer-events:none;}
    .sq-banner-title{position:relative;z-index:2;text-align:right;margin:0;
        font-size:2.6rem;font-weight:800;color:#ffff00;
        text-shadow:2px 2px 0 #045e03,0 1px 6px rgba(0,0,0,.35);letter-spacing:-.5px;}
    @media (max-width:560px){ .sq-banner-title{font-size:1.9rem;} .sq-banner{min-height:100px;} }
</style>
