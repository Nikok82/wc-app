<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FIFA WC History')</title>
    <style>
        :root{
            --verde:#045e03; --verde2:#57c785; --verde-scuro:#0f6c14;
            --giallo:#ffff00; --ink:#16231d; --muted:#6b7a72; --line:#e2e8e5;
        }
        *{box-sizing:border-box;}

        /* ---- Animazioni neon (bordo contenuti + bottone attivo) ---- */
        @property --border-angle{syntax:"<angle>";initial-value:0deg;inherits:false;}
        @keyframes border-angle-rotate{0%{--border-angle:0deg}100%{--border-angle:360deg}}
        @keyframes gradient{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
        body{margin:0;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;
             color:var(--ink);background:#f4f6f5;line-height:1.5;}
        a{color:var(--verde);text-decoration:none;}
        a:hover{text-decoration:underline;}

        .torneo-wrap{max-width:920px;margin:0 auto;padding:18px 14px 96px;position:relative;overflow:hidden;}

        /* Sfondo manifesto del torneo, sfumato e ruotato */
        .torneo-bg{position:absolute;z-index:0;top:-30px;left:-5%;width:110%;height:280px;
                   background-size:cover;background-position:center;transform:rotate(3deg);
                   opacity:.28;filter:saturate(1.1);pointer-events:none;}

        /* Titolo "Italia '90" */
        .torneo-title{position:relative;z-index:2;text-align:right;margin:6px 4px 14px;
                      font-size:2.6rem;font-weight:800;color:var(--giallo);
                      text-shadow:2px 2px 0 var(--verde),0 1px 6px rgba(0,0,0,.35);letter-spacing:-.5px;}

        /* Barra dei tab: pulsanti verdi con icona */
        .buttons-box{position:relative;z-index:2;display:flex;flex-direction:row;gap:7px;
                     padding:6px 0 18px;flex-wrap:wrap;}
        .buttons-box .button{display:flex;align-items:center;justify-content:center;
                     flex:1;max-width:64px;height:46px;padding:7px;border:0;cursor:pointer;
                     border-radius:6px;color:var(--giallo);
                     background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
                     box-shadow:0 1px 3px rgba(0,0,0,.25);transition:filter .15s;}
        .buttons-box .button:hover{filter:brightness(1.08);}
        .buttons-box .button svg{width:26px;height:26px;color:var(--giallo);}
        .buttons-box .button.active{border:1px solid var(--giallo);
                     box-shadow:0 0 .1rem var(--giallo),0 0 .1rem var(--giallo),
                                0 0 .5rem #08ff07ee,0 0 .5rem #08ff07ee;
                     background:linear-gradient(45deg,#045e03ee,#058404ee,#08ff07ee,#045e03ee);
                     background-size:300% 300%;
                     animation:gradient 10s infinite linear;}

        /* Contenitore contenuti tab */
        #tab-content{position:relative;z-index:2;--border-angle:0deg;
                     border:4px solid transparent;
                     border-radius:14px;padding:16px 16px 22px;min-height:160px;
                     box-shadow:0 1px 6px rgba(0,0,0,.05);
                     background:linear-gradient(#fff,#fff) padding-box,
                        conic-gradient(from var(--border-angle),
                            #045e03 0%,#045e03,#058404,#08ff07,#045e03) border-box;
                     animation:border-angle-rotate 2s infinite linear;}
        @media (prefers-reduced-motion:reduce){
            #tab-content,.buttons-box .button.active{animation:none;}
        }
        .err{color:#b3261e;}
        .caric{color:var(--muted);padding:20px 4px;}

        /* ---- Podio ---- */
        .podio{width:100%;max-width:420px;margin:4px auto 22px;height:190px;display:flex;
               flex-direction:row;justify-content:center;align-items:flex-end;gap:6px;}
        .podio .posto{display:flex;flex-direction:column;justify-content:flex-end;flex:1;max-width:120px;}
        .podio .team{display:flex;align-items:flex-end;justify-content:center;padding:8px 4px;}
        .podio .container-flag-podium{position:relative;display:inline-block;width:64px;}
        .podio .container-flag-podium .flag{width:64px;height:auto;border-radius:4px;
               box-shadow:0 1px 5px rgba(0,0,0,.3);display:block;}
        .podio .container-flag-podium .info-link{position:absolute;top:-9px;right:-9px;
               width:20px;height:20px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.3);
               display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--verde);}
        .podio .bar{display:flex;align-items:flex-start;justify-content:center;padding:8px;
               font-size:1.5rem;font-weight:800;color:#00000088;border-radius:4px 4px 0 0;
               text-shadow:1px 1px 0 #ffffff55;}
        .podio .nome{font-size:.72rem;text-align:center;color:var(--ink);margin-top:4px;
               font-weight:600;line-height:1.1;}
        .podio .primo   .bar{background:#ffd700;height:90px;}
        .podio .secondo .bar{background:#c0c0c0;height:62px;}
        .podio .terzo   .bar{background:#b08d57;height:40px;}

        /* ---- Righe info + premi ---- */
        .info_box{display:flex;flex-direction:column;width:100%;}
        .single_info_box{display:flex;flex-direction:row;justify-content:space-between;gap:12px;
               padding:10px 4px;border-bottom:1px solid var(--line);}
        .single_info_box:last-child{border-bottom:0;}
        .single_info_box .left{color:var(--muted);text-transform:uppercase;font-size:12px;
               letter-spacing:.4px;padding-top:2px;flex:1;}
        .single_info_box .right{font-weight:600;text-align:right;flex:1;
               display:flex;justify-content:flex-end;align-items:center;gap:6px;flex-wrap:wrap;}
        .single_info_box.capital .left{text-transform:none;}
        .titolo-sezione{font-size:1.05rem;font-weight:700;color:var(--verde-scuro);
               margin:2px 0 12px;padding-bottom:8px;border-bottom:2px solid var(--verde2);}
        .premio-vincitore{display:inline-flex;align-items:center;gap:6px;}
        .premio-vincitore .flag{width:26px;height:auto;border-radius:3px;
               box-shadow:0 1px 2px rgba(0,0,0,.25);}
        .premio-vincitore + .premio-vincitore{margin-left:10px;}

        @media (max-width:560px){
            .torneo-title{font-size:1.9rem;}
            .buttons-box .button{height:42px;max-width:none;}
            .podio{height:170px;}
        }
    </style>
</head>
<body>
    @include('partials.navbar')
    @yield('content')
    {{-- Barra bottoni globale (solo responsive). Sostituisce il vecchio
         .torneo-footer: nelle sezioni contestuali passano le locandine del
         Mondiale precedente/successivo ($prev/$next dal TorneoController). --}}
    @include('partials.barra-bottoni', [
        'barraPrev' => (isset($prev) && $prev) ? [
            'url'   => $prev['url'],
            'img'   => $prev['manifest'] ?? null,
            'label' => 'Mondiale ' . $prev['year'],
        ] : null,
        'barraNext' => (isset($next) && $next) ? [
            'url'   => $next['url'],
            'img'   => $next['manifest'] ?? null,
            'label' => 'Mondiale ' . $next['year'],
        ] : null,
    ])
    @include('partials.wc-assets')
</body>
</html>
