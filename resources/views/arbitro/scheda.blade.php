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

    <div class="riga riga-partite">
        <span class="lbl">Gare arbitrate</span>
        <span class="val">
            @if (empty($gruppi))
                Nessuna partita trovata.
            @else
                {{-- C1 (15/08): stessa impaginazione della tab Partite della
                     scheda squadra (scheda a due lati, bandiere incolonnate
                     ai lati, marcatori sotto), raggruppata per edizione. --}}
                @include('squadra.partials.partite', ['gruppi' => $gruppi, 'gol' => $gol])
            @endif
        </span>
    </div>
</div>
