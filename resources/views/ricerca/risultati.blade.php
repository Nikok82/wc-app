{{-- Risultati della ricerca globale (A3, 15/08).

     Frammento caricato via fetch dentro #ricerca-risultati. Niente <script>
     qui dentro: non verrebbe eseguito. Le frecce di impaginazione portano
     data-tipo/data-pagina e le raccoglie la delega di wc.js.

     Dieci risultati per tipo; oltre i dieci si impagina, sempre a dieci.
     I tipi escono dal piu' specifico al piu' generico. --}}

@if ($avviso)
    <p class="ric-avviso">{{ $avviso }}</p>
@elseif ($q === '')
    <p class="ric-avviso">Cerca una nazionale, un torneo, una partita, una persona, uno stadio o un club.</p>
@elseif (empty($gruppi))
    <p class="ric-avviso">Nessun risultato per “{{ $q }}”.</p>
@else
    @foreach ($gruppi as $chiave => $g)
        <div class="ric-gruppo">
            <div class="ric-titolo">
                <span>{{ $g['titolo'] }}</span>
                <span class="ric-conta">{{ $g['totale'] }}</span>
            </div>

            @foreach ($g['voci'] as $v)
                <a class="ric-voce" href="{{ $v['url'] }}">
                    @if (!empty($v['img']))
                        <img src="{{ $v['img'] }}" alt="" onerror="this.style.visibility='hidden'">
                    @else
                        <span class="ric-vuota"></span>
                    @endif
                    <span class="ric-testi">
                        <span class="ric-nome">{{ $v['titolo'] }}</span>
                        @if (!empty($v['sottotitolo']))
                            <span class="ric-sotto">{{ $v['sottotitolo'] }}</span>
                        @endif
                    </span>
                </a>
            @endforeach

            @if ($g['pagine'] > 1)
                <div class="ric-pagine">
                    <button type="button" class="ric-pg" data-tipo="{{ $chiave }}"
                            data-pagina="{{ $g['pagina'] - 1 }}"
                            @disabled($g['pagina'] <= 1)>‹</button>
                    <span>{{ $g['pagina'] }} / {{ $g['pagine'] }}</span>
                    <button type="button" class="ric-pg" data-tipo="{{ $chiave }}"
                            data-pagina="{{ $g['pagina'] + 1 }}"
                            @disabled($g['pagina'] >= $g['pagine'])>›</button>
                </div>
            @endif
        </div>
    @endforeach
@endif

{{-- Il piede resta sempre: la ricerca per numero di maglia non e' intuitiva
     e nessuno la scoprirebbe da solo. --}}
<div class="ric-piede">
    <small>
        Si può cercare anche per numero di maglia: scrivi <b>#10</b> insieme a una
        nazione o a un anno (per esempio <b>#10 Argentina 1986</b>). Funziona
        solo <b>dal 1954 in poi</b>: per le edizioni precedenti i numeri non
        esistono nei dati dei Mondiali.
    </small>
</div>
