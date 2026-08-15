{{-- Tab Partite della pagina squadra-anno.
     Impaginazione "C": elenco raggruppato, riga compatta con avversario,
     e sotto ogni partita l'elenco dei marcatori.
     Variabili: $partite (TeamAppearance), $gol (match_id => marcatori),
     $code, $titoloGruppo --}}
@if ($partite->isEmpty())
    <p>Nessuna partita trovata.</p>
@else
    <div class="pt-c">
        <div class="pt-grp">
            @if (!empty($titoloGruppo))
                <div class="pt-ghead">
                    <b>{{ $titoloGruppo }}</b>
                    <span class="pt-gn">{{ $partite->count() }} partite</span>
                </div>
            @endif

            @foreach ($partite as $p)
                @php
                    $esito = $p->win ? 'win' : ($p->draw ? 'draw' : ($p->lose ? 'lose' : ''));
                @endphp
                <div class="pt-riga pt-{{ $esito }}">
                    <a class="pt-testa" href="{{ route('partita.show', $p->match_id) }}">
                        <span class="pt-data">
                            {{ $p->match_date ? $p->match_date->format('d/m') : '' }}
                        </span>
                        <span class="pt-avv">
                            @if ($p->opponent_flag ?? null)
                                <img class="pt-fl" src="{{ $p->opponent_flag }}"
                                     alt="{{ $p->opponent_code }}" onerror="this.style.display='none'">
                            @endif
                            <span class="pt-nome">{{ $p->opponent_name }}</span>
                        </span>
                        <span class="pt-pt">{{ $p->goals_for }}–{{ $p->goals_against }}</span>
                    </a>
                    @include('partials.gol-partita', ['gol' => $gol[$p->match_id] ?? []])
                </div>
            @endforeach
        </div>
    </div>

    @include('partials.gol-partita-css')

    <style>
        .pt-ghead{display:flex;align-items:baseline;gap:9px;padding:8px 2px;
            border-bottom:2px solid var(--accent);margin-bottom:2px;}
        .pt-gn{font-size:12px;color:var(--muted);}
        .pt-riga{padding:9px 4px;border-bottom:1px solid var(--line);}
        .pt-riga:last-child{border-bottom:0;}
        .pt-riga:hover{background:rgba(27,158,87,.06);}
        /* minmax(0,1fr) sulla colonna centrale: senza, un nome lungo
           allarga la griglia e sborda dal box in responsive. */
        .pt-testa{display:grid;grid-template-columns:auto minmax(0,1fr) auto;
            gap:10px;align-items:center;text-decoration:none;color:inherit;}
        .pt-testa:hover{text-decoration:none;}
        .pt-data{font-size:12px;color:var(--muted);font-variant-numeric:tabular-nums;}
        .pt-avv{display:flex;align-items:center;gap:7px;min-width:0;}
        .pt-fl{width:22px;height:15px;object-fit:cover;border-radius:2px;flex:none;
            box-shadow:0 1px 2px rgba(0,0,0,.25);}
        .pt-nome{font-weight:600;white-space:nowrap;overflow:hidden;
            text-overflow:ellipsis;min-width:0;}
        .pt-pt{font-variant-numeric:tabular-nums;font-weight:700;}
        .pt-win  .pt-pt{color:#0f6e56;}
        .pt-lose .pt-pt{color:#c0392b;}
        .pt-draw .pt-pt{color:#5F5E5A;}
    </style>
@endif
