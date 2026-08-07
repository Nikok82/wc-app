{{-- Tab Convocati della squadra-anno: rosa completa del torneo con club di
     provenienza, allenatori della spedizione e statistiche.
     Frammento via fetch: niente <script>, l'ordinamento delle colonne è
     gestito da wc.js (delega su .conv-table th[data-sort]). --}}

@if ($convocati->isEmpty())
    <p>Nessun convocato trovato per questo torneo.</p>
@else
    <div class="conv-scroll">
        <table class="conv-table">
            <thead>
                <tr>
                    <th class="c-ico"></th>
                    <th class="c-num" data-sort="num" title="Ordina per numero di maglia">#</th>
                    <th data-sort="nome" title="Ordina per nome">Nome</th>
                    <th data-sort="cognome" title="Ordina per cognome">Cognome</th>
                    <th data-sort="nascita" title="Ordina per data di nascita">Nascita</th>
                    <th class="c-num" data-sort="ruolo" title="Ordina per ruolo (P,D,C,A / A,C,D,P)">Ruolo</th>
                    <th class="c-num" data-sort="pg" title="Partite giocate nel torneo">PG</th>
                    <th class="c-num" data-sort="gol" title="Gol fatti nel torneo">Gol</th>
                    <th data-sort="club" title="Ordina per squadra di club">Club</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($convocati as $c)
                    <tr data-num="{{ $c['numero'] ?? '' }}"
                        data-nome="{{ mb_strtolower($c['given'] ?? '') }}"
                        data-cognome="{{ mb_strtolower($c['family'] ?? '') }}"
                        data-nascita="{{ $c['nascita'] ? $c['nascita']->format('Y-m-d') : '' }}"
                        data-ruolo="{{ $c['ruolo_ord'] }}"
                        data-pg="{{ $c['pg'] }}"
                        data-gol="{{ $c['gol'] }}"
                        data-club="{{ mb_strtolower($c['club'] ?? '') }}">
                        <td class="c-ico">
                            @if ($c['player_id'])
                                <a href="{{ route('giocatore.show', $c['player_id']) }}" title="Scheda giocatore">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
                                        <path d="M12 12c2.7 0 4.8-2.2 4.8-4.9S14.7 2.3 12 2.3 7.2 4.4 7.2 7.1 9.3 12 12 12zm0 2.2c-3.6 0-9 1.8-9 5.4v2.1h18v-2.1c0-3.6-5.4-5.4-9-5.4z"/>
                                    </svg>
                                </a>
                            @endif
                        </td>
                        <td class="c-num">{{ $c['numero'] ?? '—' }}</td>
                        <td>{{ $c['given'] }}</td>
                        <td class="c-cognome">{{ $c['family'] }}</td>
                        <td class="c-nascita">
                            @if ($c['nascita'])
                                {{ $c['nascita']->format('d/m/Y') }}@if($c['eta'] !== null) ({{ $c['eta'] }})@endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="c-num">{{ $c['ruolo'] ?? '—' }}</td>
                        <td class="c-num">{{ $c['pg'] }}</td>
                        <td class="c-num">{{ $c['gol'] }}</td>
                        <td class="c-club">
                            @if ($c['club'])
                                <span class="club-cell">
                                    @if ($c['club_logo'])
                                        <img src="{{ $c['club_logo'] }}" alt="" width="16" height="16"
                                             loading="lazy" onerror="this.style.display='none'">
                                    @endif
                                    <span>{{ $c['club'] }}</span>
                                </span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Allenatori della spedizione --}}
    @if ($allenatori->isNotEmpty())
        <h3 class="conv-sez">{{ $allenatori->count() > 1 ? 'Allenatori' : 'Allenatore' }}</h3>
        <div class="conv-manager">
            @foreach ($allenatori as $a)
                <a class="mgr" href="{{ route('allenatore.show', $a['id']) }}">
                    @if ($a['flag'])<img src="{{ $a['flag'] }}" alt="" onerror="this.style.display='none'">@endif
                    <span>{{ $a['nome'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Statistiche della spedizione --}}
    <h3 class="conv-sez">Statistiche</h3>
    <div class="info-grid">
        <div class="info-row"><span class="lbl">Convocati</span><span class="val">{{ $stats['convocati'] }}</span></div>
        @if ($stats['eta_media'] !== null)
            <div class="info-row"><span class="lbl">Età media</span><span class="val">{{ str_replace('.', ',', (string) $stats['eta_media']) }} anni</span></div>
        @endif
        <div class="info-row"><span class="lbl">Gol segnati nel torneo</span><span class="val">{{ $stats['gol_fatti'] }}</span></div>
        <div class="info-row"><span class="lbl">Autogol subiti</span><span class="val">{{ $stats['autogol'] }}</span></div>
        <div class="info-row"><span class="lbl">Giocatori da club nazionali</span><span class="val">{{ $stats['club_patria'] }}</span></div>
        <div class="info-row"><span class="lbl">Giocatori da club esteri</span><span class="val">{{ $stats['club_estero'] }}</span></div>
        @if ($stats['club_ignoti'] > 0)
            <div class="info-row"><span class="lbl">Club non noto</span><span class="val">{{ $stats['club_ignoti'] }}</span></div>
        @endif
    </div>

    <style>
        .conv-scroll{overflow-x:auto;margin:0 -8px;padding:0 8px;}
        .conv-table{width:100%;border-collapse:collapse;font-size:13px;min-width:640px;}
        .conv-table th{position:sticky;top:0;background:#f4f6f5;color:var(--muted);
            text-transform:uppercase;font-size:11px;letter-spacing:.4px;text-align:left;
            padding:8px 6px;border-bottom:2px solid var(--line);white-space:nowrap;}
        .conv-table th[data-sort]{cursor:pointer;user-select:none;}
        .conv-table th[data-sort]:hover{color:var(--ink);}
        .conv-table th[data-sort]::after{content:'↕';opacity:.35;margin-left:4px;font-size:10px;}
        .conv-table th[data-sort].asc::after{content:'↑';opacity:1;}
        .conv-table th[data-sort].desc::after{content:'↓';opacity:1;}
        .conv-table td{padding:7px 6px;border-bottom:1px solid var(--line);vertical-align:middle;}
        .conv-table tbody tr:last-child td{border-bottom:0;}
        .conv-table .c-num{text-align:center;}
        .conv-table .c-ico{width:24px;text-align:center;}
        .conv-table .c-ico a{color:var(--accent);display:inline-flex;}
        .conv-table .c-cognome{font-weight:700;}
        .conv-table .c-nascita{white-space:nowrap;}
        .conv-table .club-cell{display:inline-flex;align-items:center;gap:6px;}
        .conv-table .club-cell img{width:16px;height:16px;object-fit:contain;flex:none;}
        .conv-sez{font-size:1.02rem;font-weight:700;color:#0f6c14;margin:22px 0 10px;
            padding-bottom:6px;border-bottom:2px solid #57c785;}
        .conv-manager{display:flex;flex-wrap:wrap;gap:10px;}
        .conv-manager .mgr{display:inline-flex;align-items:center;gap:8px;padding:7px 12px;
            background:#fff;border:1px solid var(--line);border-radius:999px;font-weight:600;}
        .conv-manager .mgr img{width:22px;height:auto;border-radius:3px;
            box-shadow:0 1px 2px rgba(0,0,0,.25);}
    </style>
@endif
