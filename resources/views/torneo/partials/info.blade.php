@php
    // Ordine visivo del podio: 2° - 1° - 3°
    $ordine = [2 => 'secondo', 1 => 'primo', 3 => 'terzo'];
@endphp

@if (!empty(array_filter($podio)))
    <div class="podio">
        @foreach ($ordine as $pos => $cls)
            @if (isset($podio[$pos]))
                @php $p = $podio[$pos]; @endphp
                <div class="posto {{ $cls }}">
                    <div class="team">
                        <div class="container-flag-podium">
                            @if ($p['flag'])
                                @if ($p['squadra_url'])<a href="{{ $p['squadra_url'] }}">@endif
                                    <img class="flag" src="{{ $p['flag'] }}" alt="{{ $p['team_name'] }}"
                                         onerror="this.style.display='none'">
                                @if ($p['squadra_url'])</a>@endif
                            @endif
                            @if ($p['squadra_url'])
                                <a class="info-link" href="{{ $p['squadra_url'] }}" title="{{ $p['team_name'] }}">i</a>
                            @endif
                        </div>
                    </div>
                    <div class="bar"><span>{{ $pos }}</span></div>
                    <div class="nome">
                        @if ($p['squadra_url'])
                            <a href="{{ $p['squadra_url'] }}">{{ $p['team_name'] }}</a>
                        @else
                            {{ $p['team_name'] }}
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif

<div class="titolo-sezione">Informazioni</div>

<div class="info_box">
    @foreach ($righe as $label => $valore)
        <div class="single_info_box capital">
            <div class="left"><span>{{ $label }}</span></div>
            <div class="right"><span>{{ $valore }}</span></div>
        </div>
    @endforeach

    @foreach ($premi as $premio)
        <div class="single_info_box capital">
            <div class="left"><span>{{ $premio['premio'] }}</span></div>
            <div class="right">
                @foreach ($premio['vincitori'] as $v)
                    <span class="premio-vincitore">
                        @if ($v['player_url'])
                            <a href="{{ $v['player_url'] }}">{{ $v['nome'] }}</a>
                        @else
                            {{ $v['nome'] }}
                        @endif
                        @if ($v['flag'])
                            <img class="flag" src="{{ $v['flag'] }}" alt="{{ $v['team_code'] }}"
                                 onerror="this.style.display='none'">
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
