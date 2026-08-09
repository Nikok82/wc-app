@if ($record['giocate'] === 0)
    <p>Nessun dato disponibile per questa squadra.</p>
@else
    <div class="record-stats">
        <div class="stat"><span class="num">{{ $record['giocate'] }}</span><span class="lab">Partite</span></div>
        <div class="stat"><span class="num">{{ $record['vittorie'] }}</span><span class="lab">Vittorie</span></div>
        <div class="stat"><span class="num">{{ $record['pareggi'] }}</span><span class="lab">Pareggi</span></div>
        <div class="stat"><span class="num">{{ $record['sconfitte'] }}</span><span class="lab">Sconfitte</span></div>
        <div class="stat"><span class="num">{{ $record['gol_fatti'] }}</span><span class="lab">Gol fatti</span></div>
        <div class="stat"><span class="num">{{ $record['gol_subiti'] }}</span><span class="lab">Gol subiti</span></div>
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
                        gap:12px; margin-bottom:24px; }
        .record-stats .stat { background:#f4f6f5; border:1px solid var(--line);
                              border-radius:10px; padding:16px; text-align:center; }
        .record-stats .num { display:block; font-size:30px; font-weight:800; color:var(--ink); }
        .record-stats .lab { display:block; margin-top:4px; font-size:12px; color:var(--muted);
                             text-transform:uppercase; letter-spacing:.5px; }
        .record-perc { display:flex; flex-direction:column; gap:10px; }
        .perc-row { display:flex; align-items:center; gap:12px; }
        .perc-bar { position:relative; flex:1; background:#e9ecef; height:24px;
                    border-radius:12px; overflow:hidden; }
        .perc-fill { height:100%; border-radius:12px; transition:width .3s ease; }
        .perc-fill.win  { background:#1b9e57; }
        .perc-fill.draw { background:#b0b7b3; }
        .perc-fill.lose { background:#c0392b; }
        .perc-lab { width:150px; flex:none; font-size:13px; font-weight:600; }
    </style>
@endif