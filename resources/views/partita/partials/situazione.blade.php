{{-- Tab Situazione:
     - partita di girone: classifica del girone prima e dopo la partita
     - turno a eliminazione: tutte le partite dello stesso turno (linkate)
     - finale / finale 3° posto: podio del mondiale + le due finali. --}}
@php $corrente = $m->match_id; @endphp

@if ($situazione['tipo'] === 'girone')
    <h3 class="st-titolo">{{ $situazione['stage'] }} — {{ $situazione['girone'] }}</h3>

    <div class="st-gironi">
        @foreach (['prima' => 'Prima della partita', 'dopo' => 'Dopo la partita'] as $chiave => $label)
            <div class="st-girone">
                <h4>{{ $label }}</h4>
                <table class="st-cls">
                    <thead>
                        <tr>
                            <th></th><th class="sq">Squadra</th><th>PG</th><th>V</th><th>N</th>
                            <th>P</th><th>GF</th><th>GS</th><th>Pt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($situazione[$chiave] as $i => $r)
                            <tr class="{{ in_array($r['code'], [$m->home_team_code, $m->away_team_code], true) ? 'evid' : '' }}">
                                <td class="pos">{{ $i + 1 }}</td>
                                <td class="sq">
                                    @if ($r['flag'])<img class="flag" src="{{ $r['flag'] }}" alt="" onerror="this.style.display='none'">@endif
                                    <a href="{{ $r['url'] }}">{{ $r['name'] }}</a>
                                </td>
                                <td>{{ $r['pg'] }}</td><td>{{ $r['v'] }}</td><td>{{ $r['n'] }}</td>
                                <td>{{ $r['p'] }}</td><td>{{ $r['gf'] }}</td><td>{{ $r['gs'] }}</td>
                                <td class="pt">{{ $r['pt'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@else
    @if ($situazione['tipo'] === 'finale')
        <h3 class="st-titolo">Il podio del Mondiale</h3>
        @if (count($situazione['podio']))
            <div class="st-podio">
                @foreach ($situazione['podio'] as $r)
                    <div class="st-medaglia m{{ $r['position'] }}">
                        <span class="st-pos">{{ $r['position'] }}°</span>
                        @if ($r['flag'])<img class="flag" src="{{ $r['flag'] }}" alt="" onerror="this.style.display='none'">@endif
                        <a href="{{ $r['url'] }}">{{ $r['name'] }}</a>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--muted)">Podio non disponibile.</p>
        @endif
        <h3 class="st-titolo">Le finali</h3>
    @else
        <h3 class="st-titolo">{{ $situazione['stage'] }} — tutte le partite del turno</h3>
    @endif

    <div class="st-partite">
        @php $labelPrec = null; @endphp
        @foreach ($situazione['partite'] as $p)
            @if ($situazione['tipo'] === 'finale' && $p['label_stage'] !== $labelPrec)
                @php $labelPrec = $p['label_stage']; @endphp
                <div class="st-fase">{{ $p['label_stage'] }}</div>
            @endif
            <a class="st-riga {{ $p['match_id'] === $corrente ? 'evid' : '' }}"
               href="{{ route('partita.show', $p['match_id']) }}">
                <span class="st-sq casa">
                    <span>{{ $p['home']['name'] ?: 'da definire' }}</span>
                    @if ($p['home']['flag'])<img class="flag" src="{{ $p['home']['flag'] }}" alt="" onerror="this.style.display='none'">@endif
                </span>
                <span class="st-ris">
                    {{ $p['ris_gol'] }}
                    @if ($p['e_replay'])<small>replay</small>
                    @elseif ($p['replay'])<small>{{ $p['replay']['score'] }} d.R.</small>
                    @elseif ($p['dcr'])<small>{{ $p['ris_rigori'] }} d.c.r.</small>
                    @elseif ($p['dts'])<small>d.t.s.</small>
                    @endif
                </span>
                <span class="st-sq ospite">
                    @if ($p['away']['flag'])<img class="flag" src="{{ $p['away']['flag'] }}" alt="" onerror="this.style.display='none'">@endif
                    <span>{{ $p['away']['name'] ?: 'da definire' }}</span>
                </span>
            </a>
        @endforeach
    </div>
@endif

<style>
    .st-titolo { font-size:16px; margin:2px 0 10px; }
    .st-titolo ~ .st-titolo { margin-top:18px; }

    /* ---- classifiche girone prima/dopo ---- */
    .st-gironi { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .st-girone h4 { margin:0 0 6px; font-size:12px; text-transform:uppercase;
                    letter-spacing:.5px; color:var(--muted, #6b7a72); }
    .st-cls { width:100%; border-collapse:collapse; font-size:12.5px; }
    .st-cls th { text-align:center; color:var(--muted, #6b7a72); font-size:10.5px;
                 text-transform:uppercase; padding:4px 3px;
                 border-bottom:1px solid var(--line, #e2e8e5); }
    .st-cls th.sq { text-align:left; }
    .st-cls td { text-align:center; padding:5px 3px;
                 border-bottom:1px solid var(--line, #e2e8e5);
                 font-variant-numeric:tabular-nums; }
    .st-cls tr:last-child td { border-bottom:0; }
    .st-cls td.sq { text-align:left; white-space:nowrap; }
    .st-cls td.sq .flag { width:18px; height:auto; border-radius:2px;
                          vertical-align:-3px; margin-right:5px;
                          box-shadow:0 1px 2px rgba(0,0,0,.25); }
    .st-cls td.pos { color:var(--muted, #6b7a72); }
    .st-cls td.pt { font-weight:800; }
    .st-cls tr.evid { background:rgba(27,158,87,.10); }
    @media (max-width:640px) {
        .st-gironi { grid-template-columns:1fr; }
        .st-cls td.sq { white-space:normal; }
    }

    /* ---- partite del turno / finali ---- */
    .st-fase { font-size:12px; text-transform:uppercase; letter-spacing:.5px;
               color:var(--muted, #6b7a72); font-weight:700; margin:12px 0 4px; }
    .st-riga { display:flex; align-items:center; gap:10px; padding:9px 10px;
               border:1px solid var(--line, #e2e8e5); border-radius:10px;
               margin-bottom:8px; color:var(--ink, #16231d); }
    .st-riga:hover { text-decoration:none; border-color:var(--accent, #1b9e57); }
    .st-riga.evid { background:rgba(27,158,87,.10); border-color:var(--accent, #1b9e57); }
    .st-sq { flex:1; display:flex; align-items:center; gap:8px; min-width:0;
             font-weight:600; font-size:13.5px; overflow-wrap:anywhere; }
    .st-sq.casa   { justify-content:flex-end; text-align:right; }
    .st-sq.ospite { justify-content:flex-start; }
    .st-sq .flag { width:24px; height:auto; border-radius:3px; flex:none;
                   box-shadow:0 1px 3px rgba(0,0,0,.3); }
    .st-ris { flex:none; min-width:56px; text-align:center; font-weight:800;
              font-variant-numeric:tabular-nums; }
    .st-ris small { display:block; font-weight:600; color:var(--muted, #6b7a72);
                    font-size:10.5px; }

    /* ---- podio ---- */
    .st-podio { display:flex; flex-direction:column; gap:8px; margin-bottom:6px; }
    .st-medaglia { display:flex; align-items:center; gap:10px; padding:9px 12px;
                   border-radius:10px; border:1px solid var(--line, #e2e8e5);
                   font-weight:700; }
    .st-medaglia .flag { width:26px; height:auto; border-radius:3px;
                         box-shadow:0 1px 3px rgba(0,0,0,.3); }
    .st-medaglia .st-pos { width:30px; height:30px; border-radius:50%; flex:none;
                   display:flex; align-items:center; justify-content:center;
                   font-size:13px; color:#16231d; box-shadow:inset 0 0 0 1px rgba(0,0,0,.15); }
    .st-medaglia.m1 .st-pos { background:#ffcc00; }
    .st-medaglia.m2 .st-pos { background:#c9c9c9; }
    .st-medaglia.m3 .st-pos { background:#cd7f32; color:#fff; }
</style>
