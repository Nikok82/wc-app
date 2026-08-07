{{-- Card partita (traduzione di partita() del vecchio tema) + popup ui_game.
     Variabili: $p (rigaPartita), $pid (id univoco del popup), $tournamentId --}}
@php
    $homeCls = $p['winner'] === $p['home']['name'] ? 'bold' : ($p['winner'] === $p['away']['name'] ? 'lose' : '');
    $awayCls = $p['winner'] === $p['away']['name'] ? 'bold' : ($p['winner'] === $p['home']['name'] ? 'lose' : '');
@endphp

<div class="matches" data-popup="{{ $pid }}">
    <div class="gara-bracket">
        <div class="home">
            @if ($p['home']['flag'])
                <img class="flag effetto_luce" src="{{ $p['home']['flag'] }}" alt="{{ $p['home']['code'] }}"
                     onerror="this.style.display='none'">
            @endif
            <div class="team puntini {{ $homeCls }}"><span>{{ $p['home']['name'] }}</span></div>
        </div>
        <div class="away">
            @if ($p['away']['flag'])
                <img class="flag effetto_luce" src="{{ $p['away']['flag'] }}" alt="{{ $p['away']['code'] }}"
                     onerror="this.style.display='none'">
            @endif
            <div class="team puntini {{ $awayCls }}"><span>{{ $p['away']['name'] }}</span></div>
        </div>
    </div>
    <div class="risult">
        <div class="ris"><span>{{ $p['ris'] }}</span></div>
        @if ($p['e_replay'])
            <div class="ris-2"><span>replay</span></div>
        @elseif ($p['replay'])
            <div class="ris-2"><span>{{ $p['replay']['score'] }} d.R.</span></div>
        @elseif ($p['dcr'])
            <div class="ris-2"><span>d.c.r.</span></div>
        @elseif ($p['dts'])
            <div class="ris-2"><span>d.t.s.</span></div>
        @endif
    </div>
</div>

@include('torneo.partials._popup_partita', ['p' => $p, 'pid' => $pid])
