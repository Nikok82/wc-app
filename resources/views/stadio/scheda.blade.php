{{-- Scheda stadio (frammento, riusata da: pagina /stadio/{id}, popup
     dell'elenco /stadi e popup della tab Stadi del torneo). Niente <script>:
     la mappa Leaflet viene inizializzata da wc.js (WC.initMappeStadio su
     .mappa-stadio[data-lat], chiamata dopo il load di pagina/popup). --}}

@include('partials.scheda-css')

<div class="scheda">
    <div class="scheda-head">
        <h2>{{ $nome }}</h2>
        @if ($flag)
            <div class="bandiere">
                <img src="{{ $flag }}" alt="" onerror="this.style.display='none'">
            </div>
        @endif
    </div>

    <div class="riga">
        <span class="lbl">Città</span>
        <span class="val">
            @if ($s->city_wikipedia_link)
                <a href="{{ $s->city_wikipedia_link }}" target="_blank" rel="noopener">{{ $s->city_name }}</a>
            @else
                {{ $s->city_name }}
            @endif
        </span>
    </div>

    <div class="riga">
        <span class="lbl">Paese</span>
        <span class="val">{{ $s->country_name }}</span>
    </div>

    @if ($s->stadium_capacity)
        <div class="riga">
            <span class="lbl">Capienza</span>
            <span class="val">{{ number_format($s->stadium_capacity, 0, ',', '.') }}</span>
        </div>
    @endif

    @if ($s->stadium_wikipedia_link)
        <div class="riga">
            <span class="lbl">Wikipedia</span>
            <span class="val"><a href="{{ $s->stadium_wikipedia_link }}" target="_blank" rel="noopener">Scheda dello stadio</a></span>
        </div>
    @endif

    @if ($tornei->isNotEmpty())
        <div class="riga">
            <span class="lbl">Mondiali ospitati</span>
            <span class="val">
                @foreach ($tornei as $t)
                    <a href="{{ route('torneo.show', $t['tid']) }}"
                       title="{{ $t['n'] }} {{ $t['n'] === 1 ? 'partita' : 'partite' }}">{{ $t['anno'] }}</a>@if(! $loop->last), @endif
                @endforeach
            </span>
        </div>
    @endif

    @if ($s->lat !== null && $s->lng !== null)
        <div class="riga">
            <span class="lbl">Posizione</span>
            <span class="val val-mappa">
                <div class="mappa-stadio" data-lat="{{ $s->lat }}" data-lng="{{ $s->lng }}"
                     data-nome="{{ $nome }} — {{ $s->city_name }}"></div>
            </span>
        </div>
    @endif

    <div class="riga">
        <span class="lbl">Partite giocate</span>
        <span class="val">
            @if ($partite->isEmpty())
                Nessuna partita trovata.
            @else
                <table class="gare">
                    <thead>
                        <tr>
                            <th>Mondiale</th>
                            <th>Data</th>
                            <th>Fase</th>
                            <th>Partita</th>
                            <th>Risultato</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partite as $p)
                            <tr>
                                <td class="data-cell"><a href="{{ route('torneo.show', $p['tid']) }}">{{ $p['anno'] }}</a></td>
                                <td class="data-cell">{{ $p['data'] }}</td>
                                <td>{{ $p['stage'] }}</td>
                                <td>@include('partials.match-cell', ['match' => $p['match'], 'matchId' => $p['match_id']])</td>
                                <td class="data-cell">{{ $p['score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </span>
    </div>
</div>

<style>
    .scheda .val-mappa{display:block;width:100%;}
    .mappa-stadio{width:100%;height:260px;border-radius:10px;overflow:hidden;
        border:1px solid var(--line,#e2e8e5);background:#eef2ef;}
</style>
