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
    <div class="lingue-riga">
        @php $primo = true; @endphp
        @foreach ($lingue as [$col, $wikiCol, $flagCode])
            @if (!empty($geo->$col))
                @unless ($primo)<span class="sep" aria-hidden="true">•</span>@endunless
                @php $primo = false; @endphp
                <span class="lingua">
                    @if ($f = $wc->bandieraUrl($flagCode, null))
                        <img src="{{ $f }}" alt="" onerror="this.style.display='none'">
                    @endif
                    <span class="nome">{{ $geo->$col }}</span>
                    @if (!empty($geo->$wikiCol))
                        <a class="wbtn" href="{{ $geo->$wikiCol }}" target="_blank" rel="noopener"
                           title="Wikipedia ({{ $col }})">W</a>
                    @endif
                </span>
            @endif
        @endforeach
    </div>
    <style>
        /* Nomi su un'unica riga che va a capo al bisogno. Il gruppo
           bandiera+nome+W non si spezza mai: e' un solo inline-flex con
           white-space:nowrap, quindi il ritorno a capo puo' avvenire solo
           in corrispondenza dei separatori. */
        .lingue-riga{margin:2px 0 20px;line-height:2.1;}
        .lingue-riga .lingua{display:inline-flex;align-items:center;gap:6px;
            white-space:nowrap;vertical-align:middle;}
        .lingue-riga .sep{color:var(--muted);margin:0 9px;vertical-align:middle;}
        .lingue-riga img{width:22px;height:auto;border-radius:2px;flex:none;
            box-shadow:0 1px 2px rgba(0,0,0,.25);}
        .lingue-riga .nome{font-weight:600;font-size:13px;}
        .lingue-riga .wbtn{flex:none;width:20px;height:20px;display:inline-flex;
            align-items:center;justify-content:center;background:#fff;
            border:1px solid var(--line);border-radius:4px;font-weight:800;
            font-size:11px;font-family:Georgia,serif;color:var(--ink);
            text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,.12);}
        .lingue-riga .wbtn:hover{background:#eee;text-decoration:none;}
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
