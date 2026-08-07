{{-- Popup dettaglio partita (traduzione di ui_game() del vecchio tema).
     Variabili: $p (rigaPartita), $pid (id univoco) --}}
@php
    $anno = \Illuminate\Support\Carbon::parse($p['date'])->format('Y');
    $dataMatch = \Illuminate\Support\Carbon::parse($p['date'])->format('d-m-Y');
    $oraMatch = $p['time'] ? substr($p['time'], 0, 5) : '';
    $stage = ucfirst($p['stage']);
    if (!empty($p['group']) && $p['group'] !== 'sconosciuto/a') {
        $stage .= ', '.$p['group'];
    }
    $risPopup = $p['ris_gol'];
@endphp

<div class="overlay-partita" id="ov-{{ $pid }}"></div>
<div class="popup-partita" id="{{ $pid }}">
    <div class="ui_game">
        <div class="data"><span>{{ $dataMatch }} {{ $oraMatch }}@if($p['stadium']), {{ $p['stadium'] }}@endif</span></div>
        <div class="torneo-riga">
            <span class="nome-coppa">Coppa del Mondo {{ $anno }}</span><br>
            <span class="stage_name">{{ $stage }}</span>
        </div>
        <div class="match">
            <div class="home">
                @if ($p['home']['flag'])
                    <img class="flag effetto_luce" src="{{ $p['home']['flag'] }}" alt=""
                         onerror="this.style.display='none'">
                @endif
                <span>@if ($p['home']['code'])<a href="{{ route('squadra.show', $p['home']['code']) }}">{{ $p['home']['name'] }}</a>@else{{ $p['home']['name'] ?: 'da definire' }}@endif</span>
            </div>
            <div class="result">
                {{ $risPopup }}
                @if ($p['dcr'])
                    <span class="dts">({{ $p['ris_rigori'] }} d.c.r.)</span>
                @elseif ($p['dts'])
                    <span class="dts">(d.t.s.)</span>
                @endif
            </div>
            <div class="away">
                <span>@if ($p['away']['code'])<a href="{{ route('squadra.show', $p['away']['code']) }}">{{ $p['away']['name'] }}</a>@else{{ $p['away']['name'] ?: 'da definire' }}@endif</span>
                @if ($p['away']['flag'])
                    <img class="flag effetto_luce" src="{{ $p['away']['flag'] }}" alt=""
                         onerror="this.style.display='none'">
                @endif
            </div>
        </div>
        @if (!empty($p['marcatori_match']))
            <div class="marcatori-popup">
                @foreach ($p['marcatori_match'] as $g)
                    <span class="marcatore">
                        @if ($g['flag'])
                            <img class="flag" src="{{ $g['flag'] }}" alt="" onerror="this.style.display='none'">
                        @endif
                        {{ $g['minuto'] }}
                        <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>
                        @if ($g['own_goal'])<em class="autogol">(autogol)</em>@endif
                        @if ($g['penalty'])<em class="autogol">(rigore)</em>@endif
                    </span>
                @endforeach
            </div>
        @endif
    </div>
    <div class="links_popup">
        <a class="link_popup" href="{{ route('partita.show', $p['match_id']) }}">Vedi partita</a>
        <span class="link_popup chiudi-popup">Torna indietro</span>
    </div>
</div>
