{{-- Card partita del torneo, impaginazione "A".
     Sopra la riga di bandiere e nomi stanno data, stadio e citta';
     sotto, al posto della data, l'elenco dei marcatori.
     Variabili: $p (rigaPartita), $pid (id univoco del popup), $ctx.
     $barra: true nella fase a eliminazione, dove la perdente va barrata;
     nei gironi resta false, perche' li' una sconfitta non elimina. --}}
@php
    $barra    = $barra ?? false;
    $homeCls  = $p['winner'] === $p['home']['name']
        ? 'bold'
        : (($barra && $p['winner'] === $p['away']['name']) ? 'lose' : '');
    $awayCls  = $p['winner'] === $p['away']['name']
        ? 'bold'
        : (($barra && $p['winner'] === $p['home']['name']) ? 'lose' : '');

    $quando = collect([
        $p['date'] ? \Carbon\Carbon::parse($p['date'])->format('d/m/Y') : null,
        $p['stadium'] ?: null,
        $p['city'] ?: null,
    ])->filter()->implode(' · ');
@endphp

<div class="matches" data-popup="{{ $pid }}">
    @if ($quando)
        <div class="mt-quando">{{ $quando }}</div>
    @endif

    <div class="mt-corpo">
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

    @if (!empty($p['marcatori_match']))
        <div class="mt-gol">
            @foreach ($p['marcatori_match'] as $g)
                <span class="mt-gol-voce">
                    <span class="mt-gol-min">{{ $g['minuto'] }}</span>
                    @if ($g['flag'])
                        <img class="mt-gol-fl" src="{{ $g['flag'] }}" alt="{{ $g['team_code'] }}"
                             onerror="this.style.display='none'">
                    @endif
                    <span class="mt-gol-nome">{{ $g['nome'] }}</span>
                    @if ($g['own_goal'])
                        <span class="mt-gol-nota">(aut.)</span>
                    @elseif ($g['penalty'])
                        <span class="mt-gol-nota">(rig.)</span>
                    @endif
                </span>
            @endforeach
        </div>
    @endif
</div>

@include('torneo.partials._popup_partita', ['p' => $p, 'pid' => $pid])
