{{-- Asset condivisi: config JS, splash di caricamento e script comune.
     Da includere in fondo al <body> di ogni layout. --}}
<style>
    .wc-splash{display:flex;flex-direction:column;align-items:center;justify-content:center;
               padding:44px 0;gap:18px;}
    .wc-splash-logo{width:92px;height:92px;animation:wcpulse 1.2s ease-in-out infinite;}
    .wc-splash-bar{width:180px;height:6px;border-radius:99px;background:#e2e8e5;overflow:hidden;}
    .wc-splash-bar span{display:block;height:100%;width:40%;border-radius:99px;
               background:linear-gradient(90deg,#045e03,#57c785);
               animation:wcbar 1.1s ease-in-out infinite;}
    @keyframes wcbar{0%{transform:translateX(-110%)}100%{transform:translateX(360%)}}
    @keyframes wcpulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
</style>
<script>
    window.WCCONF = {
        logoSplash: @json(route('img', ['tipo' => 'site_logos', 'file' => 'logo_no_brand_no_bg_512.png'])),
        leafletJs:  @json(asset('vendor/leaflet/leaflet.js')),
        leafletCss: @json(asset('vendor/leaflet/leaflet.css'))
    };
</script>
<script src="{{ asset('js/wc.js') }}"></script>
