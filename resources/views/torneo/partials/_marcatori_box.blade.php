{{-- Riga espandibile "Gol / Marcatore" (traduzione di marcatori() del vecchio
     tema), autogol inclusi in riga dedicata.
     Variabili: $marc = ['gruppi' => [gol => [giocatori]], 'autogol' => ?] --}}
@if (!empty($marc['gruppi']) || !empty($marc['autogol']))
    <details class="marc-box">
        <summary>
            <span class="arrow">▼</span>
            <span class="h-gol">Gol</span>
            <span class="h-marc">Marcatore</span>
        </summary>
        <div class="marc-grid">
            @foreach ($marc['gruppi'] as $gol => $giocatori)
                <div class="grid-cell gol">{{ $gol }}</div>
                <div class="grid-cell marcatori">
                    @foreach ($giocatori as $g)
                        <span class="marcatore">
                            @if ($g['flag'])
                                <img class="flag effetto_luce" src="{{ $g['flag'] }}" alt="{{ $g['team_code'] }}"
                                     onerror="this.style.display='none'">
                            @endif
                            <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>
                        </span>
                    @endforeach
                </div>
            @endforeach

            @if (!empty($marc['autogol']))
                <div class="grid-cell gol"><span class="autogol">Autogol:</span> {{ $marc['autogol']['tot'] }}</div>
                <div class="grid-cell marcatori">
                    @foreach ($marc['autogol']['autori'] as $g)
                        <span class="marcatore">
                            @if ($g['flag'])
                                <img class="flag effetto_luce" src="{{ $g['flag'] }}" alt="{{ $g['team_code'] }}"
                                     onerror="this.style.display='none'">
                            @endif
                            <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>
                            <em class="autogol">(autogol)</em>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </details>
@endif
