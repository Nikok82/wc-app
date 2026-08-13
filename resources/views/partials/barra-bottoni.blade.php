{{-- Barra bottoni globale a piè di pagina (05/08) — visibile SOLO in responsive (≤768px).
     Sei sezioni: [contesto prec.] [indietro] [aggiorna] [stop] [avanti] [contesto succ.].
     Le sezioni 1 e 6 sono opzionali e contestuali: i layout passano $barraPrev/$barraNext
     come array ['url' => link, 'img' => miniatura (opz.), 'icon' => nome icona (opz.),
     'label' => titolo]. Se assenti, le sezioni restano vuote (segnaposto flessibile).
     Le azioni non permesse ricevono la classe .wcb-off (opacità 50%) da wc.js:
     frecce via Navigation API (solo Chrome/Edge/Android — su Safari/Firefox restano
     sempre attive), aggiorna/stop alternati sullo stato di caricamento. --}}
<style>
    .wc-barra{position:fixed;left:0;bottom:0;width:100%;height:56px;z-index:9999;
        display:none;align-items:stretch;gap:2px;padding:0 4px;
        background:linear-gradient(180deg,#0f6c14 0%,#045e03 100%);
        box-shadow:0 -2px 10px rgba(0,0,0,.45);}
    .wc-barra .wcb-slot{flex:1 1 0;display:flex;align-items:center;justify-content:center;min-width:0;}
    .wc-barra .wcb-btn{display:flex;align-items:center;justify-content:center;
        width:100%;max-width:62px;height:42px;border:0;border-radius:8px;cursor:pointer;
        color:#ffff00;background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
        box-shadow:0 1px 3px rgba(0,0,0,.3);transition:opacity .2s,filter .15s;}
    .wc-barra .wcb-btn:hover{filter:brightness(1.08);}
    .wc-barra .wcb-btn svg{width:24px;height:24px;display:block;}
    .wc-barra .wcb-off{opacity:.5;cursor:default;filter:none !important;}
    .wc-barra .wcb-ctx{display:flex;align-items:center;gap:4px;color:#ffff00;
        text-decoration:none;padding:3px;max-width:100%;}
    .wc-barra .wcb-ctx:hover{text-decoration:none;filter:brightness(1.1);}
    /* Miniature contestuali: stessa altezza dei bottoni centrali (42px).
       .tonda  -> bandiere delle squadre, cerchio di diametro 42px
       .quadrata -> locandine dei tornei, quadrato di lato 42px
       In entrambi i casi object-fit:cover, altrimenti l'immagine si
       deforma nel riquadro invece di essere ritagliata. */
    .wc-barra .wcb-ctx img{height:42px;width:auto;max-width:56px;object-fit:contain;
        border-radius:4px;background:#ffffffcc;box-shadow:0 2px 6px rgba(0,0,0,.4);}
    .wc-barra .wcb-ctx img.tonda{width:42px;height:42px;max-width:42px;
        border-radius:50%;object-fit:cover;background:#ffffffcc;}
    .wc-barra .wcb-ctx img.quadrata{width:42px;height:42px;max-width:42px;
        border-radius:6px;object-fit:cover;background:#ffffffcc;}
    .wc-barra .wcb-ctx svg{width:18px;height:18px;flex:0 0 auto;display:block;}
    .wc-barra .wcb-ctx .wcb-ctx-icona svg{width:34px;height:34px;}
    @media (max-width:768px){
        .wc-barra{display:flex;}
        body{padding-bottom:68px;}
    }
</style>
<nav class="wc-barra" aria-label="Navigazione rapida">
    <div class="wcb-slot">
        @if (!empty($barraPrev))
            <a class="wcb-ctx" href="{{ $barraPrev['url'] }}" title="{{ $barraPrev['label'] ?? '' }}">
                {!! \App\Support\Icons::svg('angle-double-left') !!}
                @if (!empty($barraPrev['img']))
                    <img class="{{ $barraPrev['forma'] ?? '' }}"
                         src="{{ $barraPrev['img'] }}" alt="{{ $barraPrev['label'] ?? '' }}"
                         onerror="this.style.display='none'">
                @elseif (!empty($barraPrev['icon']))
                    <span class="wcb-ctx-icona">{!! \App\Support\Icons::svg($barraPrev['icon']) !!}</span>
                @endif
            </a>
        @endif
    </div>
    <div class="wcb-slot"><button type="button" class="wcb-btn" data-wcbar="back"
        title="Indietro">{!! \App\Support\Icons::svg('undo') !!}</button></div>
    <div class="wcb-slot"><button type="button" class="wcb-btn" data-wcbar="reload"
        title="Aggiorna la pagina">{!! \App\Support\Icons::svg('reload') !!}</button></div>
    <div class="wcb-slot"><button type="button" class="wcb-btn" data-wcbar="stop"
        title="Ferma il caricamento">{!! \App\Support\Icons::svg('stop') !!}</button></div>
    <div class="wcb-slot"><button type="button" class="wcb-btn" data-wcbar="forward"
        title="Avanti">{!! \App\Support\Icons::svg('redo') !!}</button></div>
    <div class="wcb-slot">
        @if (!empty($barraNext))
            <a class="wcb-ctx" href="{{ $barraNext['url'] }}" title="{{ $barraNext['label'] ?? '' }}">
                @if (!empty($barraNext['img']))
                    <img class="{{ $barraNext['forma'] ?? '' }}"
                         src="{{ $barraNext['img'] }}" alt="{{ $barraNext['label'] ?? '' }}"
                         onerror="this.style.display='none'">
                @elseif (!empty($barraNext['icon']))
                    <span class="wcb-ctx-icona">{!! \App\Support\Icons::svg($barraNext['icon']) !!}</span>
                @endif
                {!! \App\Support\Icons::svg('angle-double-right') !!}
            </a>
        @endif
    </div>
</nav>
