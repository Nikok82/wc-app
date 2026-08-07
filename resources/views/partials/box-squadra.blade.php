{{-- Contenuto del box "Scopri una nazionale" (home). Frammento senza <script>:
     viene reso al primo caricamento della home e ri-servito via fetch dalla
     rotta home.box.squadra quando si preme "Mostra un'altra squadra".
     Variabile: $box (HomeController::squadraCasuale). --}}

<a class="scopri-link" href="{{ $box['url'] }}" title="Vai alla scheda di {{ $box['team_name'] }}">
    <div class="scopri-nome">{{ $box['team_name'] }}</div>

    @if ($box['flag'])
        <img class="flag scopri-flag" src="{{ $box['flag'] }}" alt="Bandiera {{ $box['team_name'] }}"
             onerror="this.style.display='none'">
    @endif

    <table class="scopri-stat">
        <thead>
            <tr>
                @foreach ($box['stats'] as $sigla => $val)
                    <th class="st-{{ strtolower($sigla) }}"
                        title="{{ ['WC' => 'Partecipazioni ai Mondiali', 'PG' => 'Partite giocate', 'V' => 'Vittorie', 'N' => 'Pareggi', 'P' => 'Sconfitte', 'GF' => 'Gol fatti', 'GS' => 'Gol subiti'][$sigla] }}">{{ $sigla }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach ($box['stats'] as $sigla => $val)
                    <td class="st-{{ strtolower($sigla) }}">{{ $val }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

    @php [$ori, $argenti, $bronzi] = $box['medaglie']; @endphp
    @if ($ori || $argenti || $bronzi)
        <div class="scopri-medaglie">
            @if ($ori)
                <span class="box-med oro" title="{{ $ori }}× campione del Mondo">{{ $ori }}</span>
            @endif
            @if ($argenti)
                <span class="box-med argento" title="{{ $argenti }}× secondo posto">{{ $argenti }}</span>
            @endif
            @if ($bronzi)
                <span class="box-med bronzo" title="{{ $bronzi }}× terzo posto">{{ $bronzi }}</span>
            @endif
        </div>
    @endif
</a>
