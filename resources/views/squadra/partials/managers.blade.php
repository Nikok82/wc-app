{{-- Tab Managers della scheda squadra: gli allenatori della nazionale, come
     l'elenco generale (/allenatori) ma con l'elenco dei Mondiali in cui hanno
     allenato QUESTA nazionale al posto della bandiera. Ordine iniziale:
     primo Mondiale crescente. Frammento via fetch: niente <script>; il popup
     scheda e' gestito da wc.js (delega su .mgr-elenco .voce[data-scheda];
     il popup viene spostato su <body> all'apertura). --}}

@if ($managers->isEmpty())
    <p>Nessun allenatore trovato per questa nazionale.</p>
@else
    <div class="elenco mgr-elenco">
        @foreach ($managers as $m)
            <div class="voce"
                 data-scheda="{{ route('allenatore.scheda', $m['id']) }}"
                 data-href="{{ route('allenatore.show', $m['id']) }}"
                 role="button" tabindex="0">
                <span class="nome">
                    <span class="mgr-anni">
                        @foreach ($m['anni'] as $anno)
                            <a href="{{ route('squadra_anno.show', ['code' => $code, 'year' => $anno]) }}"
                               title="{{ $anno }}: scheda {{ $code }}-{{ $anno }}">{{ $anno }}</a>@if(! $loop->last)<span class="sep">, </span>@endif
                        @endforeach
                    </span>
                    {{ $m['nome'] }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- Popup scheda allenatore (aperto da wc.js, spostato su <body>) --}}
    <div id="popup-overlay" class="mgr-popup-overlay" hidden>
        <div id="popup" class="luce-bordo" role="dialog" aria-modal="true">
            <button id="popup-chiudi" type="button" aria-label="Chiudi">×</button>
            <div id="popup-corpo">Caricamento…</div>
        </div>
    </div>

    <style>
        .elenco .voce{display:flex;justify-content:space-between;gap:14px;
            padding:10px 4px;border-bottom:1px solid var(--line);color:var(--ink);
            cursor:pointer;}
        .elenco .voce:last-child{border-bottom:0;}
        .elenco .voce:hover{background:#f2f7f4;text-decoration:none;}
        .elenco .voce .nome{font-weight:600;color:var(--accent);
            display:flex;align-items:center;gap:12px;min-width:0;flex-wrap:wrap;}
        .mgr-elenco .mgr-anni{color:var(--muted);font-size:13px;font-weight:600;
            white-space:nowrap;}
        .mgr-elenco .mgr-anni a{color:var(--muted);text-decoration:underline;}
        .mgr-elenco .mgr-anni a:hover{color:var(--accent);}
        .mgr-elenco .mgr-anni .sep{margin-right:2px;}

        /* Popup: stesse regole responsive dell'elenco generale */
        .mgr-popup-overlay{position:fixed;inset:0;background:rgba(10,20,15,.55);
            display:flex;align-items:center;justify-content:center;z-index:1300;}
        .mgr-popup-overlay[hidden]{display:none;}
        .mgr-popup-overlay #popup{position:relative;width:min(940px,92vw);max-height:88dvh;
            background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.35);
            display:flex;flex-direction:column;}
        .mgr-popup-overlay #popup-chiudi{position:absolute;top:10px;right:12px;width:38px;
            height:38px;border-radius:50%;border:1px solid var(--line);background:#fff;
            font-size:20px;line-height:1;cursor:pointer;color:var(--ink);z-index:1;
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
@endif
