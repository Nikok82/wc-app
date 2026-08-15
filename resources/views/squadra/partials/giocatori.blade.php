{{-- Tab Giocatori della scheda squadra: tutti i convocati della nazionale in
     tutti i Mondiali. Frammento via fetch: niente <script>; ordinamento,
     filtro e paginazione sono gestiti da wc.js (delega su .gioc-wrap:
     th[data-sort] della .conv-table, input .gioc-filtro, select .gioc-perpage,
     bottoni .gioc-pg). Ordine iniziale: cognome crescente (server-side). --}}

@if ($giocatori->isEmpty())
    <p>Nessun giocatore trovato per questa nazionale.</p>
@else
    <div class="gioc-wrap" data-page="1" data-perpage="10">

        {{-- Filtro + elementi per pagina --}}
        <div class="gioc-barra">
            <input type="search" class="gioc-filtro" autocomplete="off"
                   placeholder="Filtra per nome, cognome, mondiale, club o ruolo…">
            <label class="gioc-pp">
                Per pagina:
                <select class="gioc-perpage">
                    @foreach ([10, 25, 50, 75, 100, 200] as $n)
                        <option value="{{ $n }}" @selected($n === 10)>{{ $n }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="conv-scroll">
            <table class="conv-table gioc-table">
                <thead>
                    <tr>
                        <th class="c-ico"></th>
                        <th data-sort="nome" title="Ordina per nome">Nome</th>
                        <th class="asc" data-sort="cognome" title="Ordina per cognome">Cognome</th>
                        <th data-sort="nascita" title="Ordina per data di nascita">Nascita</th>
                        <th class="c-num" data-sort="ruolo" title="Ordina per ruolo (P,D,C,A / A,C,D,P)">Ruolo</th>
                        <th class="c-num" data-sort="mondiali" title="Numero di Mondiali giocati">Mondiali</th>
                        <th class="c-num" data-sort="pg" title="Partite giocate con questa nazionale">PG</th>
                        <th class="c-num" data-sort="gol" title="Gol fatti con questa nazionale">Gol</th>
                        <th>Mondiali giocati</th>
                        <th>Club</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($giocatori as $g)
                        @php
                            // Testo di ricerca: nome, cognome, anni dei mondiali,
                            // club e ruolo (lettere + parole intere)
                            $parole = ['P' => 'portiere', 'D' => 'difensore', 'C' => 'centrocampista', 'A' => 'attaccante'];
                            $ruoloParole = collect(explode('/', (string) $g['ruolo']))
                                ->map(fn ($l) => $parole[$l] ?? '')->implode(' ');
                            $cerca = mb_strtolower(trim(
                                ($g['given'] ?? '').' '.($g['family'] ?? '').' '
                                .$g['mondiali']->pluck('anno')->implode(' ').' '
                                .$g['club']->pluck('nome')->implode(' ').' '
                                .($g['ruolo'] ?? '').' '.$ruoloParole
                            ));
                        @endphp
                        <tr data-nome="{{ mb_strtolower($g['given'] ?? '') }}"
                            data-cognome="{{ mb_strtolower($g['family'] ?? '') }}"
                            data-nascita="{{ $g['nascita'] ? $g['nascita']->format('Y-m-d') : '' }}"
                            data-ruolo="{{ $g['ruolo_ord'] }}"
                            data-mondiali="{{ $g['n_mondiali'] }}"
                            data-pg="{{ $g['pg'] }}"
                            data-gol="{{ $g['gol'] }}"
                            data-search="{{ $cerca }}">
                            <td class="c-ico">
                                @if ($g['player_id'])
                                    <a href="{{ route('giocatore.show', $g['player_id']) }}" title="Scheda giocatore">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
                                            <path d="M12 12c2.7 0 4.8-2.2 4.8-4.9S14.7 2.3 12 2.3 7.2 4.4 7.2 7.1 9.3 12 12 12zm0 2.2c-3.6 0-9 1.8-9 5.4v2.1h18v-2.1c0-3.6-5.4-5.4-9-5.4z"/>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                            <td>{{ $g['given'] }}</td>
                            <td class="c-cognome">{{ $g['family'] }}</td>
                            <td class="c-nascita">
                                @if ($g['nascita'])
                                    {{ $g['nascita']->format('d/m/Y') }}@if($g['eta'] !== null) ({{ $g['eta'] }})@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="c-num">{{ $g['ruolo'] ?? '—' }}</td>
                            <td class="c-num">{{ $g['n_mondiali'] }}</td>
                            <td class="c-num">{{ $g['pg'] }}</td>
                            <td class="c-num">{{ $g['gol'] }}</td>
                            <td class="c-mond">
                                @foreach ($g['mondiali'] as $m)
                                    <a href="{{ route('squadra_anno.show', ['code' => $code, 'year' => $m['anno']]) }}">{{ $m['anno'] }}@if($m['maglia']) (#{{ $m['maglia'] }})@endif</a>@if(! $loop->last)<span class="sep"> - </span>@endif
                                @endforeach
                            </td>
                            <td class="c-clubs">
                                {{-- D1 (15/08): lo stemma porta alla scheda del
                                     club, ora che la sezione esiste. Senza id
                                     (nome scritto solo in team_past) resta
                                     un'immagine muta. --}}
                                @forelse ($g['club'] as $c)
                                    @if ($c['logo'])
                                        @if (!empty($c['id']))
                                            <a href="{{ route('club.show', $c['id']) }}" title="{{ $c['nome'] }}"><img
                                                 src="{{ $c['logo'] }}" alt="{{ $c['nome'] }}"
                                                 width="18" height="18" loading="lazy"
                                                 onerror="this.style.display='none'"></a>
                                        @else
                                            <img src="{{ $c['logo'] }}" alt="{{ $c['nome'] }}" title="{{ $c['nome'] }}"
                                                 width="18" height="18" loading="lazy" onerror="this.style.display='none'">
                                        @endif
                                    @endif
                                @empty
                                    —
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginazione (client-side, stile della paginazione degli elenchi) --}}
        <div class="paginazione gioc-paginazione">
            <button type="button" class="pg gioc-pg" data-dir="-1" aria-label="Pagina precedente">‹</button>
            <span class="pg-stato gioc-stato"></span>
            <button type="button" class="pg gioc-pg" data-dir="1" aria-label="Pagina successiva">›</button>
        </div>
    </div>

    <style>
        .gioc-barra{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
        .gioc-barra .gioc-filtro{flex:1;min-width:220px;font:inherit;padding:9px 14px;
            border:1px solid var(--line);border-radius:999px;background:#fff;outline-color:var(--accent);}
        .gioc-barra .gioc-pp{color:var(--muted);font-size:13px;white-space:nowrap;}
        .gioc-barra select{font:inherit;padding:7px 10px;border-radius:8px;
            border:1px solid var(--line);background:#fff;}

        .conv-scroll{overflow-x:auto;margin:0 -8px;padding:0 8px;}
        .conv-table{width:100%;border-collapse:collapse;font-size:13px;min-width:860px;}
        .conv-table th{position:sticky;top:0;background:#f4f6f5;color:var(--muted);
            text-transform:uppercase;font-size:11px;letter-spacing:.4px;text-align:left;
            padding:8px 6px;border-bottom:2px solid var(--line);white-space:nowrap;}
        .conv-table th[data-sort]{cursor:pointer;user-select:none;}
        .conv-table th[data-sort]:hover{color:var(--ink);}
        .conv-table th[data-sort]::after{content:'↕';opacity:.35;margin-left:4px;font-size:10px;}
        .conv-table th[data-sort].asc::after{content:'↑';opacity:1;}
        .conv-table th[data-sort].desc::after{content:'↓';opacity:1;}
        .conv-table td{padding:7px 6px;border-bottom:1px solid var(--line);vertical-align:middle;}
        .conv-table tbody tr:last-child td{border-bottom:0;}
        .conv-table .c-num{text-align:center;}
        .conv-table .c-ico{width:24px;text-align:center;}
        .conv-table .c-ico a{color:var(--accent);display:inline-flex;}
        .conv-table .c-cognome{font-weight:700;}
        .conv-table .c-nascita{white-space:nowrap;}
        .gioc-table .c-mond{max-width:280px;}
        .gioc-table .c-mond a{white-space:nowrap;}
        .gioc-table .c-mond .sep{color:var(--muted);}
        .gioc-table .c-clubs{white-space:nowrap;}
        .gioc-table .c-clubs img{width:18px;height:18px;object-fit:contain;
            vertical-align:middle;margin-right:4px;}

        .gioc-paginazione .pg{font:inherit;cursor:pointer;color:var(--ink);}
        .gioc-paginazione .pg:disabled{opacity:.35;cursor:default;}
        .paginazione{display:flex;align-items:center;justify-content:center;
            gap:14px;margin-top:20px;}
        .paginazione .pg{display:flex;align-items:center;justify-content:center;
            width:38px;height:38px;border-radius:50%;background:#fff;
            border:1px solid var(--line);font-size:18px;font-weight:700;}
        .paginazione .pg-stato{color:var(--muted);font-size:14px;}
    </style>
@endif
