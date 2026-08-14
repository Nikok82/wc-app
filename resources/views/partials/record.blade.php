@php
    /**
     * Tab Record. Riceve $rec dal RecordService e, opzionalmente,
     * $recTitolo per l'intestazione.
     */
    $voci = $rec['voci'] ?? [];
@endphp

@if (($rec['vuoto'] ?? true) || empty($voci))
    <p>Nessun dato disponibile.</p>
@else

    @if ($rec['senza_dati'] ?? false)
        <p class="rec-avviso">
            Cartellini e sostituzioni sono registrati soltanto dal 1970 in poi:
            per le edizioni precedenti i primati relativi restano a zero.
        </p>
    @endif

    <div class="rec-griglia">
        @foreach ($voci as $v)
            <div class="rec-card rec-{{ $v['chiave'] }}">
                <div class="rec-tit">{{ $v['etichetta'] }}</div>

                {{-- Valore principale --}}
                @if (!is_null($v['valore'] ?? null))
                    <div class="rec-val">{{ $v['valore'] }}</div>
                @elseif (!empty($v['classifica']))
                    {{-- niente valore singolo: parla la classifica --}}
                @else
                    <div class="rec-val rec-vuoto">—</div>
                @endif

                {{-- Protagonista, con collegamento alla scheda --}}
                @if (!empty($v['nome']))
                    <div class="rec-chi">
                        @if (!empty($v['player_id']))
                            <a href="{{ route('giocatore.show', $v['player_id']) }}">{{ $v['nome'] }}</a>
                        @else
                            {{ $v['nome'] }}
                        @endif
                        @if (!empty($v['team_name']))
                            <span class="rec-sq">{{ $v['team_name'] }}</span>
                        @endif
                    </div>
                @endif

                {{-- Partita in cui il primato e' stato stabilito --}}
                @if (!empty($v['match_id']))
                    <a class="rec-partita" href="{{ route('partita.show', $v['match_id']) }}">
                        {{ $v['partita'] ?? $v['match_id'] }}
                        @if (!empty($v['data']))
                            <span class="rec-data">{{ \Carbon\Carbon::parse($v['data'])->format('d/m/Y') }}</span>
                        @endif
                    </a>
                @endif

                {{-- Classifica (marcatori, portieri) --}}
                @if (!empty($v['classifica']))
                    <ol class="rec-classifica">
                        @foreach ($v['classifica'] as $c)
                            <li>
                                @if (!empty($c['player_id']))
                                    <a href="{{ route('giocatore.show', $c['player_id']) }}">{{ $c['nome'] }}</a>
                                @else
                                    <span>{{ $c['nome'] }}</span>
                                @endif
                                <b>
                                    @if (isset($c['reti']))
                                        {{ $c['reti'] }}
                                    @elseif (isset($c['media']))
                                        {{ number_format($c['media'], 2, ',', '') }}
                                        <span class="rec-sq">({{ $c['partite'] }}p)</span>
                                    @endif
                                </b>
                            </li>
                        @endforeach
                    </ol>
                @endif

                {{-- Elenco degli episodi: ammonizioni, rigori, autogol --}}
                @if (!empty($v['dettaglio']))
                    <ul class="rec-episodi">
                        @foreach ($v['dettaglio'] as $d)
                            <li>
                                <a href="{{ route('partita.show', $d['match_id']) }}">{{ $d['nome'] }}</a>
                            </li>
                        @endforeach
                        @if (!empty($v['altri']))
                            <li class="rec-altri">e altri {{ $v['altri'] }}</li>
                        @endif
                    </ul>
                @endif

                @if (!empty($v['nota']))
                    <div class="rec-nota">{{ $v['nota'] }}</div>
                @endif
            </div>
        @endforeach
    </div>

    <style>
        .rec-avviso{background:#fffbe6;border:1px solid #f0e0a0;border-radius:8px;
            padding:10px 14px;margin:0 0 16px;font-size:13px;color:#6b5b16;}
        /* minmax(0,...) e' necessario: senza, i nomi lunghi allargano la
           colonna e la griglia sborda dal contenitore in responsive. */
        .rec-griglia{display:grid;gap:12px;
            grid-template-columns:repeat(auto-fill,minmax(min(100%,240px),1fr));}
        .rec-card{background:#f4f6f5;border:1px solid var(--line);border-radius:10px;
            padding:14px;min-width:0;overflow-wrap:anywhere;}
        .rec-tit{font-size:11px;text-transform:uppercase;letter-spacing:.6px;
            color:var(--muted);margin-bottom:6px;}
        .rec-val{font-size:20px;font-weight:800;color:var(--ink);line-height:1.25;}
        .rec-val.rec-vuoto{color:#b9c2bd;font-weight:600;}
        .rec-chi{margin-top:4px;font-size:14px;font-weight:600;}
        .rec-chi a{color:var(--accent);text-decoration:none;}
        .rec-chi a:hover{text-decoration:underline;}
        .rec-sq{font-weight:400;color:var(--muted);font-size:12px;}
        .rec-partita{display:block;margin-top:8px;font-size:12px;color:var(--muted);
            text-decoration:none;border-top:1px dotted var(--line);padding-top:7px;}
        .rec-partita:hover{color:var(--accent);}
        .rec-data{display:block;font-size:11px;opacity:.8;}
        .rec-classifica{margin:8px 0 0;padding-left:20px;font-size:13px;}
        .rec-classifica li{margin:3px 0;}
        .rec-classifica a{color:var(--accent);text-decoration:none;}
        .rec-classifica b{float:right;}
        .rec-episodi{list-style:none;margin:8px 0 0;padding:0;font-size:12px;
            display:flex;flex-wrap:wrap;gap:4px 8px;}
        .rec-episodi a{color:var(--muted);text-decoration:none;}
        .rec-episodi a:hover{color:var(--accent);text-decoration:underline;}
        .rec-altri{color:#b9c2bd;}
        .rec-nota{margin-top:8px;font-size:11px;color:var(--muted);}
    </style>
@endif
