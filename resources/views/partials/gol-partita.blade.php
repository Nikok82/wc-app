{{-- Elenco marcatori sotto il punteggio di una partita.
     Attende $gol: elenco di ['player_id','minuto','flag','nome','nota','team_code'].

     La bandiera e' quella della squadra a cui la rete e' accreditata, non
     quella di chi la segna: cosi' un autogol compare sotto la squadra che
     ne ha beneficiato, e l'annotazione "aut." chiarisce l'equivoco.

     B1 (15/08): il nome porta alla scheda del giocatore. L'ancora del
     punteggio si chiude PRIMA di questa include in tutte e tre le tab che
     la usano, quindi non si creano ancore annidate. --}}
@if (!empty($gol))
    <div class="gol-riga">
        @foreach ($gol as $g)
            <span class="gol-voce">
                <span class="gol-min">{{ $g['minuto'] }}</span>
                @if ($g['flag'])
                    <img class="gol-fl" src="{{ $g['flag'] }}" alt="{{ $g['team_code'] }}"
                         onerror="this.style.display='none'">
                @endif
                @if (!empty($g['player_id']))
                    <a class="gol-nome" href="{{ route('giocatore.show', $g['player_id']) }}"
                       title="Scheda giocatore">{{ $g['nome'] }}</a>
                @else
                    <span class="gol-nome">{{ $g['nome'] }}</span>
                @endif
                @if ($g['nota'])
                    <span class="gol-nota">({{ $g['nota'] }})</span>
                @endif
            </span>
        @endforeach
    </div>
@endif
