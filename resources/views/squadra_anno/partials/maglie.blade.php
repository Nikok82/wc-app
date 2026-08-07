{{-- Tab Maglie della squadra-anno: le maglie indossate dalla nazionale nel
     torneo, ordinate per numero di partite (la piu' usata per prima). Sotto
     ogni maglia, le partite in cui e' stata indossata, linkate alla scheda
     partita. Frammento via fetch: niente <script>, solo <style>. --}}

@if (empty($maglie))
    <p>Nessuna maglia disponibile per questa spedizione.</p>
@else
    <div class="maglie-anno">
        @foreach ($maglie as $k)
            <div class="kit-block">
                <div class="kit-img">
                    @if ($k['url'])
                        <img src="{{ $k['url'] }}" alt="Maglia" loading="lazy"
                             onerror="this.style.display='none'">
                    @endif
                    <span class="kit-count">{{ $k['count'] }} {{ $k['count'] == 1 ? 'partita' : 'partite' }}</span>
                </div>

                <div class="kit-partite">
                    @foreach ($k['partite'] as $p)
                        <a class="kit-partita" href="{{ $p['url'] }}"
                           title="Vai alla scheda della partita">
                            <span class="data">{{ $p['data'] }}</span>
                            <span class="avv">
                                @if ($p['avversario_flag'])
                                    <img src="{{ $p['avversario_flag'] }}" alt=""
                                         onerror="this.style.display='none'">
                                @endif
                                <span>{{ $p['avversario'] ?: '—' }}</span>
                            </span>
                            @if ($p['gf'] !== null && $p['gs'] !== null)
                                <span class="ris">{{ $p['gf'] }}–{{ $p['gs'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .maglie-anno { display:flex; flex-direction:column; gap:14px; }
        .kit-block { display:flex; gap:14px; align-items:flex-start; padding:12px;
            border:1px solid var(--line, #e2e8e5); border-radius:12px; background:#fafcfa; }
        .kit-img { flex:none; width:104px; display:flex; flex-direction:column;
            align-items:center; gap:6px; }
        .kit-img img { width:100px; height:auto;
            image-rendering:-webkit-optimize-contrast; }
        .kit-count { font-size:11px; font-weight:700; color:var(--muted, #6b7a72);
            text-transform:uppercase; letter-spacing:.3px; text-align:center; }
        .kit-partite { flex:1; display:flex; flex-direction:column; min-width:0; }
        .kit-partita { display:flex; align-items:center; gap:10px; padding:8px 4px;
            border-bottom:1px solid var(--line, #e2e8e5); font-size:14px;
            color:var(--ink, #1a2420); }
        .kit-partita:last-child { border-bottom:0; }
        a.kit-partita:hover { text-decoration:none; background:rgba(27,158,87,.07); }
        .kit-partita .data { color:var(--muted, #6b7a72); width:80px; flex:none;
            font-variant-numeric:tabular-nums; }
        .kit-partita .avv { flex:1; display:inline-flex; align-items:center; gap:8px;
            font-weight:600; min-width:0; }
        .kit-partita .avv img { width:20px; height:20px; border-radius:50%;
            object-fit:cover; flex:none; box-shadow:0 1px 2px rgba(0,0,0,.2); }
        .kit-partita .avv span { overflow:hidden; text-overflow:ellipsis;
            white-space:nowrap; }
        .kit-partita .ris { font-variant-numeric:tabular-nums; font-weight:700;
            flex:none; }
        @media (max-width:480px) {
            .kit-block { gap:10px; padding:10px; }
            .kit-img { width:82px; }
            .kit-img img { width:80px; }
            .kit-partita { gap:8px; font-size:13px; }
            .kit-partita .data { width:66px; }
        }
    </style>
@endif
