@php
    $navSquadre = $navSquadre ?? collect();
    $navTornei  = $navTornei ?? collect();
    $logoNav = route('img', ['tipo' => 'site_logos', 'file' => 'logo_brand_white_no_bg_512.png']);
@endphp
<style>
    /* ---- barra superiore ---- */
    .wcnav{position:sticky;top:0;z-index:1100;display:flex;align-items:center;
           justify-content:space-between;gap:10px;padding:6px 12px;min-height:56px;
           background:linear-gradient(142deg,#045e03 0%,#0f6c14 55%,#045e03 100%);
           box-shadow:0 2px 8px rgba(0,0,0,.25);}
    .wcnav a{text-decoration:none;}
    .wcnav .wcnav-logo{display:flex;align-items:center;}
    .wcnav .wcnav-logo img{height:44px;width:auto;display:block;}
    .wcnav .wcnav-logo .fallback{color:#fff;font-weight:800;font-size:1rem;}
    .wcnav .wcnav-logo .fallback b{color:#ffff00;}

    /* hamburger -> X */
    .wcnav-burger{width:44px;height:44px;border:0;background:transparent;cursor:pointer;
           display:flex;flex-direction:column;justify-content:center;align-items:center;
           gap:6px;padding:8px;}
    .wcnav-burger span{display:block;width:26px;height:3px;border-radius:2px;
           background:#ffff00;transition:transform .3s,opacity .3s;}
    .wcnav-burger.aperto span:nth-child(1){transform:translateY(9px) rotate(45deg);}
    .wcnav-burger.aperto span:nth-child(2){opacity:0;}
    .wcnav-burger.aperto span:nth-child(3){transform:translateY(-9px) rotate(-45deg);}

    /* ---- overlay + drawer 260px ---- */
    #wc-drawer-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1190;
           opacity:0;pointer-events:none;transition:opacity .3s;}
    #wc-drawer-overlay.aperto{opacity:1;pointer-events:auto;}
    #wc-drawer{position:fixed;top:0;left:0;bottom:0;width:260px;z-index:1200;
           transform:translateX(-100%);transition:transform .3s ease;
           background:linear-gradient(180deg,#045e03 0%,#0f6c14 35%,#b3a000 85%,#d4b800 100%);
           overflow-y:auto;overscroll-behavior:contain;box-shadow:4px 0 18px rgba(0,0,0,.35);
           scrollbar-width:thin;}
    #wc-drawer.aperto{transform:translateX(0);}
    #wc-drawer .drawer-testa{display:flex;align-items:center;padding:12px 14px 8px;}
    #wc-drawer .drawer-testa img{height:40px;width:auto;}

    /* voci principali */
    #wc-drawer .drawer-acc-head,
    #wc-drawer .drawer-voce{display:flex;align-items:center;justify-content:space-between;
           padding:13px 16px;color:#ffff00;font-weight:700;font-size:15px;cursor:pointer;
           border-bottom:1px solid rgba(255,255,0,.18);text-decoration:none;}
    #wc-drawer .drawer-voce:hover,#wc-drawer .drawer-acc-head:hover{background:rgba(255,255,255,.08);}
    #wc-drawer .drawer-acc-head .caret{transition:transform .3s;font-size:12px;}
    #wc-drawer .drawer-acc.aperto .drawer-acc-head{background:#1c1c1c;color:#ffcf40;}
    #wc-drawer .drawer-acc.aperto .drawer-acc-head .caret{transform:rotate(180deg);}
    #wc-drawer .drawer-acc-corpo{display:none;background:#fff;}
    #wc-drawer .drawer-acc.aperto .drawer-acc-corpo{display:block;}

    /* righe squadre: nome a sinistra, bandiera che riempie il bordo destro */
    #wc-drawer .drawer-squadra{display:flex;align-items:center;height:34px;padding:0 0 0 14px;
           color:#111;font-weight:600;font-size:13.5px;border-bottom:1px solid #ececec;
           text-decoration:none;background-color:#fff;
           background-size:auto 100%;background-repeat:no-repeat;background-position:right center;}
    #wc-drawer .drawer-squadra .velo{position:relative;z-index:1;display:flex;align-items:center;
           width:100%;height:100%;
           background:linear-gradient(to right,#ffffff 52%,rgba(255,255,255,.88) 62%,rgba(255,255,255,0) 88%);}
    #wc-drawer .drawer-squadra:hover .velo{background:linear-gradient(to right,#f1f7f2 52%,rgba(241,247,242,.88) 62%,rgba(255,255,255,0) 88%);}

    /* righe tornei: manifesto a sinistra, "Paese Anno" a destra */
    #wc-drawer .drawer-torneo{display:flex;align-items:center;justify-content:space-between;
           gap:10px;padding:5px 14px 5px 10px;color:#111;font-weight:700;font-size:14px;
           border-bottom:1px solid #ececec;text-decoration:none;background:#fff;}
    #wc-drawer .drawer-torneo:hover{background:#f1f7f2;}
    #wc-drawer .drawer-torneo img{width:44px;height:56px;object-fit:contain;flex:none;}
    #wc-drawer .drawer-torneo span{text-align:right;}

    /* ================= A3: RICERCA GLOBALE (15/08) ================= */
    /* La lente sta accanto all'hamburger; al clic il campo entra da
       sinistra con uno scorrimento rapido e i risultati compaiono in un
       riquadro sovrapposto. */
    .wcnav-azioni{display:flex;align-items:center;gap:2px;}
    .wcnav-lente{width:44px;height:44px;border:0;background:transparent;cursor:pointer;
           display:flex;align-items:center;justify-content:center;color:#ffff00;padding:9px;}
    .wcnav-lente svg{width:24px;height:24px;color:#ffff00;}

    .wc-ricerca{position:fixed;top:0;left:0;right:0;z-index:1250;
           background:linear-gradient(142deg,#045e03 0%,#0f6c14 55%,#045e03 100%);
           box-shadow:0 2px 10px rgba(0,0,0,.35);
           transform:translateX(-100%);transition:transform .22s ease-out;}
    .wc-ricerca.aperta{transform:translateX(0);}
    .wc-ricerca .ric-barra{display:flex;align-items:center;gap:8px;padding:8px 10px;min-height:56px;}
    .wc-ricerca input{flex:1;min-width:0;font:inherit;font-size:16px;padding:10px 14px;
           border:0;border-radius:999px;background:#fff;color:var(--ink,#16231d);
           outline:2px solid transparent;}
    .wc-ricerca input:focus{outline-color:#ffff00;}
    .wc-ricerca .ric-chiudi{width:40px;height:40px;flex:none;border:0;background:transparent;
           color:#ffff00;font-size:24px;line-height:1;cursor:pointer;}

    #ricerca-overlay{position:fixed;inset:0;background:rgba(10,20,15,.45);z-index:1240;}
    #ricerca-overlay[hidden]{display:none;}

    #ricerca-risultati{position:fixed;top:56px;left:0;right:0;z-index:1245;
           max-height:calc(100dvh - 56px);overflow:auto;-webkit-overflow-scrolling:touch;
           background:#fff;border-radius:0 0 14px 14px;box-shadow:0 12px 30px rgba(0,0,0,.35);
           padding:8px 12px 16px;}
    #ricerca-risultati[hidden]{display:none;}
    @media (min-width:760px){
        .wc-ricerca{left:auto;right:auto;width:100%;}
        #ricerca-risultati{left:50%;transform:translateX(-50%);width:min(680px,96vw);
            border-radius:14px;}
    }

    .ric-gruppo{margin-bottom:12px;}
    .ric-titolo{display:flex;align-items:baseline;justify-content:space-between;gap:8px;
           padding:8px 2px 5px;border-bottom:2px solid var(--verde,#1b9e57);
           font-weight:800;font-size:13px;text-transform:uppercase;letter-spacing:.5px;
           color:var(--verde-scuro,#045e03);}
    .ric-titolo .ric-conta{font-size:12px;font-weight:600;color:#6b7a72;
           text-transform:none;letter-spacing:0;}
    .ric-voce{display:flex;align-items:center;gap:10px;padding:9px 4px;
           border-bottom:1px solid #e2e8e5;color:#16231d;text-decoration:none;min-width:0;}
    .ric-voce:last-of-type{border-bottom:0;}
    .ric-voce:hover{background:#f2f7f4;text-decoration:none;}
    .ric-voce img,.ric-voce .ric-vuota{width:26px;height:26px;flex:none;border-radius:50%;
           object-fit:cover;box-shadow:0 1px 2px rgba(0,0,0,.22);background:#eef2ef;}
    .ric-voce .ric-vuota{box-shadow:none;}
    .ric-testi{display:flex;flex-direction:column;min-width:0;}
    .ric-nome{font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .ric-sotto{font-size:12px;color:#6b7a72;overflow:hidden;text-overflow:ellipsis;
           white-space:nowrap;}
    .ric-pagine{display:flex;align-items:center;justify-content:center;gap:12px;
           padding:8px 0 2px;color:#6b7a72;font-size:13px;}
    .ric-pg{width:32px;height:32px;border-radius:50%;border:1px solid #e2e8e5;
           background:#fff;font-size:16px;font-weight:700;cursor:pointer;color:#16231d;}
    .ric-pg[disabled]{opacity:.35;cursor:default;}
    .ric-avviso{color:#6b7a72;padding:14px 4px;margin:0;}
    .ric-piede{border-top:1px dotted #cfd6d1;margin-top:10px;padding-top:8px;
           color:#6b7a72;line-height:1.45;}
</style>

<nav class="wcnav">
    <a class="wcnav-logo" href="{{ route('home') }}" aria-label="Home">
        <img src="{{ $logoNav }}" alt="FIFA WC History"
             onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'fallback',innerHTML:'FIFA <b>WC</b> History'}))">
    </a>
    <div class="wcnav-azioni">
        {{-- A3: ricerca globale --}}
        <button class="wcnav-lente" type="button" aria-label="Cerca">
            {!! \App\Support\Icons::svg('search') !!}
        </button>
        <button class="wcnav-burger" type="button" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

{{-- A3: barra di ricerca (entra da sinistra) e riquadro dei risultati --}}
<div class="wc-ricerca" id="wc-ricerca">
    <div class="ric-barra">
        <input type="search" id="wc-ricerca-campo" placeholder="Cerca in tutto il sito…"
               autocomplete="off" enterkeyhint="search">
        <button class="ric-chiudi" type="button" aria-label="Chiudi la ricerca">&times;</button>
    </div>
</div>
<div id="ricerca-overlay" hidden></div>
<div id="ricerca-risultati" hidden data-url="{{ route('ricerca') }}"></div>

<div id="wc-drawer-overlay"></div>
<aside id="wc-drawer" aria-label="Menu principale">
    <div class="drawer-acc">
        <div class="drawer-acc-head"><span>Squadre</span><span class="caret">▼</span></div>
        <div class="drawer-acc-corpo">
            @foreach ($navSquadre as $s)
                <a class="drawer-squadra" href="{{ route('squadra.show', $s->team_code) }}"
                   @if($s->flag) style="background-image:url('{{ $s->flag }}')" @endif>
                    <span class="velo">{{ $s->team_name }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="drawer-acc">
        <div class="drawer-acc-head"><span>Tornei</span><span class="caret">▼</span></div>
        <div class="drawer-acc-corpo">
            @foreach ($navTornei as $t)
                <a class="drawer-torneo" href="{{ route('torneo.show', $t->tournament_id) }}">
                    <img src="{{ $t->mini }}" alt="" onerror="this.style.visibility='hidden'">
                    <span>{{ $t->host_country }} {{ $t->year }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Spostata qui dalla home (14/08): era un link sotto i box "Scopri". --}}
    <a class="drawer-voce" href="{{ route('classifica') }}">Classifica perpetua</a>
    <a class="drawer-voce" href="{{ route('giocatori.index') }}">Giocatori</a>
    <a class="drawer-voce" href="{{ route('allenatori.index') }}">Manager</a>
    <a class="drawer-voce" href="{{ route('arbitri.index') }}">Arbitri</a>
    <a class="drawer-voce" href="{{ route('stadi.index') }}">Stadi</a>
    <a class="drawer-voce" href="{{ route('club.index') }}">Club</a>
    {{-- Voce temporanea: accesso rapido al pannello admin (da rimuovere in futuro) --}}
    <a class="drawer-voce" href="{{ url('admin') }}">Admin</a>
</aside>
