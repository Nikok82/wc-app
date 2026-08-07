{{-- Tab Squadre (Fase 3): card delle qualificate a coppie + toggle mappa
     mondiale Leaflet con i confini colorati per traguardo raggiunto.
     Frammento caricato via fetch: niente <script>, l'interattivita' e la
     mappa sono gestite dallo script delegato della pagina torneo.
     Le card linkano la scheda squadra-anno (es. /squadra/ITA-1990). --}}
@php
    // Legenda: solo i traguardi presenti in questo torneo, in ordine di importanza
    $ordineLegenda = array_keys(\App\Services\TorneoService::COLORI_PERFORMANCE);
    $presenti = collect($squadre)->pluck('performance')->unique()->all();
    $legenda = [];
    foreach ($ordineLegenda as $p) {
        if (in_array($p, $presenti, true)) {
            $legenda[$p] = \App\Services\TorneoService::COLORI_PERFORMANCE[$p][0];
        }
    }
@endphp

<div id="torneo-squadre">

    <div class="sub-bottoni">
        <span class="sub-btn sq-toggle active" data-view="elenco"
              title="Elenco squadre">{!! \App\Support\Icons::svg('world-teams') !!}</span>
        <span class="sub-btn sq-toggle" data-view="mappa"
              title="Mappa">{!! \App\Support\Icons::svg('world') !!}</span>
    </div>

    <div class="sq-view" data-view="elenco">
        <div class="tournament-teams">
            @foreach ($squadre as $s)
                <a class="box-team" @if($s['squadra_url']) href="{{ $s['squadra_url'] }}" @endif
                   @if($s['flag']) style="background-image:url('{{ $s['flag'] }}')" @endif
                   title="{{ $s['team_name'] }}@if($s['performance']) — {{ $s['performance'] }}@endif">
                    <span class="box-team-name">{{ $s['team_name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="sq-view" data-view="mappa" style="display:none">
        <div class="mappa-torneo" data-paesi="{{ json_encode($paesi) }}"></div>

        @if (!empty($legenda))
            <div class="legenda-mappa">
                @foreach ($legenda as $perf => $colore)
                    <span class="voce"><i style="background:{{ $colore }}"></i>{{ $perf }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
    #torneo-squadre .sub-bottoni{display:flex;flex-direction:row;justify-content:flex-end;gap:6px;
        background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
        padding:5px 10px;border-radius:0 10px 0 10px;margin-bottom:16px;}
    #torneo-squadre .sub-btn{display:flex;align-items:center;justify-content:center;cursor:pointer;
        width:40px;height:40px;padding:5px;border-radius:5px;background:#fff;color:#000;}
    /* NB: la regola vale SOLO per l'elemento <svg>: le <image> interne usano
       le unità del viewBox (64) e ridimensionarle a 26 le faceva apparire
       minuscole dentro il riquadro (fix 25/07). */
    #torneo-squadre .sub-btn svg{width:26px;height:26px;}
    #torneo-squadre .sub-btn.active{border:1px solid var(--giallo);
        box-shadow:0 0 .1rem var(--giallo),0 0 .5rem #08ff07aa;}

    /* card squadre a coppie (grid 2 colonne, come il vecchio sito su mobile) */
    #torneo-squadre .tournament-teams{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    #torneo-squadre .box-team{position:relative;display:block;height:86px;
        border-radius:0 20px 0 0;border:1px solid var(--verde-scuro);border-bottom:0;
        background-size:cover;background-position:center;background-color:#e8ede9;
        overflow:visible;text-decoration:none;color:inherit;
        box-shadow:0 1px 4px rgba(0,0,0,.18);}
    #torneo-squadre .box-team-name{position:absolute;left:-1px;right:-1px;bottom:-21px;
        height:22px;display:flex;justify-content:flex-end;align-items:center;
        background:#fff;border:1px solid var(--verde-scuro);border-top:0;
        border-radius:0 0 0 20px;padding:1px 8px;font-size:13px;font-weight:600;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    #torneo-squadre .tournament-teams{padding-bottom:24px;row-gap:36px;}

    /* mappa */
    #torneo-squadre .mappa-torneo{width:100%;height:380px;border-radius:10px;
        border:1px solid var(--line);background:#eef2ef;position:relative;z-index:1;}
    #torneo-squadre .legenda-mappa{display:flex;flex-wrap:wrap;gap:6px 14px;
        margin-top:10px;font-size:12px;color:var(--ink);}
    #torneo-squadre .legenda-mappa .voce{display:inline-flex;align-items:center;gap:5px;
        text-transform:capitalize;}
    #torneo-squadre .legenda-mappa i{width:14px;height:14px;border-radius:3px;
        display:inline-block;border:1px solid rgba(0,0,0,.35);}

    @media (min-width:561px){
        #torneo-squadre .tournament-teams{grid-template-columns:repeat(3,1fr);}
        #torneo-squadre .mappa-torneo{height:440px;}
    }
</style>
