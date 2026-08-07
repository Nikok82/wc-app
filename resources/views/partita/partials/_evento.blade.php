{{-- Cella evento della tab Eventi. $r: tipo gol|giallo|rosso|sub + dati. --}}
<span class="ev-item">
    @if ($r['tipo'] === 'gol')
        <span class="ev-ball">{!! \App\Support\Icons::svg('ball') !!}</span>
        <a href="{{ route('giocatore.show', $r['player']['id']) }}">{{ $r['player']['nome'] }}</a>
        @if ($r['rigore'])<em>(rig.)</em>@endif
        @if ($r['autogol'])<em>(aut.)</em>@endif
    @elseif ($r['tipo'] === 'giallo')
        <span class="ev-card giallo"></span>
        <a href="{{ route('giocatore.show', $r['player']['id']) }}">{{ $r['player']['nome'] }}</a>
    @elseif ($r['tipo'] === 'rosso')
        <span class="ev-card rosso {{ !empty($r['doppia']) ? 'ev-doppia' : '' }}"></span>
        <a href="{{ route('giocatore.show', $r['player']['id']) }}">{{ $r['player']['nome'] }}</a>
        @if (!empty($r['doppia']))<em>(2ª amm.)</em>@endif
    @elseif ($r['tipo'] === 'sub')
        @if ($r['out'])
            <a href="{{ route('giocatore.show', $r['out']['id']) }}">{{ $r['out']['nome'] }}</a>
            <span class="ev-arrow out" title="Fuori"></span>
        @endif
        @if ($r['out'] && $r['in'])<span style="color:var(--muted)">–</span>@endif
        @if ($r['in'])
            <a href="{{ route('giocatore.show', $r['in']['id']) }}">{{ $r['in']['nome'] }}</a>
            <span class="ev-arrow in" title="Dentro"></span>
        @endif
    @endif
</span>
