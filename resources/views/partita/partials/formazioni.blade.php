{{-- Tab Formazioni: campo verticale con gli 11 titolari per squadra
     (casa in alto: P/D/C/A; ospite speculare in basso: A/C/D/P) e sotto
     l'elenco su due colonne (titolari, manager, subentrati, resto rosa).
     Le maglie della partita fanno da sfondo (25%) alle colonne dei nomi:
     nella colonna di casa (sinistra) si vede la metà destra della maglia,
     in quella ospite la metà sinistra. --}}
@php
    $home = $formazioni['home'];
    $away = $formazioni['away'];
    $vuota = empty($home['titolari']) && empty($away['titolari']);
@endphp

@if ($vuota)
    <p style="color:var(--muted)">Nessuna formazione disponibile per questa partita.</p>
@else
    <div class="fz-campo-wrap">
        <div class="fz-campo">
            <span class="fz-area alto"></span>
            <span class="fz-area basso"></span>
            <span class="fz-metacampo"></span>
            <span class="fz-cerchio"></span>

            {{-- Casa: portiere in alto, poi D / C / A verso il centro --}}
            @foreach (['P', 'D', 'C', 'A'] as $rep)
                <div class="fz-riga">
                    @foreach ($home['campo'][$rep] as $p)
                        @include('partita.partials._pallino', ['p' => $p])
                    @endforeach
                </div>
            @endforeach

            {{-- Ospite: speculare (attaccanti verso il centro, portiere in basso) --}}
            @foreach (['A', 'C', 'D', 'P'] as $rep)
                <div class="fz-riga">
                    @foreach ($away['campo'][$rep] as $p)
                        @include('partita.partials._pallino', ['p' => $p])
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- ================= ELENCO SU DUE COLONNE ================= --}}
    <div class="fz-elenco">
        @foreach (['home', 'away'] as $side)
            @php
                $sq = $formazioni[$side];
                $kit = $info['maglie'][$side] ?? null;
            @endphp
            <div class="fz-col {{ $side === 'home' ? 'casa' : 'ospite' }}">
                @if ($kit)
                    <img class="fz-kit-bg" src="{{ $kit }}" alt="" aria-hidden="true"
                         onerror="this.style.display='none'">
                @endif

                <div class="fz-col-testa">
                    @if ($sq['team']['url'])<a href="{{ $sq['team']['url'] }}">{{ $sq['team']['name'] }}</a>@else{{ $sq['team']['name'] }}@endif
                </div>

                @foreach ($sq['titolari'] as $p)
                    <div class="fz-voce">
                        <span class="fz-nome"><a href="{{ route('giocatore.show', $p['player_id']) }}">{{ $p['nome'] }}</a></span>
                        @include('partita.partials._pallino', ['p' => $p])
                    </div>
                @endforeach

                @if ($sq['manager'])
                    <div class="fz-voce fz-mgr">
                        <span class="fz-nome">
                            <small>Manager</small>
                            <a href="{{ route('allenatore.show', $sq['manager']['id']) }}">{{ $sq['manager']['nome'] }}</a>
                        </span>
                        <span class="plr" title="{{ $sq['manager']['nome'] }}">
                            <span class="plr-cerchio"
                                  @if ($sq['manager']['flag']) style="background-image:url('{{ $sq['manager']['flag'] }}')" @endif></span>
                            <span class="plr-num plr-num-mgr">M</span>
                        </span>
                    </div>
                @endif

                @foreach ($sq['subentrati'] as $p)
                    <div class="fz-voce">
                        <span class="fz-nome"><a href="{{ route('giocatore.show', $p['player_id']) }}">{{ $p['nome'] }}</a></span>
                        @include('partita.partials._pallino', ['p' => $p])
                    </div>
                @endforeach

                @foreach ($sq['panchina'] as $p)
                    <div class="fz-voce fz-riserva">
                        <span class="fz-nome"><a href="{{ route('giocatore.show', $p['player_id']) }}">{{ $p['nome'] }}</a></span>
                        @include('partita.partials._pallino', ['p' => $p])
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

<style>
    /* ================= CAMPO ================= */
    .fz-campo-wrap { display:flex; justify-content:center; margin-bottom:18px; }
    .fz-campo { position:relative; width:100%; max-width:430px;
                background:linear-gradient(180deg,#2f9e57 0%,#268a4b 50%,#2f9e57 100%);
                border:3px solid #e8f5ec; outline:1px solid #9fbfa9; border-radius:8px;
                padding:16px 8px; display:flex; flex-direction:column; gap:14px; }
    .fz-campo::before { content:''; position:absolute; inset:0; pointer-events:none;
                background:repeating-linear-gradient(180deg,rgba(255,255,255,.045) 0 46px,
                           rgba(0,0,0,.045) 46px 92px); border-radius:6px; }
    .fz-metacampo { position:absolute; left:0; right:0; top:50%; height:2px;
                background:rgba(255,255,255,.85); }
    .fz-cerchio { position:absolute; left:50%; top:50%; width:86px; height:86px;
                margin:-43px 0 0 -43px; border:2px solid rgba(255,255,255,.85);
                border-radius:50%; pointer-events:none; }
    .fz-area { position:absolute; left:50%; width:46%; height:40px;
               transform:translateX(-50%); border:2px solid rgba(255,255,255,.85);
               pointer-events:none; }
    .fz-area.alto  { top:-2px; border-top:0; }
    .fz-area.basso { bottom:-2px; border-bottom:0; }

    .fz-riga { position:relative; display:flex; justify-content:space-evenly;
               align-items:center; min-height:52px; }

    /* ================= PALLINO ================= */
    .plr { position:relative; display:inline-block; width:44px; height:44px; flex:none; }
    .plr-cerchio { display:block; width:44px; height:44px; border-radius:50%;
                   background:#fff center/cover no-repeat; border:2px solid #fff;
                   box-shadow:0 1px 5px rgba(0,0,0,.4); box-sizing:border-box; }
    .plr-num { position:absolute; top:-7px; left:-7px; min-width:19px; height:19px;
               padding:0 3px; border-radius:999px; background:#fff; color:#16231d;
               border:1px solid #c8d2cc; font-size:11px; font-weight:800;
               line-height:17px; text-align:center; box-sizing:border-box;
               box-shadow:0 1px 3px rgba(0,0,0,.3); z-index:2; }
    .plr-num-mgr { background:#16231d; color:#fff; border-color:#16231d; }

    .plr-basso { position:absolute; right:-8px; bottom:-6px; display:flex;
                 align-items:center; gap:1px; z-index:2; }
    .plr-ball { display:inline-flex; width:17px; height:17px; border-radius:50%;
                background:#fff; border:1px solid #c8d2cc; align-items:center;
                justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.3); }
    .plr-ball svg { width:12px; height:12px; fill:#16231d; }
    .plr-ngol { font-size:11px; font-weight:800; background:#fff; border-radius:999px;
                padding:0 3px; border:1px solid #c8d2cc; line-height:15px; }
    .plr-card { display:inline-block; width:11px; height:15px; border-radius:2px;
                border:1px solid rgba(0,0,0,.35); box-shadow:0 1px 3px rgba(0,0,0,.3); }
    .plr-card.giallo { background:#f6c700; }
    .plr-card.rosso  { background:#d1281e; }

    .plr-alto { position:absolute; top:-7px; right:-8px; display:flex; gap:1px; z-index:2; }
    .plr-arrow { display:inline-block; width:0; height:0; border-top:6px solid transparent;
                 border-bottom:6px solid transparent;
                 filter:drop-shadow(0 1px 1px rgba(0,0,0,.4)); }
    .plr-arrow.out { border-left:9px solid #d1281e; }   /* punta a destra, rossa */
    .plr-arrow.in  { border-right:9px solid #17c24a; }  /* punta a sinistra, verde */

    /* ================= ELENCO ================= */
    .fz-elenco { display:grid; grid-template-columns:1fr 1fr; }
    .fz-col { position:relative; overflow:hidden; padding:10px 12px 14px; }
    .fz-col.casa   { border-right:1px solid var(--line, #e2e8e5); }
    .fz-kit-bg { position:absolute; top:0; width:150%; height:auto; max-width:none;
                 opacity:.25; pointer-events:none; z-index:0; }
    /* colonna di casa: si vede la metà DESTRA della maglia; ospite: la metà SINISTRA */
    .fz-col.casa   .fz-kit-bg { left:0;  transform:translateX(-50%); }
    .fz-col.ospite .fz-kit-bg { right:0; transform:translateX(50%); }

    .fz-col-testa { position:relative; z-index:1; font-weight:800; font-size:15px;
                    padding-bottom:8px; margin-bottom:6px;
                    border-bottom:2px solid var(--accent, #1b9e57); }
    .fz-col.ospite .fz-col-testa { text-align:right; }

    .fz-voce { position:relative; z-index:1; display:flex; align-items:center;
               justify-content:space-between; gap:8px; padding:7px 0;
               border-bottom:1px solid var(--line, #e2e8e5); }
    .fz-voce:last-child { border-bottom:0; }
    .fz-voce .plr, .fz-voce .plr-cerchio { width:34px; height:34px; }
    .fz-voce .fz-nome { font-size:13.5px; font-weight:600; overflow-wrap:anywhere; }
    .fz-voce .fz-nome small { display:block; color:var(--muted, #6b7a72);
                              text-transform:uppercase; font-size:10px; letter-spacing:.5px; }

    /* casa: nome al bordo sinistro, pallino verso il divisorio centrale */
    .fz-col.casa .fz-voce { flex-direction:row; }
    /* ospite: pallino al bordo sinistro della colonna, nome al bordo destro */
    .fz-col.ospite .fz-voce { flex-direction:row-reverse; text-align:right; }

    .fz-mgr { background:rgba(27,158,87,.06); }
    .fz-riserva { opacity:.72; }

    @media (max-width:480px) {
        .plr, .plr-cerchio { width:38px; height:38px; }
        .fz-riga { min-height:46px; }
        .fz-voce .fz-nome { font-size:12.5px; }
        .fz-col { padding:8px 6px 10px; }
    }
</style>
