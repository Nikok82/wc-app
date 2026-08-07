{{-- Tab Eventi: cronologia della partita su tre colonne (casa 3/7,
     minuto 1/7, ospite 3/7). Icone: pallone = gol, cartellino giallo/rosso,
     freccia rossa a destra = fuori, freccia verde a sinistra = dentro. --}}

@if (empty(array_filter($eventi, fn ($r) => ! isset($r['sep']))))
    <p style="color:var(--muted)">Nessun evento registrato per questa partita.</p>
@else
    <table class="ev-table">
        <colgroup>
            <col style="width:42.85%"><col style="width:14.3%"><col style="width:42.85%">
        </colgroup>
        <tbody>
            @foreach ($eventi as $r)
                @if (isset($r['sep']))
                    <tr class="ev-sep"><td></td><td colspan="1">{{ $r['sep'] }}</td><td></td></tr>
                @else
                    @php
                        $cella = view('partita.partials._evento', ['r' => $r])->render();
                    @endphp
                    <tr>
                        <td class="ev-casa">@if ($r['side'] === 'home'){!! $cella !!}@endif</td>
                        <td class="ev-min">{{ $r['minuto'] }}</td>
                        <td class="ev-ospite">@if ($r['side'] === 'away'){!! $cella !!}@endif</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif

<style>
    .ev-table { width:100%; border-collapse:collapse; font-size:13.5px; }
    .ev-table td { padding:8px 6px; border-bottom:1px solid var(--line, #e2e8e5);
                   vertical-align:middle; }
    .ev-table tr:last-child td { border-bottom:0; }
    .ev-casa   { text-align:left; }
    .ev-ospite { text-align:right; }
    .ev-min { text-align:center; color:var(--muted, #6b7a72); font-weight:700;
              font-variant-numeric:tabular-nums; white-space:nowrap; }
    .ev-sep td { text-align:center; color:var(--accent, #1b9e57); font-weight:800;
                 text-transform:uppercase; font-size:12px; letter-spacing:.6px;
                 padding:12px 6px 6px; white-space:nowrap; }

    .ev-item { display:inline-flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .ev-ospite .ev-item { justify-content:flex-end; }
    .ev-item em { color:var(--muted, #6b7a72); font-style:normal; font-size:11px; }

    .ev-ball { display:inline-flex; width:18px; height:18px; border-radius:50%;
               background:#fff; border:1px solid #c8d2cc; align-items:center;
               justify-content:center; flex:none; }
    .ev-ball svg { width:13px; height:13px; fill:#16231d; }
    .ev-card { display:inline-block; width:12px; height:16px; border-radius:2px;
               border:1px solid rgba(0,0,0,.3); flex:none; }
    .ev-card.giallo { background:#f6c700; }
    .ev-card.rosso  { background:#d1281e; }
    .ev-doppia { position:relative; margin-right:5px; }
    .ev-doppia::after { content:''; position:absolute; left:5px; top:-3px; width:12px;
               height:16px; border-radius:2px; background:#f6c700;
               border:1px solid rgba(0,0,0,.3); z-index:-1; }
    .ev-arrow { display:inline-block; width:0; height:0; flex:none;
                border-top:6px solid transparent; border-bottom:6px solid transparent; }
    .ev-arrow.out { border-left:9px solid #d1281e; }
    .ev-arrow.in  { border-right:9px solid #17c24a; }

    @media (max-width:480px) {
        .ev-table { font-size:12px; }
        .ev-table td { padding:7px 3px; }
    }
</style>
