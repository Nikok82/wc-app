{{-- Pallino giocatore della scheda partita (campo + elenco formazioni).
     $p: player_id, nome, numero, flag, gol, card, entrato, uscito, panchina.
     Icone: numero in alto a sx; pallone (+conteggio) e cartellino in basso
     a dx (pallone davanti al cartellino); frecce cambio in alto a dx
     (rossa a destra = uscito, verde a sinistra = entrato). --}}
<a class="plr{{ !empty($p['panchina']) ? ' plr-panchina' : '' }}"
   href="{{ route('giocatore.show', $p['player_id']) }}" title="{{ $p['nome'] }}">
    <span class="plr-cerchio"
          @if ($p['flag']) style="background-image:url('{{ $p['flag'] }}')" @endif></span>

    @if ($p['numero'])
        <span class="plr-num">{{ $p['numero'] }}</span>
    @endif

    @if ($p['gol'] > 0 || $p['card'])
        <span class="plr-basso">
            @if ($p['gol'] > 0)
                <span class="plr-ball">{!! \App\Support\Icons::svg('ball') !!}</span>@if ($p['gol'] > 1)<b class="plr-ngol">{{ $p['gol'] }}</b>@endif
            @endif
            @if ($p['card'])
                <span class="plr-card {{ $p['card'] }}"></span>
            @endif
        </span>
    @endif

    @if (!empty($p['uscito']) || !empty($p['entrato']))
        <span class="plr-alto">
            @if (!empty($p['entrato']))<span class="plr-arrow in" title="Entrato al {{ $p['entrato'] }}"></span>@endif
            @if (!empty($p['uscito']))<span class="plr-arrow out" title="Uscito al {{ $p['uscito'] }}"></span>@endif
        </span>
    @endif
</a>
