{{-- Tab Stadi del torneo (04/08): mappa con i marker di TUTTI gli stadi del
     torneo (wc.js: WC.initMappeStadio su .mappa-stadi[data-stadi]) + elenco
     con citta', capienza e numero di partite. Frammento via fetch: niente
     <script>; il popup scheda stadio e' gestito da wc.js (delega su
     .mgr-elenco .voce[data-scheda] + init mappa dopo il load del popup). --}}

<div class="std-wrap">
    <p class="prs-titolo">Stadi del Mondiale {{ $anno }} ({{ $stadi->count() }})</p>

    @if ($stadi->isEmpty())
        <p>Nessuno stadio trovato per questo torneo.</p>
    @else
        @if ($marker->isNotEmpty())
            <div class="mappa-stadi" data-stadi='@json($marker)'></div>
        @endif

        <div class="elenco mgr-elenco">
            @foreach ($stadi as $s)
                <div class="voce"
                     data-scheda="{{ route('stadio.scheda', $s->stadium_id) }}"
                     data-href="{{ route('stadio.show', $s->stadium_id) }}"
                     role="button" tabindex="0">
                    <span class="nome">{{ $s->stadium_name }}
                        <small class="std-citta">{{ $s->city_name }}</small>
                    </span>
                    <span class="extra">
                        @if ($s->stadium_capacity){{ number_format($s->stadium_capacity, 0, ',', '.') }} posti · @endif{{ $s->partite }} {{ $s->partite == 1 ? 'partita' : 'partite' }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Popup scheda stadio (aperto da wc.js, spostato su <body>) --}}
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
    .std-wrap .mappa-stadi{width:100%;height:320px;border-radius:10px;overflow:hidden;
        border:1px solid var(--line,#e2e8e5);background:#eef2ef;margin-bottom:16px;}
    .std-wrap .elenco .voce{display:flex;justify-content:space-between;gap:14px;
        padding:10px 4px;border-bottom:1px solid var(--line,#e2e8e5);color:var(--ink,#16231d);
        cursor:pointer;align-items:center;}
    .std-wrap .elenco .voce:last-child{border-bottom:0;}
    .std-wrap .elenco .voce:hover{background:#f2f7f4;}
    .std-wrap .elenco .voce .nome{font-weight:600;color:var(--verde,#045e03);
        display:flex;align-items:baseline;gap:9px;min-width:0;flex-wrap:wrap;}
    .std-wrap .elenco .voce .std-citta{color:var(--muted,#6b7a72);font-weight:600;
        font-size:13px;}
    .std-wrap .elenco .voce .extra{color:var(--muted,#6b7a72);font-size:13px;
        white-space:nowrap;}
    @media (max-width:560px){ .std-wrap .mappa-stadi{height:240px;} }

    /* Popup: stesse regole responsive delle altre tab */
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
