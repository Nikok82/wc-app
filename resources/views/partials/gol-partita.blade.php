{{-- Elenco marcatori sotto il punteggio di una partita.
     Attende $gol: elenco di ['minuto','flag','nome','nota','team_code'].

     La bandiera e' quella della squadra a cui la rete e' accreditata, non
     quella di chi la segna: cosi' un autogol compare sotto la squadra che
     ne ha beneficiato, e l'annotazione "aut." chiarisce l'equivoco. --}}
@if (!empty($gol))
    <div class="gol-riga">
        @foreach ($gol as $g)
            <span class="gol-voce">
                <span class="gol-min">{{ $g['minuto'] }}</span>
                @if ($g['flag'])
                    <img class="gol-fl" src="{{ $g['flag'] }}" alt="{{ $g['team_code'] }}"
                         onerror="this.style.display='none'">
                @endif
                <span class="gol-nome">{{ $g['nome'] }}</span>
                @if ($g['nota'])
                    <span class="gol-nota">({{ $g['nota'] }})</span>
                @endif
            </span>
        @endforeach
    </div>
@endif
