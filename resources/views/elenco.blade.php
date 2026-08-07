@extends('layouts.app')

@section('title', $titolo)

@section('content')
    <div class="team-head">
        <h1>{{ $titolo }}</h1>
    </div>

    {{-- ------------- Ricerca + elementi per pagina ------------- --}}
    <form class="barra-ricerca" method="get" action="{{ route($routeIndex) }}">
        <input type="search" name="q" value="{{ $q }}" placeholder="{{ $placeholder ?? 'Cerca per nome o cognome…' }}" autocomplete="off">
        <button type="submit">
            <img src="{{ route('img', ['tipo' => 'icons', 'file' => 'search.svg']) }}" alt="">
            Cerca
        </button>
        <label class="per-page">
            Per pagina:
            <select name="per_page" onchange="this.form.submit()">
                @foreach ([20, 50, 100] as $n)
                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                @endforeach
            </select>
        </label>
    </form>

    {{-- ------------- Elenco ------------- --}}
    <div id="tab-content">
        @if ($items->isEmpty())
            <p>Nessun risultato{{ $q !== '' ? ' per "'.$q.'"' : '' }}.</p>
        @else
            <div class="elenco">
                @foreach ($items as $item)
                    <a class="voce"
                       href="{{ route($routeShow, $item['id']) }}"
                       data-scheda="{{ route($routeScheda, $item['id']) }}">
                        <span class="nome">
                            @if (!empty($item['flag']))
                                <img class="flag-riga" src="{{ $item['flag'] }}" alt=""
                                     onerror="this.style.display='none'">
                            @else
                                <span class="flag-riga vuota"></span>
                            @endif
                            {{ $item['nome'] }}
                        </span>
                        @if (!empty($item['extra']))
                            <span class="extra">{{ $item['extra'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ------------- Paginazione ------------- --}}
    @if ($items->lastPage() > 1)
        <div class="paginazione">
            @if ($items->onFirstPage())
                <span class="pg disab">‹</span>
            @else
                <a class="pg" href="{{ $items->previousPageUrl() }}">‹</a>
            @endif

            <span class="pg-stato">Pagina {{ $items->currentPage() }} di {{ $items->lastPage() }}
                <small>({{ $items->total() }} elementi)</small></span>

            @if ($items->hasMorePages())
                <a class="pg" href="{{ $items->nextPageUrl() }}">›</a>
            @else
                <span class="pg disab">›</span>
            @endif
        </div>
    @endif

    {{-- ------------- Popup scheda (80% x 80%) ------------- --}}
    <div id="popup-overlay" hidden>
        <div id="popup" class="luce-bordo" role="dialog" aria-modal="true">
            <button id="popup-chiudi" type="button" aria-label="Chiudi">×</button>
            <div id="popup-corpo">Caricamento…</div>
        </div>
    </div>

    <style>
        .barra-ricerca { display:flex; align-items:center; gap:10px; flex-wrap:wrap;
                         margin-bottom:18px; }
        .barra-ricerca input[type=search] { flex:1; min-width:220px; font:inherit;
                         padding:9px 14px; border:1px solid var(--line);
                         border-radius:999px; background:#fff; outline-color:var(--accent); }
        .barra-ricerca button { font:inherit; cursor:pointer; display:flex; gap:7px;
                         align-items:center; padding:9px 18px; border-radius:999px;
                         border:1px solid var(--accent); background:var(--accent); color:#fff; }
        .barra-ricerca button img { width:15px; height:15px; filter:brightness(0) invert(1); }
        .barra-ricerca .per-page { color:var(--muted); font-size:13px; }
        .barra-ricerca select { font:inherit; padding:7px 10px; border-radius:8px;
                         border:1px solid var(--line); background:#fff; }

        .elenco .voce { display:flex; justify-content:space-between; gap:14px;
                        padding:10px 4px; border-bottom:1px solid var(--line);
                        color:var(--ink); }
        .elenco .voce:last-child { border-bottom:0; }
        .elenco .voce:hover { background:#f2f7f4; text-decoration:none; }
        .elenco .voce .nome { font-weight:600; color:var(--accent);
                        display:flex; align-items:center; gap:9px; min-width:0; }
        .elenco .voce .flag-riga { width:22px; height:22px; border-radius:50%; flex:none;
                        object-fit:cover; box-shadow:0 1px 2px rgba(0,0,0,.25); }
        .elenco .voce .flag-riga.vuota { box-shadow:none; background:#eef2ef; }
        .elenco .voce .extra { color:var(--muted); font-size:13px; white-space:nowrap; }

        .paginazione { display:flex; align-items:center; justify-content:center;
                       gap:14px; margin-top:20px; }
        .paginazione .pg { display:flex; align-items:center; justify-content:center;
                       width:38px; height:38px; border-radius:50%; background:#fff;
                       border:1px solid var(--line); font-size:18px; font-weight:700; }
        .paginazione .pg.disab { opacity:.35; }
        .paginazione .pg-stato { color:var(--muted); font-size:14px; }

        /* ---- Popup scheda: responsive (desktop centrato, mobile a schermo pieno) ---- */
        #popup-overlay { position:fixed; inset:0; background:rgba(10,20,15,.55);
                         display:flex; align-items:center; justify-content:center;
                         z-index:1300; }
        /* l'attributo hidden deve vincere sul display:flex qui sopra */
        #popup-overlay[hidden] { display:none; }
        #popup { position:relative; width:min(940px, 92vw); max-height:88dvh; background:#fff;
                 border-radius:14px; box-shadow:0 12px 40px rgba(0,0,0,.35);
                 display:flex; flex-direction:column; }
        #popup-chiudi { position:absolute; top:10px; right:12px; width:38px; height:38px;
                 border-radius:50%; border:1px solid var(--line); background:#fff;
                 font-size:20px; line-height:1; cursor:pointer; color:var(--ink); z-index:1;
                 box-shadow:0 1px 4px rgba(0,0,0,.18); }
        #popup-chiudi:hover { background:#f2f7f4; }
        #popup-corpo { flex:1; overflow:auto; -webkit-overflow-scrolling:touch;
                 padding:22px 26px; min-height:120px; }
        /* le tabelle larghe (gare giocate) scorrono in orizzontale, non tagliano */
        #popup-corpo table { display:block; overflow-x:auto; max-width:100%; }
        @media (max-width:640px) {
            #popup-overlay { align-items:stretch; justify-content:stretch; }
            #popup { width:100vw; max-height:none; height:100dvh; border-radius:0; }
            #popup-corpo { padding:16px 12px 28px; }
        }
    </style>

    <script>
    (function () {
        const overlay = document.getElementById('popup-overlay');
        const corpo   = document.getElementById('popup-corpo');
        const chiudiBtn = document.getElementById('popup-chiudi');

        function apri(url) {
            overlay.hidden = false;
            document.body.style.overflow = 'hidden';
            corpo.innerHTML = (window.WC && WC.splashHTML) ? WC.splashHTML() : 'Caricamento…';
            fetch(url)
                .then(r => r.ok ? r.text() : Promise.reject(r.status))
                .then(html => {
                    corpo.innerHTML = html;
                    // mappe nei frammenti (scheda stadio): i <script> non
                    // vengono eseguiti dall'innerHTML, si inizializza qui
                    if (window.WC && WC.initMappeStadio) WC.initMappeStadio(corpo);
                })
                .catch(() => { corpo.innerHTML = '<p class="err">Errore nel caricamento della scheda.</p>'; });
        }

        function chiudi() {
            overlay.hidden = true;
            document.body.style.overflow = '';
            corpo.innerHTML = '';
        }

        document.querySelectorAll('.elenco .voce').forEach(a => {
            a.addEventListener('click', function (e) {
                e.preventDefault();               // niente pagina nuova: apriamo il popup
                apri(a.dataset.scheda);
            });
        });

        chiudiBtn.addEventListener('click', chiudi);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) chiudi();   // click fuori dal popup
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !overlay.hidden) chiudi();
        });
    })();
    </script>
@endsection
