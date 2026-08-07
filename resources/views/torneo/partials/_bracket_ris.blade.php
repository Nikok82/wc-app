{{-- Box risultato del bracket (traduzione di bracket_risultato()):
     punteggio + eventuale (dts) / rigori dcr / replay d.R.; click -> popup.
     Variabili: $p (rigaPartita), $pid --}}
<span class="br-box" data-popup="{{ $pid }}">
    @if ($p['replay'])
        <span class="r1">{{ $p['ris_gol'] }}</span>
        <span class="r2">{{ $p['replay']['score'] }}</span>
        <span class="r3">d.R.</span>
    @elseif ($p['dcr'])
        <span class="r1">{{ $p['ris_gol'] }}</span>
        <span class="r2">{{ $p['ris_rigori'] }}</span>
        <span class="r3">d.c.r.</span>
    @elseif ($p['dts'])
        <span class="r1">{{ $p['ris_gol'] }}</span>
        <span class="r3">d.t.s.</span>
    @else
        <span class="r1">{{ $p['ris_gol'] }}</span>
    @endif
</span>

@include('torneo.partials._popup_partita', ['p' => $p, 'pid' => $pid])
