{{-- Tab Info della scheda partita: data, ora, stadio (link), arbitro (link)
     e, sotto, le maglie della partita su due colonne. --}}
@include('partials.scheda-css')

<div class="scheda">
    <div class="riga">
        <span class="lbl">Data</span>
        <span class="val">{{ $info['data'] ?: '—' }}</span>
    </div>

    <div class="riga">
        <span class="lbl">Ora</span>
        <span class="val">{{ $info['ora'] ?: '—' }}</span>
    </div>

    <div class="riga">
        <span class="lbl">Stadio</span>
        <span class="val">
            @if ($info['stadio'] && $info['stadio']['id'])
                <a href="{{ route('stadio.show', $info['stadio']['id']) }}">{{ $info['stadio']['nome'] }}</a>@if($info['stadio']['citta']), {{ $info['stadio']['citta'] }}@endif
            @elseif ($info['stadio'])
                {{ $info['stadio']['nome'] }}@if($info['stadio']['citta']), {{ $info['stadio']['citta'] }}@endif
            @else
                —
            @endif
        </span>
    </div>

    <div class="riga">
        <span class="lbl">Arbitro</span>
        <span class="val">
            @if ($info['arbitro'])
                <a href="{{ route('arbitro.show', $info['arbitro']['id']) }}">{{ $info['arbitro']['nome'] }}</a>@if($info['arbitro']['paese']) ({{ $info['arbitro']['paese'] }})@endif
            @else
                —
            @endif
        </span>
    </div>

    @if ($info['maglie']['home'] || $info['maglie']['away'])
        <div class="riga">
            <span class="lbl">Maglie</span>
            <span class="val val-maglie">
                <span class="maglie-info">
                    <span class="maglia-box">
                        @if ($info['maglie']['home'])
                            <img src="{{ $info['maglie']['home'] }}" alt="Maglia {{ $m->home_team_name }}"
                                 onerror="this.style.display='none'">
                        @endif
                        <small>{{ $m->home_team_name }}</small>
                    </span>
                    <span class="maglia-box">
                        @if ($info['maglie']['away'])
                            <img src="{{ $info['maglie']['away'] }}" alt="Maglia {{ $m->away_team_name }}"
                                 onerror="this.style.display='none'">
                        @endif
                        <small>{{ $m->away_team_name }}</small>
                    </span>
                </span>
            </span>
        </div>
    @endif
</div>

<style>
    .val-maglie { display:block; }
    .maglie-info { display:flex; gap:12px; }
    .maglia-box { flex:1; display:flex; flex-direction:column; align-items:center;
                  gap:6px; padding:12px 6px; border:1px solid var(--line, #e2e8e5);
                  border-radius:10px; background:#fafcfa; }
    .maglia-box img { width:96px; max-width:70%; height:auto;
                      image-rendering:-webkit-optimize-contrast; }
    .maglia-box small { color:var(--muted, #6b7a72); font-weight:600; }
</style>
