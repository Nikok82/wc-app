@if ($partite->isEmpty())
    <p>Nessuna partita trovata per questa squadra.</p>
@else
    <div class="partite">
        @foreach ($partite as $p)
            <a class="partita" href="{{ route('partita.show', $p->match_id) }}"
               title="Vai alla scheda della partita">
                <span class="data">{{ $p->match_date ? $p->match_date->format('d/m/Y') : '' }}</span>
                <span class="avversario">vs {{ $p->opponent_name }}</span>
                <span class="punteggio">{{ $p->goals_for }}–{{ $p->goals_against }}</span>
                @if ($p->win)
                    <span class="esito win">V</span>
                @elseif ($p->draw)
                    <span class="esito draw">N</span>
                @elseif ($p->lose)
                    <span class="esito lose">P</span>
                @else
                    <span class="esito">–</span>
                @endif
                <span class="torneo">{{ $p->tournament_name }}</span>
            </a>
        @endforeach
    </div>

    <style>
        .partita { display:flex; align-items:center; gap:12px; padding:9px 2px;
                   border-bottom:1px solid var(--line); font-size:14px;
                   color:var(--ink); }
        a.partita:hover { text-decoration:none; background:rgba(27,158,87,.07); }
        .partita:last-child { border-bottom:0; }
        .partita .data { color:var(--muted); width:84px; flex:none; }
        .partita .avversario { flex:1; font-weight:600; }
        .partita .punteggio { font-variant-numeric:tabular-nums; }
        .partita .esito { width:22px; height:22px; flex:none; border-radius:50%;
                          color:#fff; font-size:12px; font-weight:700;
                          display:flex; align-items:center; justify-content:center; }
        .partita .esito.win  { background:#1b9e57; }
        .partita .esito.draw { background:#b0b7b3; }
        .partita .esito.lose { background:#c0392b; }
        .partita .torneo { color:var(--muted); width:150px; flex:none;
                           text-align:right; font-size:12px; }
    </style>
@endif