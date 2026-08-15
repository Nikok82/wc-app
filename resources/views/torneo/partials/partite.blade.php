{{-- Tab Partite (Fase 2): sub-bottoni fase, gironi con classifiche e
     marcatori, fase a eliminazione (elenco + bracket grafico con toggle).
     Frammento caricato via fetch: niente <script> (non verrebbe eseguito),
     l'interattivita' e' delegata allo script della pagina torneo. --}}
@php
    $apertura = $imp['solo_ko'] ? 'ko' : 'f1';
    $haKo = ! empty($rounds) || $gironeFinale->isNotEmpty();
@endphp

{{-- A2 (15/08): riga dei gruppi fra la barra verde delle icone e il primo
     girone. Ogni lettera e' un'ancora al girone corrispondente. --}}
@php
    $barraGruppi = function ($gruppi, $ctx) {
        return collect($gruppi)->map(fn ($g) => [
            'etichetta' => trim(explode(' ', $g['group_name'], 2)[1] ?? $g['group_name']),
            'ancora'    => 'girone-'.$ctx.'-'.\Illuminate\Support\Str::slug($g['group_name']),
        ])->all();
    };
@endphp

<div id="torneo-partite" data-apertura="{{ $apertura }}">

    <div class="sub-bottoni">
        @if ($fase1->isNotEmpty())
            <span class="sub-btn {{ $apertura === 'f1' ? 'active' : '' }}" data-show="f1"
                  title="Fase a gruppi">{!! \App\Support\Icons::svg('1step') !!}</span>
        @endif
        @if ($fase2->isNotEmpty())
            <span class="sub-btn" data-show="f2"
                  title="Seconda fase a gruppi">{!! \App\Support\Icons::svg('2step') !!}</span>
        @endif
        @if ($haKo)
            <span class="sub-btn {{ $apertura === 'ko' ? 'active' : '' }}" data-show="ko"
                  title="Fase a eliminazione">{!! \App\Support\Icons::svg('competition') !!}</span>
        @endif
        <span class="sub-btn" data-show="all" title="Tutte le fasi">{!! \App\Support\Icons::svg('all') !!}</span>
    </div>

    {{-- Fase a gruppi 1 --}}
    @if ($fase1->isNotEmpty())
        <div class="sezione-fase" data-sez="f1" @if($apertura !== 'f1') style="display:none" @endif>
            @include('torneo.partials._barra_gruppi', ['voci' => $barraGruppi($fase1, 'f1')])
            @foreach ($fase1 as $g)
                @include('torneo.partials._girone', ['g' => $g, 'ctx' => 'f1'])
            @endforeach
        </div>
    @endif

    {{-- Seconda fase a gruppi (1974-82) --}}
    @if ($fase2->isNotEmpty())
        <div class="sezione-fase" data-sez="f2" style="display:none">
            @include('torneo.partials._barra_gruppi', ['voci' => $barraGruppi($fase2, 'f2')])
            @foreach ($fase2 as $g)
                @include('torneo.partials._girone', ['g' => $g, 'ctx' => 'f2'])
            @endforeach
        </div>
    @endif

    {{-- Fase finale: girone finale 1950 oppure eliminazione diretta --}}
    @if ($haKo)
        <div class="sezione-fase" data-sez="ko" @if($apertura !== 'ko') style="display:none" @endif>

            @if ($gironeFinale->isNotEmpty())
                @foreach ($gironeFinale as $g)
                    @include('torneo.partials._girone', ['g' => $g, 'ctx' => 'gf'])
                @endforeach
            @endif

            @if (! empty($rounds))
                @if (! empty($bracket))
                    <div class="ko-toggle-box">
                        <span class="sub-btn ko-toggle active" data-view="list"
                              title="Elenco">{!! \App\Support\Icons::svg('list') !!}</span>
                        <span class="sub-btn ko-toggle" data-view="playoff"
                              title="Tabellone">{!! \App\Support\Icons::svg('playoff') !!}</span>
                    </div>
                @endif

                <div class="ko-view" data-view="list">
                    @foreach ($rounds as $round)
                        <div class="round">
                            <div class="label">{{ $round['label'] }}</div>
                            <div class="partite-list">
                                @foreach ($round['partite'] as $p)
                                    @include('torneo.partials._match_card', [
                                        'p'     => $p,
                                        'pid'   => 'pop-ko-'.$p['match_id'],
                                        // Nella fase a eliminazione chi perde
                                        // esce dal torneo: il nome va barrato.
                                        'barra' => true,
                                    ])
                                @endforeach
                            </div>
                            @include('torneo.partials._marcatori_box', ['marc' => $round['marcatori']])
                        </div>
                    @endforeach
                </div>

                @if (! empty($bracket))
                    <div class="ko-view" data-view="playoff" style="display:none">
                        @include('torneo.partials._bracket_fotmob')
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>

<style>
    /* ---- sub-bottoni fase (1step/2step/competition/all) ---- */
    #torneo-partite .sub-bottoni{display:flex;flex-direction:row;justify-content:flex-end;gap:6px;
        background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
        padding:5px 10px;border-radius:0 10px 0 10px;margin-bottom:14px;}
    #torneo-partite .sub-btn{display:flex;align-items:center;justify-content:center;cursor:pointer;
        width:40px;height:40px;padding:5px;border-radius:5px;background:#fff;color:#000;
        transition:box-shadow .15s,filter .15s;}
    #torneo-partite .sub-btn svg,#torneo-partite .sub-btn image{width:26px;height:26px;}
    #torneo-partite .sub-btn.active{border:1px solid var(--giallo);
        box-shadow:0 0 .1rem var(--giallo),0 0 .5rem #08ff07aa;}

    /* ---- girone ---- */
    #torneo-partite .groups{display:flex;flex-direction:column;margin:38px 0 26px;
        border-top:2px solid var(--verde);border-bottom:2px solid var(--verde);
        border-radius:0 40px 0 40px;background:#fbfdfb;}
    #torneo-partite .title-group{position:relative;top:-19px;align-self:flex-end;right:18px;
        color:var(--verde);text-shadow:0 1px #fff;font-weight:500;display:flex;align-items:center;}
    #torneo-partite .title-group .g1{font-size:18px;}
    #torneo-partite .title-group .g2{font-size:56px;margin-left:16px;line-height:1;}
    #torneo-partite .title-group .g2.finale-1950{font-size:38px;}
    #torneo-partite .body-group{display:flex;flex-direction:column;padding:0 10px 18px;}
    #torneo-partite .groups-matches .label{font-size:20px;font-weight:700;
        border-bottom:1px dotted #000;padding:4px 0 4px 8px;margin-bottom:8px;}

    /* ---- card partita ---- */
    #torneo-partite .partite-list{display:flex;flex-direction:column;}
    /* Impaginazione "A": data e luogo sopra, squadre al centro,
       marcatori sotto. La card e' una colonna, non piu' una riga. */
    #torneo-partite .matches{display:flex;flex-direction:column;cursor:pointer;
        margin-bottom:8px;border-radius:6px;padding:7px 10px;min-width:0;}
    #torneo-partite .matches:nth-child(odd){background:#ececec;}
    #torneo-partite .matches:nth-child(even){background:#f6f6f6;}
    #torneo-partite .mt-quando{font-size:12px;color:#6b7a72;margin-bottom:5px;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    #torneo-partite .mt-corpo{display:flex;flex-direction:row;align-items:center;min-width:0;}
    #torneo-partite .mt-gol{display:flex;flex-wrap:wrap;gap:3px 12px;margin-top:6px;
        padding-top:5px;border-top:1px dotted #bbb;font-size:12px;color:#6b7a72;min-width:0;}
    #torneo-partite .mt-gol-voce{display:inline-flex;align-items:center;gap:4px;
        white-space:normal;max-width:100%;}
    #torneo-partite .mt-gol-min{font-variant-numeric:tabular-nums;min-width:26px;
        text-align:right;color:#8b968f;}
    #torneo-partite .mt-gol-fl{width:15px;height:10px;object-fit:cover;border-radius:1px;
        flex:none;box-shadow:0 1px 1px rgba(0,0,0,.2);}
    #torneo-partite .mt-gol-nome{overflow:hidden;text-overflow:ellipsis;color:#16231d;
        text-decoration:none;}
    #torneo-partite a.mt-gol-nome:hover{text-decoration:underline;color:var(--verde);}
    #torneo-partite .mt-gol-nota{color:#8b968f;font-style:italic;}
    #torneo-partite .gara-bracket{flex:1;padding:2px 12px 2px 2px;border-right:1px solid #999;min-width:0;}
    #torneo-partite .gara-bracket .home,#torneo-partite .gara-bracket .away{display:flex;
        flex-direction:row;align-items:center;margin:4px 0;min-width:0;}
    #torneo-partite .gara-bracket .flag{width:25px;height:25px;border-radius:50%;object-fit:cover;
        margin-right:8px;flex:none;box-shadow:0 1px 2px rgba(0,0,0,.3);}
    #torneo-partite .gara-bracket .team{overflow:hidden;}
    #torneo-partite .gara-bracket .team span{white-space:nowrap;overflow:hidden;
        text-overflow:ellipsis;display:block;}
    #torneo-partite .gara-bracket .bold{font-weight:700;}
    #torneo-partite .gara-bracket .lose{text-decoration:line-through;
        text-decoration-color:#595959;color:#666;}
    #torneo-partite .risult{position:relative;flex:none;width:64px;align-self:center;
        display:flex;flex-direction:column;align-items:center;padding:0 8px;}
    #torneo-partite .risult .ris{text-align:center;border:2px solid #000;background:#fff;
        padding:6px 0;width:46px;font-weight:700;}
    #torneo-partite .risult .ris-2{font-size:11px;margin-top:2px;color:#333;text-align:center;}

    /* ---- classifica girone ---- */
    #torneo-partite .body-table{margin:14px 0 6px;}
    #torneo-partite .table-gironi{display:flex;flex-direction:column;font-size:14px;
        background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden;}
    #torneo-partite .table-gironi .row{display:flex;flex-direction:row;align-items:center;}
    #torneo-partite .table-gironi .row.head{background:#d9d9d9;font-weight:700;}
    #torneo-partite .table-gironi .cel{flex:1;padding:6px 2px;text-align:center;
        border-bottom:1px dotted #bbb;display:flex;justify-content:center;align-items:center;min-width:0;}
    #torneo-partite .table-gironi .row:last-child .cel{border-bottom:0;}
    #torneo-partite .table-gironi .c1{flex:0 0 26px;}
    #torneo-partite .table-gironi .c2{flex:3;justify-content:flex-start;text-align:left;gap:5px;}
    #torneo-partite .table-gironi .c2 a{display:flex;align-items:center;gap:5px;min-width:0;color:inherit;}
    #torneo-partite .table-gironi .c2 .flag{width:20px;height:20px;border-radius:50%;
        object-fit:cover;flex:none;}
    #torneo-partite .table-gironi .c2 .tcode{display:none;font-size:12px;}
    #torneo-partite .table-gironi .advanced{background:rgba(0,200,0,.35);}

    /* ---- marcatori espandibili ---- */
    #torneo-partite .marc-box{margin:10px 0 0;border-top:1px solid #ccc;}
    #torneo-partite .marc-box summary{display:grid;grid-template-columns:26px 46px 1fr;
        gap:3px;cursor:pointer;font-weight:700;padding:6px 2px;list-style:none;}
    #torneo-partite .marc-box summary::-webkit-details-marker{display:none;}
    #torneo-partite .marc-box .arrow{transition:transform .4s;display:inline-block;}
    #torneo-partite .marc-box[open] .arrow{transform:rotate(-180deg);}
    #torneo-partite .marc-grid{display:grid;grid-template-columns:72px 1fr;gap:3px;}
    #torneo-partite .marc-grid .grid-cell{padding:6px 2px;border-bottom:1px solid #eee;
        font-size:13px;}
    #torneo-partite .marc-grid .gol{font-weight:700;text-align:center;}
    #torneo-partite .marcatore{display:inline-flex;align-items:center;gap:4px;
        margin:2px 10px 2px 0;white-space:nowrap;}
    #torneo-partite .marcatore .flag{width:18px;height:18px;border-radius:50%;object-fit:cover;}
    #torneo-partite .autogol{font-size:.75em;font-style:italic;}

    /* ---- fase a eliminazione: elenco ---- */
    #torneo-partite .round{margin:26px 0;padding:2px 8px 12px;border-radius:8px;}
    #torneo-partite .round:nth-child(odd){background:#f2f4f2;}
    #torneo-partite .round .label{font-size:20px;font-weight:700;
        border-bottom:1px dotted #000;padding:8px 0 4px 6px;margin-bottom:10px;}

    /* ---- toggle elenco / tabellone ---- */
    #torneo-partite .ko-toggle-box{display:flex;justify-content:center;gap:8px;
        width:fit-content;margin:6px auto 4px;padding:5px;border-radius:0 0 10px 10px;
        background:linear-gradient(142deg,#045e03 -100%,#57c785 58%,#045e03 100%);}

    /* ================= BRACKET FOTMOB ================= */
    .fm-bracket{position:relative;}

    /* pillole dei turni */
    .fm-pills{display:flex;gap:8px;overflow-x:auto;padding:4px 2px 12px;scrollbar-width:none;}
    .fm-pills::-webkit-scrollbar{display:none;}
    .fm-pill{flex:none;padding:8px 16px;border-radius:999px;background:#fff;
        border:1px solid var(--line);font-weight:700;font-size:13px;cursor:pointer;color:var(--ink);}
    .fm-pill.active{background:linear-gradient(142deg,#045e03 0%,#57c785 100%);
        color:#fff;border-color:transparent;}

    /* colonne scorrevoli (scroll-snap: swipe su mobile, affiancate su desktop) */
    .fm-cols{display:flex;flex-direction:row;gap:22px;overflow-x:auto;
        scroll-snap-type:x mandatory;padding:2px 2px 14px;scroll-padding-left:2px;}
    .fm-col{flex:0 0 86%;max-width:330px;scroll-snap-align:start;}
    @media (min-width:700px){ .fm-col{flex:0 0 310px;} }
    .fm-col-titolo{font-weight:800;color:var(--verde-scuro);font-size:14px;
        padding:2px 2px 10px;text-transform:uppercase;letter-spacing:.4px;}

    /* coppie di card + graffa verso il turno successivo (tagliata a bordo) */
    .fm-pair{position:relative;margin-bottom:16px;padding-right:8px;}
    .fm-pair.con-graffa::after{content:"";position:absolute;top:16%;bottom:16%;right:-13px;
        width:13px;border:3px solid var(--verde2);border-left:0;border-radius:0 14px 14px 0;}

    /* card partita FotMob */
    .fm-card{background:#fff;border:1px solid var(--line);border-radius:12px;
        padding:9px 11px;margin-bottom:10px;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.07);}
    .fm-card:hover{border-color:var(--verde2);}
    .fm-stadio{display:flex;align-items:center;gap:7px;font-size:11px;color:var(--muted);
        margin-bottom:6px;min-width:0;}
    .fm-stadio span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .fm-stadio i{width:3px;height:14px;border-radius:2px;flex:none;
        background:linear-gradient(180deg,#045e03,#57c785);}
    .fm-sq{display:flex;align-items:center;gap:9px;padding:4px 0;min-width:0;}
    .fm-flag{width:24px;height:24px;border-radius:50%;object-fit:cover;flex:none;
        box-shadow:0 1px 2px rgba(0,0,0,.25);background:#cfd6d1;}
    .fm-flag.scudo,.fm-flag.rotta{background:#cfd6d1;display:inline-block;}
    .fm-nome{flex:1;font-weight:600;font-size:14px;min-width:0;overflow:hidden;
        text-overflow:ellipsis;white-space:nowrap;}
    .fm-sq.vince .fm-nome,.fm-sq.vince .fm-punti{font-weight:800;}
    .fm-sq.eliminata .fm-nome{text-decoration:line-through;color:#7c8a80;font-weight:500;}
    .fm-punti{font-weight:700;font-variant-numeric:tabular-nums;width:24px;
        text-align:right;flex:none;}
    .fm-nota{font-size:11px;color:var(--muted);text-align:right;padding:3px 2px 0;}
    .fm-nota.data{color:var(--verde-scuro);font-weight:600;}
    .fm-nota .risultato-replay{font-weight:700;color:var(--ink);}
    .fm-nota .nota-replay{font-size:10px;}

    /* ---- vista albero completo ---- */
    .fm-tree-scroll{overflow:auto;background:#f6f8f6;border-radius:12px;
        border:1px solid var(--line);}
    .fm-tree{display:flex;flex-direction:row;gap:28px;min-width:max-content;
        align-items:stretch;padding:14px;}
    .fm-tcol{display:flex;flex-direction:column;}
    .fm-tcol-titolo{font-weight:800;font-size:12px;color:var(--verde-scuro);
        text-transform:uppercase;letter-spacing:.4px;padding-bottom:10px;text-align:center;}
    .fm-tcol-corpo{flex:1;display:flex;flex-direction:column;justify-content:space-around;gap:12px;}
    .fm-tpair{position:relative;display:flex;flex-direction:column;
        justify-content:space-around;gap:12px;flex:1;padding-right:6px;}
    .fm-tpair.con-graffa::after{content:"";position:absolute;top:25%;bottom:25%;right:-16px;
        width:14px;border:2.5px solid var(--verde2);border-left:0;border-radius:0 10px 10px 0;}

    .fm-mini{background:#fff;border:1px solid var(--line);border-radius:9px;
        padding:5px 8px;cursor:pointer;min-width:122px;box-shadow:0 1px 3px rgba(0,0,0,.06);}
    .fm-mini:hover{border-color:var(--verde2);}
    .fm-mini .r{display:flex;align-items:center;gap:6px;padding:2px 0;font-size:12px;}
    .fm-mini img,.fm-mini .scudo{width:17px;height:17px;border-radius:50%;object-fit:cover;
        flex:none;background:#cfd6d1;display:inline-block;}
    .fm-mini .cod{flex:1;font-weight:700;}
    .fm-mini .r.eliminata .cod{text-decoration:line-through;color:#7c8a80;font-weight:500;}
    .fm-mini b{font-variant-numeric:tabular-nums;}
    .fm-mini .nota{font-size:9.5px;color:var(--muted);text-align:right;}

    /* variante 3 (bracket a 32): primi turni compattati */
    .fm-tcol.compatta .fm-mini{min-width:94px;padding:3px 6px;}
    .fm-tcol.compatta .fm-mini .r{font-size:10px;gap:4px;padding:1px 0;}
    .fm-tcol.compatta .fm-mini img,.fm-tcol.compatta .fm-mini .scudo{width:13px;height:13px;}

    /* variante 4 (bracket a 32): sedicesimi come lista compatta sopra l'albero */
    .fm-lista-compatta{margin-bottom:14px;}
    .fm-lista-titolo{font-weight:800;font-size:12px;color:var(--verde-scuro);
        text-transform:uppercase;letter-spacing:.4px;padding:0 2px 8px;}
    .fm-lista-griglia{display:grid;grid-template-columns:repeat(auto-fill,minmax(132px,1fr));gap:8px;}

    /* card FINALE + trofeo + campione + ramo 3° posto */
    .colonna-finale .badge-finale{background:#ffd400;color:#1c2a20;font-weight:900;
        border-radius:6px;padding:3px 14px;letter-spacing:1.2px;font-size:12px;}
    .fm-finale-blocco{display:flex;flex-direction:column;gap:14px;align-items:center;
        justify-content:center;height:100%;}
    .fm-finale-blocco .fm-mini{min-width:150px;border:2px solid #ffd400;}
    .fm-campione{display:flex;align-items:center;gap:8px;font-weight:800;font-size:14px;}
    .fm-campione .trofeo svg{width:30px;height:30px;color:#c9a400;}
    .fm-campione img{width:26px;height:26px;border-radius:50%;object-fit:cover;
        box-shadow:0 1px 2px rgba(0,0,0,.3);}
    .fm-campione .ignoto{font-size:22px;color:var(--muted);}
    .fm-terzo{position:relative;border-top:1px dashed #b9c4bd;padding-top:16px;margin-top:8px;}
    .fm-terzo .fm-mini{border:1px solid #b08d57;}
    .badge-terzo{position:absolute;top:-10px;left:0;background:#b08d57;color:#fff;
        font-size:11px;font-weight:800;border-radius:6px;padding:1px 9px;}

    /* bottone circolare flottante per il cambio vista */
    .fm-switch{position:sticky;bottom:74px;float:right;display:flex;align-items:center;
        justify-content:center;width:54px;height:54px;border-radius:50%;border:0;cursor:pointer;
        background:linear-gradient(142deg,#045e03 0%,#57c785 100%);
        box-shadow:0 4px 14px rgba(0,0,0,.35);color:#ffff00;z-index:30;margin-top:8px;}
    .fm-switch svg{width:26px;height:26px;}
    .fm-switch .ico-turni{display:none;}
    .fm-switch.vista-albero .ico-albero{display:none;}
    .fm-switch.vista-albero .ico-turni{display:flex;}

    /* ---- bracket grafico ---- */
    #torneo-partite .bracket-wrap{display:flex;flex-direction:column;gap:2px;padding:14px 4px;
        border-radius:10px;box-shadow:3px 3px 3px #acacac;
        background-image:radial-gradient(farthest-corner at 40px 30px,#ededed,#dcdcdc,#c9c9c9);}
    #torneo-partite .br-row{display:flex;flex-direction:row;justify-content:space-around;
        align-items:center;width:100%;}
    #torneo-partite .bflag{display:inline-block;border-radius:50%;background-size:cover;
        background-position:center;background-color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.4);
        width:30px;height:30px;margin:4px 1px;flex:none;}
    #torneo-partite .br-flags[data-n="16"] .bflag{width:17px;height:17px;}
    #torneo-partite .br-flags[data-n="12"] .bflag,
    #torneo-partite .br-flags[data-n="8"] .bflag{width:24px;height:24px;}
    #torneo-partite .br-flags[data-n="4"] .bflag{width:30px;height:30px;}
    #torneo-partite .br-flags[data-n="2"] .bflag{width:36px;height:36px;}
    #torneo-partite .bflag.grande{width:44px;height:44px;}
    #torneo-partite .br-box{display:inline-flex;flex-direction:column;align-items:center;
        justify-content:center;cursor:pointer;background:#fff;border:1px solid #000;
        min-width:30px;padding:2px 3px;line-height:1.15;position:relative;}
    #torneo-partite .br-box::before,#torneo-partite .br-box::after{content:"";
        position:absolute;left:50%;width:2px;height:7px;background:#000;}
    #torneo-partite .br-box::before{top:-8px;}
    #torneo-partite .br-box::after{bottom:-8px;}
    #torneo-partite .br-box .r1{font-weight:900;font-size:10px;white-space:nowrap;}
    #torneo-partite .br-box .r2{font-weight:500;font-size:9px;white-space:nowrap;}
    #torneo-partite .br-box .r3{font-size:8px;}
    #torneo-partite .br-finale{display:flex;flex-direction:row;justify-content:center;
        align-items:center;gap:18px;margin:10px 0 2px;}
    #torneo-partite .br-finale-label{text-align:center;font-weight:700;font-size:13px;
        color:var(--verde-scuro);margin-bottom:8px;}

    /* ---- popup partita ---- */
    .overlay-partita{display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:9998;}
    .popup-partita{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
        z-index:9999;width:min(420px,94vw);max-height:88vh;overflow:auto;text-align:center;
        background:#1c2a20;border-radius:12px;padding:16px 14px;box-shadow:0 8px 30px rgba(0,0,0,.6);}
    .popup-partita.aperto,.overlay-partita.aperto{display:block;}
    .popup-partita .ui_game{display:flex;flex-direction:column;color:#fff;}
    .popup-partita .data,.popup-partita .torneo-riga{margin:4px auto;font-size:13px;}
    .popup-partita .nome-coppa{font-weight:700;}
    .popup-partita .stage_name{font-size:12px;opacity:.85;}
    .popup-partita .match{display:flex;flex-direction:column;background:#ededed;color:#000;
        border-radius:0 24px 0 24px;border:1px solid #000;margin:10px 0;padding:10px;}
    .popup-partita .match .home,.popup-partita .match .away{display:flex;align-items:center;
        gap:10px;min-width:0;}
    .popup-partita .match .home{justify-content:flex-start;}
    .popup-partita .match .away{justify-content:flex-end;}
    /* D2: la bandiera e' avvolta da un'ancora verso la scheda squadra-anno;
       senza flex:none sul contenitore l'ancora si comprimerebbe e la
       bandiera perderebbe la sua larghezza fissa. */
    .popup-partita .match .home > a,.popup-partita .match .away > a{flex:none;
        display:inline-flex;line-height:0;}
    .popup-partita .match .flag{width:44px;height:auto;border-radius:4px;flex:none;
        box-shadow:0 1px 3px rgba(0,0,0,.35);}
    .popup-partita .match span{font-size:22px;overflow:hidden;text-overflow:ellipsis;
        white-space:nowrap;}
    .popup-partita .match a{color:#000;}
    .popup-partita .result{font-size:2.2em;font-weight:600;padding:8px 0;}
    .popup-partita .result .dts{font-size:14px;font-weight:400;}
    .popup-partita .marcatori-popup{display:flex;flex-direction:column;align-items:flex-end;
        gap:2px;padding:4px 2px;font-size:12px;}
    .popup-partita .marcatori-popup .marcatore{display:inline-flex;align-items:center;gap:4px;}
    .popup-partita .marcatori-popup .flag{width:16px;height:16px;border-radius:50%;object-fit:cover;}
    .popup-partita .marcatori-popup a{color:#fff;}
    .popup-partita .links_popup{display:flex;justify-content:center;gap:10px;margin-top:8px;}
    .popup-partita .link_popup{display:inline-block;padding:7px 4px;width:44%;cursor:pointer;
        background:#cecece;color:#000;border-radius:4px;font-size:14px;text-decoration:none;}
    .popup-partita .link_popup:hover{background:#b0b0b0;text-decoration:none;}

    @media (max-width:560px){
        #torneo-partite .table-gironi .c2 .tname{display:none;}
        #torneo-partite .table-gironi .c2 .tcode{display:inline;}
        #torneo-partite .table-gironi .c9{display:none;}
        #torneo-partite .title-group .g2{font-size:44px;}
        #torneo-partite .gara-bracket{padding:4px 8px;}
    }
    @media (min-width:561px){
        #torneo-partite .body-group{flex-direction:column;}
    }
</style>
