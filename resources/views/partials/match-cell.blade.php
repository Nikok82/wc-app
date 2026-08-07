{{-- Cella partita: (bandiera) Squadra1 - Squadra2 (bandiera) + icona-link al match --}}
<span class="match-cell">
    @if ($match['casa']['flag'])
        <img class="mflag" src="{{ $match['casa']['flag'] }}" alt="" onerror="this.style.display='none'">
    @endif
    <span class="{{ $match['casa']['grassetto'] ? 'evidenzia' : '' }}">{{ $match['casa']['nome'] }}</span>
    <span class="msep">-</span>
    <span class="{{ $match['trasferta']['grassetto'] ? 'evidenzia' : '' }}">{{ $match['trasferta']['nome'] }}</span>
    @if ($match['trasferta']['flag'])
        <img class="mflag" src="{{ $match['trasferta']['flag'] }}" alt="" onerror="this.style.display='none'">
    @endif
    @if (!empty($matchId))
        <a class="match-link" href="{{ route('partita.show', $matchId) }}" title="Vai alla scheda della partita">
            <img src="{{ route('img', ['tipo' => 'icons', 'file' => 'scoreboard.svg']) }}" alt="Partita">
        </a>
    @endif
</span>
