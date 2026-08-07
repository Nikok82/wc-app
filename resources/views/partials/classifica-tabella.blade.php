{{-- Tabella di classifica condivisa da tab "Classifica" del torneo (sub-tab
     Torneo e Perpetua) e dalla /classifica globale. Frammento senza <script>:
     ordinamento colonne e switch pt3/pt2 sono in wc.js (delega su
     .cls-table th[data-sort] e .cls-pt). Nessuna paginazione: tutte le
     squadre in un'unica lista.
     Variabili: $righe (ClassificaService), $mode ('torneo'|'perpetua'). --}}

<div class="cls-scroll">
    <table class="cls-table" data-mode="{{ $mode }}">
        <thead>
            <tr>
                <th class="c-num {{ $mode === 'torneo' ? 'asc' : '' }}" data-sort="pos"
                    title="{{ $mode === 'torneo' ? 'Posizione (fa fede class_mond)' : 'Posizione in classifica perpetua' }}">#</th>
                <th data-sort="squadra" title="Ordina per nome squadra">Squadra</th>
                <th class="c-num" data-sort="pg" title="Partite giocate">PG</th>
                <th class="c-num" data-sort="v"  title="Vittorie">V</th>
                <th class="c-num" data-sort="n"  title="Pareggi">N</th>
                <th class="c-num" data-sort="p"  title="Sconfitte">P</th>
                <th class="c-num" data-sort="gf" title="Gol fatti">GF</th>
                <th class="c-num" data-sort="gs" title="Gol subiti">GS</th>
                <th class="c-num" data-sort="dr" title="Differenza reti">DR</th>
                <th class="c-num {{ $mode === 'perpetua' ? 'desc' : '' }}" data-sort="pt3"
                    title="Punti con la vittoria da 3">Pt3</th>
                <th class="c-num" data-sort="pt2" title="Punti con la vittoria da 2">Pt2</th>
                <th class="c-note">Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($righe as $r)
                <tr data-pos="{{ $r['pos'] ?? '' }}"
                    data-squadra="{{ mb_strtolower(str_replace('*', '', $r['team_name'] ?? '')) }}"
                    data-pg="{{ $r['pg'] }}" data-v="{{ $r['v'] }}" data-n="{{ $r['n'] }}"
                    data-p="{{ $r['p'] }}" data-gf="{{ $r['gf'] }}" data-gs="{{ $r['gs'] }}"
                    data-dr="{{ $r['dr'] }}" data-pt3="{{ $r['pt3'] }}" data-pt2="{{ $r['pt2'] }}">
                    <td class="c-num c-pos">{{ $r['pos'] ?? '—' }}</td>
                    <td class="c-squadra">
                        @if ($r['flag'])
                            <img class="flag" src="{{ $r['flag'] }}" alt="" loading="lazy"
                                 onerror="this.style.display='none'">
                        @endif
                        @if ($r['url'])
                            <a href="{{ $r['url'] }}">{{ $r['team_name'] }}</a>
                        @else
                            {{ $r['team_name'] }}
                        @endif
                    </td>
                    <td class="c-num">{{ $r['pg'] }}</td>
                    <td class="c-num">{{ $r['v'] }}</td>
                    <td class="c-num">{{ $r['n'] }}</td>
                    <td class="c-num">{{ $r['p'] }}</td>
                    <td class="c-num">{{ $r['gf'] }}</td>
                    <td class="c-num">{{ $r['gs'] }}</td>
                    <td class="c-num c-dr">{{ $r['dr'] > 0 ? '+'.$r['dr'] : $r['dr'] }}</td>
                    <td class="c-num c-pt3">{{ $r['pt3'] }}</td>
                    <td class="c-num c-pt2">{{ $r['pt2'] }}</td>
                    <td class="c-note">
                        @if ($mode === 'perpetua' && $r['medaglie'])
                            @php [$ori, $argenti, $bronzi] = $r['medaglie']; @endphp
                            @if ($ori)
                                <span class="medaglia oro" title="{{ $ori }}× campione del Mondo">{{ $ori }}</span>
                            @endif
                            @if ($argenti)
                                <span class="medaglia argento" title="{{ $argenti }}× secondo posto">{{ $argenti }}</span>
                            @endif
                            @if ($bronzi)
                                <span class="medaglia bronzo" title="{{ $bronzi }}× terzo posto">{{ $bronzi }}</span>
                            @endif
                        @elseif ($mode === 'torneo')
                            {{ $r['nota'] }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
