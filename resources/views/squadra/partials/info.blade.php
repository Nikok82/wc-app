@inject('wc', 'App\Services\WcService')

@if ($geo)
    {{-- Nome del paese nelle 11 lingue principali (da awc_geo_demo),
         con bandierina della lingua e link W alla Wikipedia relativa. --}}
    @php
        $lingue = [
            // [colonna nome, colonna wiki, codice bandiera]
            ['italiano',   'wiki_it', 'ITA'],
            ['inglese',    'wiki_en', 'ENG'],
            ['spagnolo',   'wiki_es', 'ESP'],
            ['tedesco',    'wiki_de', 'GER'],
            ['francese',   'wiki_fr', 'FRA'],
            ['portoghese', 'wiki_pr', 'POR'],
            ['olandese',   'wiki_nl', 'NLD'],
            ['russo',      'wiki_ru', 'RUS'],
            ['arabo',      'wiki_ar', 'SAU'],
            ['cinese',     'wiki_ch', 'CHN'],
            ['giapponese', 'wiki_jp', 'JPN'],
        ];
    @endphp
    <div class="lingue-grid">
        @foreach ($lingue as [$col, $wikiCol, $flagCode])
            @if (!empty($geo->$col))
                <div class="lingua">
                    @if ($f = $wc->bandieraUrl($flagCode, null))
                        <img src="{{ $f }}" alt="" onerror="this.style.display='none'">
                    @endif
                    <span class="nome">{{ $geo->$col }}</span>
                    @if (!empty($geo->$wikiCol))
                        <a class="wbtn" href="{{ $geo->$wikiCol }}" target="_blank" rel="noopener"
                           title="Wikipedia ({{ $col }})">W</a>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
    <style>
        .lingue-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 18px;margin:2px 0 20px;}
        .lingua{display:flex;align-items:center;gap:8px;background:#f4f6f5;
            border-radius:6px;padding:5px 8px;min-height:30px;}
        .lingua img{width:22px;height:auto;border-radius:2px;flex:none;
            box-shadow:0 1px 2px rgba(0,0,0,.25);}
        .lingua .nome{flex:1;font-weight:600;font-size:13px;overflow:hidden;
            text-overflow:ellipsis;white-space:nowrap;}
        .lingua .wbtn{flex:none;width:22px;height:22px;display:flex;align-items:center;
            justify-content:center;background:#fff;border:1px solid var(--line);
            border-radius:4px;font-weight:800;font-size:12px;font-family:Georgia,serif;
            color:var(--ink);text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,.12);}
        .lingua .wbtn:hover{background:#eee;text-decoration:none;}
        @media (max-width:340px){ .lingue-grid{grid-template-columns:1fr;} }
    </style>
@endif

<div class="info-grid">
    <div class="info-row">
        <span class="lbl">Confederazione</span>
        <span class="val">{{ $team->confederation_name ?? '—' }}@if($team->confederation_code) ({{ $team->confederation_code }})@endif</span>
    </div>
    <div class="info-row">
        <span class="lbl">Federazione</span>
        <span class="val">{{ $team->federation_name ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="lbl">Regione</span>
        <span class="val">{{ $team->region_name ?? '—' }}</span>
    </div>

    @if ($geo)
        @if ($geo->capital_ita)
            <div class="info-row"><span class="lbl">Capitale</span><span class="val">{{ $geo->capital_ita }}</span></div>
        @endif
        @if ($geo->area)
            <div class="info-row"><span class="lbl">Superficie</span><span class="val">{{ $geo->area }}</span></div>
        @endif
        @if ($geo->population)
            <div class="info-row"><span class="lbl">Popolazione</span><span class="val">{{ $geo->population }}</span></div>
        @endif
        @if ($geo->govern)
            <div class="info-row"><span class="lbl">Governo</span><span class="val">{{ $geo->govern }}</span></div>
        @endif
        @if ($geo->pil)
            <div class="info-row"><span class="lbl">PIL</span><span class="val">{{ $geo->pil }}</span></div>
        @endif
    @endif

    @if ($team->mens_team_wikipedia_link)
        <div class="info-row">
            <span class="lbl">Wikipedia</span>
            <span class="val"><a href="{{ $team->mens_team_wikipedia_link }}" target="_blank" rel="noopener">apri ›</a></span>
        </div>
    @endif
</div>

@if (!empty($geojsonUrl))
    <div class="mappa-nazione" data-geojson="{{ $geojsonUrl }}" data-nome="{{ $team->team_name }}"></div>
    <style>
        .mappa-nazione{width:100%;height:280px;margin-top:16px;border-radius:10px;
            border:1px solid var(--line);background:#eef2ef;position:relative;z-index:1;}
        @media (min-width:700px){ .mappa-nazione{height:340px;} }
    </style>
@endif
