{{-- Asset condivisi: config JS, schermata di caricamento e script comune.
     Da includere in fondo al <body> di ogni layout. --}}
<style>
    /* ---- C3 (15/08): schermata di caricamento, unica per tutto il sito ----
       Icona del sito a 250x250 su fondo bianco al 60%, comparsa in
       dissolvenza; l'icona ruota in senso orario e sotto pulsa in ciclo un
       bagliore verde che si espande e si contrae.
       Due impieghi:
         .wc-carica         -> velo a tutto schermo, navigazione fra pagine
         .wc-splash         -> stessa animazione dentro il riquadro di un tab
       Su schermi bassi l'icona si rimpicciolisce, altrimenti tocca i bordi. */
    .wc-carica{position:fixed;inset:0;z-index:12000;display:flex;
        align-items:center;justify-content:center;background:rgba(255,255,255,.6);
        opacity:0;pointer-events:none;transition:opacity .25s ease;}
    .wc-carica.visibile{opacity:1;pointer-events:auto;}

    .wc-splash{display:flex;align-items:center;justify-content:center;
        padding:44px 0;}

    .wc-carica-corpo{position:relative;display:flex;align-items:center;
        justify-content:center;width:250px;height:250px;max-width:60vw;max-height:60vw;}
    .wc-carica-corpo .bagliore{position:absolute;inset:12%;border-radius:50%;
        background:radial-gradient(circle,rgba(27,158,87,.55) 0%,rgba(87,199,133,.25) 55%,rgba(87,199,133,0) 72%);
        animation:wc-bagliore 1.8s ease-in-out infinite;}
    .wc-carica-corpo img{position:relative;width:100%;height:100%;object-fit:contain;
        animation:wc-gira 2.4s linear infinite;}

    @keyframes wc-gira{
        from{transform:rotate(0deg);}
        to{transform:rotate(360deg);}
    }
    @keyframes wc-bagliore{
        0%,100%{transform:scale(.82);opacity:.45;}
        50%{transform:scale(1.12);opacity:.95;}
    }
    @media (prefers-reduced-motion:reduce){
        .wc-carica-corpo img,.wc-carica-corpo .bagliore{animation:none;}
    }
</style>
<script>
    window.WCCONF = {
        logoSplash: @json(route('img', ['tipo' => 'site_logos', 'file' => 'logo_no_brand_no_bg_512.png'])),
        leafletJs:  @json(asset('vendor/leaflet/leaflet.js')),
        leafletCss: @json(asset('vendor/leaflet/leaflet.css'))
    };
</script>
<script src="{{ asset('js/wc.js') }}"></script>
