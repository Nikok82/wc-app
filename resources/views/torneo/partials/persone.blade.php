{{-- Tab Arbitri / Managers del torneo (04/08): elenco condiviso nello stile
     della tab Managers della scheda squadra. Frammento via fetch: niente
     <script>; il popup scheda e' gestito da wc.js (delega su
     .mgr-elenco .voce[data-scheda], popup spostato su <body>).
     Variabili: $titolo, $vuoto, $persone (id, nome, extra, flag, extra_url?),
     $routeScheda, $routeShow. --}}

<div class="prs-wrap">
    <p class="prs-titolo">{{ $titolo }} ({{ $persone->count() }})</p>

    @if ($persone->isEmpty())
        <p>{{ $vuoto }}</p>
    @else
        <div class="elenco mgr-elenco">
            @foreach ($persone as $p)
                <div class="voce"
                     data-scheda="{{ route($routeScheda, $p['id']) }}"
                     data-href="{{ route($routeShow, $p['id']) }}"
                     role="button" tabindex="0">
                    <span class="nome">
                        @if (!empty($p['flag']))
                            <img class="flag flag-riga" src="{{ $p['flag'] }}" alt=""
                                 onerror="this.style.display='none'">
                        @else
                            <span class="flag-riga vuota"></span>
                        @endif
                        {{ $p['nome'] }}
                    </span>
                    @if (!empty($p['extra']))
                        <span class="extra">
                            @if (!empty($p['extra_url']))
                                <a href="{{ $p['extra_url'] }}">{{ $p['extra'] }}</a>
                            @else
                                {{ $p['extra'] }}
                            @endif
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Popup scheda (aperto da wc.js, spostato su <body>) --}}
        <div id="popup-overlay" class="mgr-popup-overlay" hidden>
            <div id="popup" class="luce-bordo" role="dialog" aria-modal="true">
                <button id="popup-chiudi" type="button" aria-label="Chiudi">×</button>
                <div id="popup-corpo">Caricamento…</div>
            </div>
        </div>
    @endif
</div>

<style>
    .prs-titolo{font-size:1.05rem;font-weight:700;color:var(--verde-scuro,#0f6c14);
        margin:2px 0 12px;padding-bottom:8px;border-bottom:2px solid var(--verde2,#57c785);}
    .prs-wrap .elenco .voce{display:flex;justify-content:space-between;gap:14px;
        padding:10px 4px;border-bottom:1px solid var(--line,#e2e8e5);color:var(--ink,#16231d);
        cursor:pointer;align-items:center;}
    .prs-wrap .elenco .voce:last-child{border-bottom:0;}
    .prs-wrap .elenco .voce:hover{background:#f2f7f4;}
    .prs-wrap .elenco .voce .nome{font-weight:600;color:var(--verde,#045e03);
        display:flex;align-items:center;gap:9px;min-width:0;}
    .prs-wrap .elenco .voce .flag-riga{width:22px;height:22px;border-radius:50%;flex:none;
        object-fit:cover;box-shadow:0 1px 2px rgba(0,0,0,.25);}
    .prs-wrap .elenco .voce .flag-riga.vuota{box-shadow:none;background:#eef2ef;
        display:inline-block;}
    .prs-wrap .elenco .voce .extra{color:var(--muted,#6b7a72);font-size:13px;
        white-space:nowrap;}
    .prs-wrap .elenco .voce .extra a{color:var(--muted,#6b7a72);text-decoration:underline;}
    .prs-wrap .elenco .voce .extra a:hover{color:var(--verde,#045e03);}

    /* Popup: stesse regole responsive della tab Managers della squadra */
    .mgr-popup-overlay{position:fixed;inset:0;background:rgba(10,20,15,.55);
        display:flex;align-items:center;justify-content:center;z-index:1300;}
    .mgr-popup-overlay[hidden]{display:none;}
    .mgr-popup-overlay #popup{position:relative;width:min(940px,92vw);max-height:88dvh;
        background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.35);
        display:flex;flex-direction:column;}
    .mgr-popup-overlay #popup-chiudi{position:absolute;top:10px;right:12px;width:38px;
        height:38px;border-radius:50%;border:1px solid var(--line,#e2e8e5);background:#fff;
        font-size:20px;line-height:1;cursor:pointer;color:var(--ink,#16231d);z-index:1;
        box-shadow:0 1px 4px rgba(0,0,0,.18);}
    .mgr-popup-overlay #popup-chiudi:hover{background:#f2f7f4;}
    .mgr-popup-overlay #popup-corpo{flex:1;overflow:auto;-webkit-overflow-scrolling:touch;
        padding:22px 26px;min-height:120px;}
    .mgr-popup-overlay #popup-corpo table{display:block;overflow-x:auto;max-width:100%;}
    @media (max-width:640px){
        .mgr-popup-overlay{align-items:stretch;justify-content:stretch;}
        .mgr-popup-overlay #popup{width:100vw;max-height:none;height:100dvh;border-radius:0;}
        .mgr-popup-overlay #popup-corpo{padding:16px 12px 28px;}
    }
</style>
