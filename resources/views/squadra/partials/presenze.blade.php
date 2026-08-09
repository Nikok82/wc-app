@if ($presenze->isEmpty())
    <p>Nessuna partecipazione trovata per questa squadra.</p>
@else
    <div class="presenze">
        @foreach ($presenze as $pt)
            @php $cliccabile = $pt['qualificata'] && ! empty($pt['url']); @endphp
            <div class="presenza {{ $pt['qualificata'] ? 'ok' : 'no' }}">
                {{-- Parte cliccabile (host + nome + partite): apre la scheda
                     squadra-anno. L'esito resta FUORI perché contiene già dei
                     link propri (avversari/turni): niente <a> annidati. --}}
                @if ($cliccabile)
                    <a class="riga-link" href="{{ $pt['url'] }}"
                       title="{{ $pt['tournament_name'] }} — scheda {{ $code }} {{ $pt['anno'] }}">
                @else
                    <span class="riga-link">
                @endif
                        <span class="riga-host">
                            @if (! empty($pt['host_flag']))
                                <img src="{{ $pt['host_flag'] }}" alt="{{ $pt['host'] }}"
                                     title="Paese ospitante: {{ $pt['host'] }}"
                                     onerror="this.style.display='none'">
                            @endif
                        </span>
                        <span class="torneo-nome">
                            {{ $pt['tournament_name'] }}
                            @if (! empty($pt['host']))
                                <small class="host-nome">{{ $pt['host'] }}</small>
                            @endif
                        </span>
                        @if ($pt['qualificata'])
                            <span class="match-count">{{ $pt['count_matches'] }} partite</span>
                        @else
                            <span class="match-count non-qual">non qualificata</span>
                        @endif
                @if ($cliccabile)
                    </a>
                @else
                    </span>
                @endif
                <span class="piazzamento">{!! $pt['esito'] !!}</span>
            </div>
        @endforeach
    </div>

    <style>
        .presenze { display:flex; flex-direction:column; gap:8px; }
        .presenza { display:flex; align-items:center; gap:10px; }
        .presenza.no { opacity:.72; }

        /* Parte cliccabile della riga: bordo trasparente che si ACCENDE in luce
           al passaggio del mouse (solo righe qualificate). Riusa le @keyframes
           wc-luce-rotate definite dal layout (partials/luce-bordo-css). */
        .riga-link { flex:1; min-width:0; display:flex; align-items:center; gap:12px;
                     padding:9px 12px; border:2px solid transparent; border-radius:12px;
                     color:inherit; text-decoration:none;
                     background:linear-gradient(#fff,#fff) padding-box,
                                linear-gradient(#eef2f0,#eef2f0) border-box; }
        a.riga-link { cursor:pointer; --luce-angle:0deg; }
        a.riga-link:hover { text-decoration:none;
            background:linear-gradient(#fff,#fff) padding-box,
                conic-gradient(from var(--luce-angle),
                    #045e03, #058404, #08ff07, #045e03) border-box;
            animation:wc-luce-rotate 2s infinite linear; }
        @media (prefers-reduced-motion: reduce) { a.riga-link:hover { animation:none; } }

        .riga-host { flex:none; width:34px; display:flex; justify-content:center; }
        .riga-host img { width:30px; height:auto; border-radius:3px;
                         box-shadow:0 1px 3px rgba(0,0,0,.3); }
        .torneo-nome { flex:1; min-width:0; font-weight:600; }
        .torneo-nome .host-nome { display:block; font-size:12px; font-weight:400;
                                  color:var(--muted); }
        .match-count { flex:none; width:96px; text-align:right; font-size:13px;
                       color:var(--muted); }
        .match-count.non-qual { font-style:italic; }
        .piazzamento { flex:none; width:160px; text-align:right; }

        @media (max-width:560px){
            .presenza { flex-wrap:wrap; }
            .riga-link { flex:1 1 100%; }
            .piazzamento { width:100%; text-align:left; padding-left:46px; }
            .match-count { width:auto; }
        }
    </style>
@endif
