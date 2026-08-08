<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FIFA WC History</title>
    <style>
        :root{--verde:#045e03;--verde2:#57c785;--verde-scuro:#0f6c14;--giallo:#ffff00;
              --ink:#16231d;--muted:#6b7a72;--line:#e2e8e5;}
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;
             color:var(--ink);line-height:1.5;
             background:linear-gradient(160deg,#0f6c14 0%,#045e03 45%,#032f02 100%);}
        .home-main{display:flex;align-items:flex-start;justify-content:center;padding:32px 16px 60px;}
        .home-card{width:100%;max-width:560px;background:#fff;border-radius:18px;
                   box-shadow:0 12px 40px rgba(0,0,0,.35);padding:26px 22px 30px;}
        .home-head{text-align:center;margin-bottom:20px;}
        .home-head h1{margin:0;font-size:1.7rem;letter-spacing:-.3px;color:var(--verde);}
        .home-head p{margin:4px 0 0;color:var(--muted);font-size:.9rem;}
        .home-badge{display:inline-block;margin-top:8px;font-size:.72rem;text-transform:uppercase;
                    letter-spacing:.5px;color:#fff;background:var(--verde2);padding:3px 10px;border-radius:999px;}

        .menu{display:flex;flex-direction:column;gap:12px;margin-top:8px;}
        .menu label{display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;
                    color:var(--muted);margin:0 0 4px 4px;}
        .menu select,.menu a.voce{width:100%;font:inherit;border-radius:10px;
                    border:1px solid var(--line);padding:13px 14px;background:#fff;color:var(--ink);
                    cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;}
        .menu select{appearance:none;background-image:linear-gradient(45deg,transparent 50%,var(--verde) 50%),
                     linear-gradient(135deg,var(--verde) 50%,transparent 50%);
                     background-position:calc(100% - 18px) 50%,calc(100% - 12px) 50%;
                     background-size:6px 6px,6px 6px;background-repeat:no-repeat;}
        .menu a.voce{font-weight:700;color:#fff;border:0;
                     background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
                     text-decoration:none;box-shadow:0 1px 4px rgba(0,0,0,.2);}
        .menu a.voce:hover{filter:brightness(1.08);text-decoration:none;}
        .menu a.voce .freccia{color:var(--giallo);font-weight:800;}
        .gruppo{background:#f7faf8;border:1px solid var(--line);border-radius:12px;padding:12px 12px 14px;}

        /* ---- Box "Scopri una nazionale" / "Scopri un torneo" (03/08) ---- */
        .scopri{--luce-bg:#f7faf8;padding:0;overflow:hidden;}
        .scopri-testata{background:#fff;border-bottom:2px solid var(--line);border-radius:10px 10px 0 0;
                        text-align:center;font-weight:700;font-size:1.05rem;color:var(--muted);
                        padding:10px 12px;letter-spacing:.2px;}
        .scopri-link{display:block;padding:14px 12px 16px;text-align:center;color:inherit;
                     text-decoration:none;}
        .scopri-link:hover{text-decoration:none;filter:brightness(1.02);}
        .scopri-nome{font-size:1.45rem;font-weight:800;color:var(--ink);letter-spacing:-.3px;
                     line-height:1.15;margin-bottom:10px;}
        .scopri-flag{width:150px;max-width:60%;height:auto;border-radius:6px;
                     box-shadow:0 2px 8px rgba(0,0,0,.28);}
        .scopri-stat{margin:14px auto 0;border-collapse:collapse;font-size:.85rem;}
        .scopri-stat th,.scopri-stat td{border:1px solid #222;padding:4px 9px;text-align:center;
                     font-weight:700;min-width:34px;}
        .scopri-stat th{background:#16231d;color:#fff;}
        .scopri-stat td{background:#fff;}
        .scopri-stat .st-v{background:#1faf1f;color:#fff;}
        .scopri-stat .st-n{background:#ffe33d;color:#333;}
        .scopri-stat .st-p{background:#d21f1f;color:#fff;}
        .scopri-medaglie{margin-top:12px;display:flex;justify-content:center;gap:6px;}
        .box-med{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;
                 border-radius:50%;color:#fff;font-size:12px;font-weight:800;
                 box-shadow:0 1px 3px rgba(0,0,0,.3);}
        .box-med.oro{background:#D6BD50;} .box-med.argento{background:#999b9b;} .box-med.bronzo{background:#CD7F32;}
        .scopri-torneo-testa{display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;}
        .scopri-torneo-host{display:flex;flex-direction:column;align-items:center;gap:8px;}
        .scopri-flag-host{width:110px;height:auto;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.28);}
        .scopri-torneo-host .scopri-nome{margin-bottom:0;}
        .scopri-manifesto{width:110px;height:auto;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.25);
                     background:#fff;}
        .scopri-podio{display:flex;justify-content:center;align-items:flex-end;gap:5px;margin-top:16px;}
        .scopri-podio .posto{display:flex;flex-direction:column;justify-content:flex-end;flex:0 0 72px;}
        .scopri-podio .team{display:flex;align-items:flex-end;justify-content:center;padding:4px 2px 6px;}
        .scopri-podio .team .flag{width:44px;height:auto;border-radius:4px;
                     box-shadow:0 1px 4px rgba(0,0,0,.3);}
        .scopri-podio .bar{display:flex;align-items:flex-start;justify-content:center;padding:5px;
                     font-size:1.15rem;font-weight:800;color:#00000088;border-radius:4px 4px 0 0;
                     text-shadow:1px 1px 0 #ffffff55;}
        .scopri-podio .primo   .bar{background:#ffd700;height:62px;}
        .scopri-podio .secondo .bar{background:#c0c0c0;height:42px;}
        .scopri-podio .terzo   .bar{background:#b08d57;height:28px;}
        .scopri-altro{display:block;width:100%;font:inherit;font-weight:700;color:#fff;border:0;
                     cursor:pointer;padding:11px 14px;border-radius:0 0 10px 10px;
                     background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
                     box-shadow:0 -1px 4px rgba(0,0,0,.12);}
        .scopri-altro:hover{filter:brightness(1.08);}
        .scopri-caric{opacity:.45;pointer-events:none;transition:opacity .15s;}
    </style>
    @include('partials.luce-bordo-css')
</head>
<body>
    @include('partials.navbar')
    <main class="home-main">
    <div class="home-card luce-bordo">
        <div class="home-head">
            <h1>FIFA WC History</h1>
            <p>Storia dei Mondiali di calcio</p>
            <span class="home-badge">Home provvisoria</span>
        </div>

        <div class="menu">
            {{-- Dropdown Squadre/Tornei rimossi (08/08): la navigazione resta
                 nel drawer del menu (hamburger) e nei box "Scopri" qui sotto. --}}

            {{-- Box casuali (03/08): squadra e torneo a sorte, uno sopra
                 l'altro sopra i link; il bottone ri-estrae via fetch. --}}
            <div class="gruppo scopri luce-bordo">
                <div class="scopri-testata">Scopri una nazionale</div>
                <div class="scopri-corpo" id="box-squadra">
                    @include('partials.box-squadra', ['box' => $boxSquadra])
                </div>
                <button type="button" class="scopri-altro" data-target="box-squadra"
                        data-url="{{ route('home.box.squadra') }}">Mostra un'altra squadra</button>
            </div>

            <div class="gruppo scopri luce-bordo">
                <div class="scopri-testata">Scopri un torneo</div>
                <div class="scopri-corpo" id="box-torneo">
                    @include('partials.box-torneo', ['box' => $boxTorneo])
                </div>
                <button type="button" class="scopri-altro" data-target="box-torneo"
                        data-url="{{ route('home.box.torneo') }}">Mostra un altro torneo</button>
            </div>

            <a class="voce" href="{{ route('classifica') }}">Classifica perpetua <span class="freccia">›</span></a>
            <a class="voce" href="{{ route('giocatori.index') }}">Giocatori <span class="freccia">›</span></a>
            <a class="voce" href="{{ route('allenatori.index') }}">Manager <span class="freccia">›</span></a>
            <a class="voce" href="{{ route('arbitri.index') }}">Arbitri <span class="freccia">›</span></a>
            <a class="voce" href="{{ route('stadi.index') }}">Stadi <span class="freccia">›</span></a>
        </div>
    </div>
    </main>
    {{-- Barra bottoni globale (solo responsive): in home niente contesto,
         restano i 4 bottoni centrali. --}}
    @include('partials.barra-bottoni', ['barraPrev' => null, 'barraNext' => null])
    {{-- wc.js (drawer hamburger, effetto luce…): mancava, il menu in home
         non si apriva. Da includere in fondo come negli altri layout. --}}
    @include('partials.wc-assets')
</body>
</html>
