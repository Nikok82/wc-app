@include('partials.scheda-css')

<div class="scheda">
    <div class="scheda-head">
        <h2>{{ $nome }}</h2>
        @if (count($bandiere))
            <div class="bandiere">
                @foreach ($bandiere as $b)
                    <img src="{{ $b }}" alt="" onerror="this.style.display='none'">
                @endforeach
            </div>
        @endif
    </div>

    <div class="riga">
        <span class="lbl">Data di nascita</span>
        <span class="val">
            {{ $nascita ?: '—' }}
            @if ($anni !== null) ({{ $anni }}) @endif
        </span>
    </div>

    <div class="riga">
        <span class="lbl">Ruolo</span>
        <span class="val">{{ $ruolo ?: '—' }}</span>
    </div>

    <div class="riga">
        <span class="lbl">Tornei giocati</span>
        <span class="val tornei-lista">
            {{ $tornei['count'] }}
            @if (count($tornei['anni']))
                (@foreach ($tornei['anni'] as $t)<a href="{{ route('torneo.show', $t['tid']) }}">{{ $t['anno'] }}</a>@if(!$loop->last), @endif @endforeach)
            @endif
        </span>
    </div>

    <div class="riga riga-gare">
        <span class="lbl">Gare giocate</span>
        <span class="val">
            @if ($gare->isEmpty())
                Nessuna partita trovata.
            @else
                <table class="gare gare-g">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Fase</th>
                            <th>Partita</th>
                            <th>Maglia</th>
                            <th>Minutaggio</th>
                            <th>Gol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $annoSep = null; $nGara = 0; @endphp
                        @foreach ($gare as $gara)
                            @if (!empty($gara['anno']) && $gara['anno'] !== $annoSep)
                                @php $annoSep = $gara['anno']; $nGara = 0; @endphp
                                <tr class="anno-sep"><td colspan="6">{{ $annoSep }}</td></tr>
                            @endif
                            <tr class="gara{{ $nGara++ % 2 === 1 ? ' alt' : '' }}">
                                <td class="data-cell">{{ $gara['data'] }}</td>
                                <td>{{ $gara['stage'] }}</td>
                                <td>@include('partials.match-cell', ['match' => $gara['match'], 'matchId' => $gara['match_id']])</td>
                                <td>{{ $gara['maglia'] }}</td>
                                <td>{{ $gara['minutaggio'] }}</td>
                                <td>{{ $gara['gol'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </span>
    </div>

    @if ($wikipedia)
        <div class="riga">
            <span class="lbl">Wikipedia</span>
            <span class="val"><a href="{{ $wikipedia }}" target="_blank" rel="noopener">{{ $wikipedia }}</a></span>
        </div>
    @endif
</div>
