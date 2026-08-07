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
        <span class="lbl">Ruolo</span>
        <span class="val">Arbitro</span>
    </div>

    <div class="riga">
        <span class="lbl">Tornei</span>
        <span class="val">
            @if ($tornei->isEmpty())
                Nessun torneo trovato.
            @else
                <table class="gare">
                    <tbody>
                        @foreach ($tornei as $t)
                            <tr>
                                <td class="data-cell"><a href="{{ route('torneo.show', $t['tid']) }}">{{ $t['anno'] }}</a></td>
                                <td>Confederazione: {{ $t['confederazione'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </span>
    </div>

    <div class="riga">
        <span class="lbl">Gare arbitrate</span>
        <span class="val">
            @if ($gare->isEmpty())
                Nessuna partita trovata.
            @else
                <table class="gare">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Fase</th>
                            <th>Partita</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gare as $gara)
                            <tr>
                                <td class="data-cell">{{ $gara['data'] }}</td>
                                <td>{{ $gara['stage'] }}</td>
                                <td>@include('partials.match-cell', ['match' => $gara['match'], 'matchId' => $gara['match_id']])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </span>
    </div>
</div>
