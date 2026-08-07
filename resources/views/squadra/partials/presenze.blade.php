@if ($presenze->isEmpty())
    <p>Nessuna partecipazione trovata per questa squadra.</p>
@else
    <div class="presenze">
        @foreach ($presenze as $pt)
            <div class="presenza {{ $pt['qualificata'] ? 'ok' : 'no' }}">
                <span class="torneo-nome">{{ $pt['tournament_name'] }}</span>
                @if ($pt['qualificata'])
                    <span class="match-count">{{ $pt['count_matches'] }} partite</span>
                @else
                    <span class="match-count non-qual">non qualificata</span>
                @endif
                <span class="piazzamento">{!! $pt['esito'] !!}</span>
            </div>
        @endforeach
    </div>

    <style>
        .presenza { display:flex; align-items:center; gap:12px; padding:10px 2px;
                    border-bottom:1px solid var(--line); font-size:14px; }
        .presenza:last-child { border-bottom:0; }
        .presenza.no { opacity:.7; }
        .presenza .torneo-nome { flex:1; font-weight:600; }
        .presenza .match-count { color:var(--muted); width:110px; flex:none;
                                 text-align:right; font-size:13px; }
        .presenza .match-count.non-qual { font-style:italic; }
        .presenza .piazzamento { width:170px; flex:none; text-align:right; }
    </style>
@endif