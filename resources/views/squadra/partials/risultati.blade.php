@if ($record['giocate'] === 0)
    <p>Nessun dato disponibile per questa squadra.</p>
@else
    @php
        // Costruttore delle 3 fette V/N/P per una torta (verde / giallo / rosso).
        $sl = fn ($t) => [
            ['val' => (int) $t['v'], 'color' => '#1b9e57', 'label' => 'V'],
            ['val' => (int) $t['n'], 'color' => '#e8c11c', 'label' => 'N'],
            ['val' => (int) $t['p'], 'color' => '#c0392b', 'label' => 'P'],
        ];
    @endphp

    <div class="record-stats">
        <div class="stat"><span class="num">{{ $record['giocate'] }}</span><span class="lab">Partite</span></div>
        <div class="stat"><span class="num">{{ $record['vittorie'] }}</span><span class="lab">Vittorie</span></div>
        <div class="stat"><span class="num">{{ $record['pareggi'] }}</span><span class="lab">Pareggi</span></div>
        <div class="stat"><span class="num">{{ $record['sconfitte'] }}</span><span class="lab">Sconfitte</span></div>
        <div class="stat"><span class="num">{{ $record['gol_fatti'] }}</span><span class="lab">Gol fatti</span></div>
        <div class="stat"><span class="num">{{ $record['gol_subiti'] }}</span><span class="lab">Gol subiti</span></div>
    </div>

    {{-- 3 torte pseudo-3D: totale, prime fasi (gironi), altre fasi (KO).
         "Altre fasi" è grigia (n/d) se la squadra non ha mai superato i gironi. --}}
    <div class="risultati-torte">
        <div class="torta torta-big">{!! \App\Support\Charts::pie3d($sl($torte['totale']), 84, 'Totale') !!}</div>
        <div class="torta torta-sm">{!! \App\Support\Charts::pie3d($sl($torte['prime']), 54, 'Prime fasi', false) !!}</div>
        <div class="torta torta-sm">{!! \App\Support\Charts::pie3d($sl($torte['altre']), 54, 'Altre fasi', false) !!}</div>
    </div>
    <div class="torte-legenda">
        <span><i style="background:#1b9e57"></i>Vittorie</span>
        <span><i style="background:#e8c11c"></i>Pareggi</span>
        <span><i style="background:#c0392b"></i>Sconfitte</span>
    </div>

    <div class="record-perc">
        <div class="perc-row">
            <div class="perc-bar"><div class="perc-fill win" style="width: {{ $record['perc_vittorie'] }}%"></div></div>
            <span class="perc-lab">Vittorie {{ $record['perc_vittorie'] }}%</span>
        </div>
        <div class="perc-row">
            <div class="perc-bar"><div class="perc-fill draw" style="width: {{ $record['perc_pareggi'] }}%"></div></div>
            <span class="perc-lab">Pareggi {{ $record['perc_pareggi'] }}%</span>
        </div>
        <div class="perc-row">
            <div class="perc-bar"><div class="perc-fill lose" style="width: {{ $record['perc_sconfitte'] }}%"></div></div>
            <span class="perc-lab">Sconfitte {{ $record['perc_sconfitte'] }}%</span>
        </div>
    </div>

    <style>
        .record-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(110px, 1fr));
                        gap:12px; margin-bottom:20px; }
        .record-stats .stat { background:#f4f6f5; border:1px solid var(--line);
                              border-radius:10px; padding:16px; text-align:center; }
        .record-stats .num { display:block; font-size:30px; font-weight:800; color:var(--ink); }
        .record-stats .lab { display:block; margin-top:4px; font-size:12px; color:var(--muted);
                             text-transform:uppercase; letter-spacing:.5px; }

        /* Torte 3D (V verde / N giallo / P rosso) — SVG statico da App\Support\Charts. */
        .risultati-torte { display:flex; gap:22px; align-items:flex-end; justify-content:center;
                           flex-wrap:wrap; margin:4px 0 12px; }
        .risultati-torte .wc-pie { max-width:100%; height:auto; overflow:visible; }
        .risultati-torte .torta-big .wc-pie { width:196px; }
        .risultati-torte .torta-sm  .wc-pie { width:140px; }
        .wc-pie-tit  { font-size:12px; font-weight:700; fill:var(--ink); }
        .wc-pie-num  { font-size:13px; font-weight:800; fill:#fff;
                       paint-order:stroke; stroke:rgba(0,0,0,.38); stroke-width:3px; stroke-linejoin:round; }
        .wc-pie-empty{ font-size:14px; font-weight:700; fill:var(--muted); }
        .torte-legenda { display:flex; gap:18px; justify-content:center; flex-wrap:wrap;
                         font-size:13px; color:var(--muted); margin:0 0 22px; }
        .torte-legenda i { display:inline-block; width:11px; height:11px; border-radius:2px;
                           margin-right:6px; vertical-align:-1px; }

        .record-perc { display:flex; flex-direction:column; gap:10px; }
        .perc-row { display:flex; align-items:center; gap:12px; }
        .perc-bar { position:relative; flex:1; background:#e9ecef; height:24px;
                    border-radius:12px; overflow:hidden; }
        .perc-fill { height:100%; border-radius:12px; transition:width .3s ease; }
        .perc-fill.win  { background:#1b9e57; }
        .perc-fill.draw { background:#e8c11c; }
        .perc-fill.lose { background:#c0392b; }
        .perc-lab { width:150px; flex:none; font-size:13px; font-weight:600; }
    </style>
@endif
