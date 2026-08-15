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

    // D2 (15/08): dal riquadro del tabellino le squadre portano alla scheda
    // SQUADRA-ANNO, non a quella generale: si arriva dal contesto di una
    // singola edizione, ed e' li' che il visitatore si aspetta di finire.
    // Se l'anno non e' ricavabile dalla data si ripiega sulla scheda squadra.
    $urlSquadra = function ($code) use ($anno) {
        if (! $code) {
            return null;
        }

        return ($anno && preg_match('/^[A-Za-z]{3}$/', $code))
            ? route('squadra_anno.show', ['code' => strtoupper($code), 'year' => $anno])
            : route('squadra.show', $code);
    };
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
                    @if ($urlSquadra($p['home']['code']))
                        <a href="{{ $urlSquadra($p['home']['code']) }}" title="{{ $p['home']['name'] }}"><img class="flag effetto_luce" src="{{ $p['home']['flag'] }}" alt="{{ $p['home']['name'] }}" onerror="this.style.display='none'"></a>
                    @else
                        <img class="flag effetto_luce" src="{{ $p['home']['flag'] }}" alt=""
                             onerror="this.style.display='none'">
                    @endif
                @endif
                <span>@if ($urlSquadra($p['home']['code']))<a href="{{ $urlSquadra($p['home']['code']) }}">{{ $p['home']['name'] }}</a>@else{{ $p['home']['name'] ?: 'da definire' }}@endif</span>
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
                <span>@if ($urlSquadra($p['away']['code']))<a href="{{ $urlSquadra($p['away']['code']) }}">{{ $p['away']['name'] }}</a>@else{{ $p['away']['name'] ?: 'da definire' }}@endif</span>
                @if ($p['away']['flag'])
                    @if ($urlSquadra($p['away']['code']))
                        <a href="{{ $urlSquadra($p['away']['code']) }}" title="{{ $p['away']['name'] }}"><img class="flag effetto_luce" src="{{ $p['away']['flag'] }}" alt="{{ $p['away']['name'] }}" onerror="this.style.display='none'"></a>
                    @else
                        <img class="flag effetto_luce" src="{{ $p['away']['flag'] }}" alt=""
                             onerror="this.style.display='none'">
                    @endif
                @endif
            </div>
        </div>
        @php $vociGol = \App\Support\Marcatori::aggrega($p['marcatori_match'] ?? []); @endphp
        @if (!empty($vociGol))
            <div class="marcatori-popup">
                @foreach ($vociGol as $g)
                    <span class="marcatore">
                        @if ($g['flag'])
                            <img class="flag" src="{{ $g['flag'] }}" alt="" onerror="this.style.display='none'">
                        @endif
                        {{ \App\Support\Marcatori::minuti($g) }}
                        <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>
                        @if ($g['autogol'])<em class="autogol">(autogol)</em>@endif
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
