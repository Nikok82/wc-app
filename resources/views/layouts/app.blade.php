<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FIFA WC History')</title>
    <style>
        :root { --accent:#1b9e57; --ink:#16231d; --muted:#6b7a72; --line:#e2e8e5;
                --giallo:#ffff00; --verde:#1b9e57; --verde-scuro:#045e03; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;
               color:var(--ink); background:#f4f6f5; line-height:1.5; }
        a { color:var(--accent); text-decoration:none; }
        a:hover { text-decoration:underline; }
        .wrap { max-width:880px; margin:0 auto; padding:24px 16px 90px; }

        .team-head { display:flex; align-items:center; gap:16px; margin-bottom:20px; }
        .team-head .flag { width:88px; height:auto; border-radius:6px;
                           box-shadow:0 1px 5px rgba(0,0,0,.22); }
        .team-head h1 { font-size:28px; margin:0; letter-spacing:-.3px; }

        .tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;
                border-bottom:1px solid var(--line); padding-bottom:12px; }
        .tab-btn { font:inherit; cursor:pointer; padding:8px 16px; border-radius:999px;
                   border:1px solid var(--line); background:#fff; color:var(--ink); }
        .tab-btn.active { background:var(--accent); border-color:var(--accent); color:#fff; }
        .tab-btn:disabled { opacity:.45; cursor:not-allowed; }

        /* Variante a icone (pagina squadra e squadra-anno): stessa resa dei
           bottoni del torneo. flex-wrap:nowrap tiene la barra su una riga
           sola a qualsiasi larghezza; i bottoni si restringono in proporzione
           perche' hanno flex:1 e min-width:0. */
        .tabs.tabs-icone { flex-wrap:nowrap; gap:7px; }
        .tabs-icone .tab-btn { flex:1 1 0; min-width:0; max-width:64px; height:46px;
                   display:flex; align-items:center; justify-content:center;
                   padding:7px; border:0; border-radius:6px; color:var(--giallo);
                   background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
                   box-shadow:0 1px 3px rgba(0,0,0,.25); transition:filter .15s; }
        .tabs-icone .tab-btn:hover { filter:brightness(1.08); }
        .tabs-icone .tab-btn svg { width:26px; height:26px; max-width:100%;
                   color:var(--giallo); }
        .tabs-icone .tab-btn.active { border:1px solid var(--giallo);
                   box-shadow:0 0 .1rem var(--giallo),0 0 .5rem #08ff07ee;
                   background:linear-gradient(45deg,#045e03ee,#058404ee,#08ff07ee,#045e03ee); }
        @media (max-width:560px){
            .tabs-icone .tab-btn { height:42px; max-width:none; padding:5px; }
            .tabs-icone .tab-btn svg { width:22px; height:22px; }
        }

        #tab-content { background:#fff; border:1px solid var(--line);
                       border-radius:12px; padding:18px 20px; min-height:120px; }

        .info-grid { display:grid; gap:0; }
        .info-row { display:flex; justify-content:space-between; gap:16px;
                    padding:10px 2px; border-bottom:1px solid var(--line); }
        .info-row:last-child { border-bottom:0; }
        .info-row .lbl { color:var(--muted); text-transform:uppercase;
                         font-size:12px; letter-spacing:.5px; padding-top:2px; }
        .info-row .val { font-weight:600; text-align:right; }

        .footer-nav { display:flex; justify-content:space-between; gap:12px; margin-top:22px; }
        .footer-nav a { padding:8px 14px; background:#fff; border:1px solid var(--line);
                        border-radius:999px; font-weight:600; }
        .err { color:#b3261e; }
    </style>
    @include('partials.luce-bordo-css')
</head>
<body>
    @include('partials.navbar')
    <div class="wrap">
        @yield('content')
    </div>
    {{-- Barra bottoni globale (solo responsive). Le sezioni contestuali
         prev/next arriveranno dai controller come $barraPrev/$barraNext. --}}
    @include('partials.barra-bottoni', [
        'barraPrev' => $barraPrev ?? null,
        'barraNext' => $barraNext ?? null,
    ])
    {{-- A1: scorrimento laterale fra schede (solo dove i controller
         passano $swipeNav: squadra e squadra-anno). --}}
    @include('partials.swipe-schede', ['swipe' => $swipeNav ?? null])
    @include('partials.wc-assets')
</body>
</html>
