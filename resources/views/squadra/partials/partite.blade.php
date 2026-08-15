{{-- Impaginazione "B" delle partite (scheda a due lati), condivisa.
     Usata dalla tab Partite della pagina squadra e, dal 15/08 (C1), dalle
     schede dimostrative: giocatore, allenatore, arbitro, stadio.
     Chi include non deve sapere nulla del resto: riceve $gruppi e $gol.

     Tab Partite della pagina squadra.
     Impaginazione "B" (scheda a due lati) raggruppata per edizione come
     nella "C". La squadra perdente non viene barrata.
     Variabili: $gruppi (etichetta => partite), $gol (match_id => marcatori) --}}
@if (empty($gruppi))
    <p>Nessuna partita trovata per questa squadra.</p>
@else
    <div class="pb">
        @foreach ($gruppi as $titolo => $righe)
            <div class="pb-grp">
                <div class="pb-ghead">
                    <b>{{ $titolo }}</b>
                    <span class="pb-gn">{{ count($righe) }} partite</span>
                </div>

                @foreach ($righe as $p)
                    <div class="pb-card">
                        <a class="pb-link" href="{{ route('partita.show', $p['match_id']) }}">
                            <div class="pb-top">{{ $p['meta'] }}</div>
                            {{-- Cinque colonne di larghezza fissa per bandiere e
                                 punteggio: non dipendendo dal contenuto, le
                                 bandiere restano incolonnate da una riga
                                 all'altra. Le due colonne dei nomi sono
                                 minmax(0,1fr), quindi si restringono insieme e i
                                 nomi lunghi vengono troncati invece di sfondare
                                 il contenitore. --}}
                            <div class="pb-mid">
                                <span class="pb-fl pb-fl-sx">
                                    @if ($p['casa']['flag'])
                                        <img src="{{ $p['casa']['flag'] }}" alt="{{ $p['casa']['code'] }}"
                                             onerror="this.style.display='none'">
                                    @endif
                                </span>
                                <span class="pb-nome pb-nome-sx">{{ $p['casa']['nome'] }}</span>
                                <span class="pb-pt">{{ $p['casa']['gol'] }}&ndash;{{ $p['ospite']['gol'] }}</span>
                                <span class="pb-nome pb-nome-dx">{{ $p['ospite']['nome'] }}</span>
                                <span class="pb-fl pb-fl-dx">
                                    @if ($p['ospite']['flag'])
                                        <img src="{{ $p['ospite']['flag'] }}" alt="{{ $p['ospite']['code'] }}"
                                             onerror="this.style.display='none'">
                                    @endif
                                </span>
                            </div>
                        </a>
                        @include('partials.gol-partita', ['gol' => $gol[$p['match_id']] ?? []])
                        {{-- C1: riga facoltativa sotto il punteggio. Nella tab
                             Partite della squadra non e' mai valorizzata; nelle
                             schede dimostrative porta cio' che la vecchia
                             tabella teneva in colonne (maglia, minutaggio, gol,
                             ruolo arbitrale). --}}
                        @if (!empty($p['extra']))
                            <div class="pb-extra">{{ $p['extra'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    @include('partials.gol-partita-css')

    <style>
        .pb-grp{margin-bottom:20px;}
        .pb-ghead{display:flex;align-items:baseline;gap:9px;padding:8px 2px;
            border-bottom:2px solid var(--accent);margin-bottom:10px;}
        .pb-gn{font-size:12px;color:var(--muted);}
        .pb-card{border:1px solid var(--line);border-radius:10px;padding:11px 12px;
            background:#f8faf9;margin-bottom:9px;}
        .pb-card:hover{border-color:var(--accent);}
        .pb-link{display:block;text-decoration:none;color:inherit;}
        .pb-link:hover{text-decoration:none;}
        .pb-top{text-align:center;font-size:12px;color:var(--muted);margin-bottom:8px;
            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .pb-mid{display:grid;gap:6px 10px;align-items:center;
            grid-template-columns:34px minmax(0,1fr) auto minmax(0,1fr) 34px;}
        .pb-fl{display:flex;align-items:center;width:34px;}
        .pb-fl-dx{justify-content:flex-end;}
        .pb-fl img{width:34px;height:23px;object-fit:cover;border-radius:3px;
            box-shadow:0 1px 3px rgba(0,0,0,.28);}
        .pb-nome{font-weight:600;white-space:nowrap;overflow:hidden;
            text-overflow:ellipsis;min-width:0;}
        .pb-nome-sx{text-align:right;}
        .pb-nome-dx{text-align:left;}
        .pb-pt{font-size:22px;font-weight:800;font-variant-numeric:tabular-nums;
            white-space:nowrap;padding:0 2px;}
        .pb-extra{margin-top:6px;padding-top:5px;border-top:1px dotted #d5ded8;
            font-size:12px;color:var(--muted);}
        @media (max-width:420px){
            .pb-mid{grid-template-columns:26px minmax(0,1fr) auto minmax(0,1fr) 26px;gap:5px 7px;}
            .pb-fl{width:26px;}
            .pb-fl img{width:26px;height:18px;}
            .pb-pt{font-size:18px;}
        }
    </style>
@endif
