{{-- Contenuto del box "Scopri un torneo" (home). Frammento senza <script>:
     reso al primo caricamento e ri-servito via fetch dalla rotta
     home.box.torneo ("Mostra un altro torneo").
     Variabile: $box (HomeController::torneoCasuale). --}}

<a class="scopri-link" href="{{ $box['url'] }}" title="Vai al Mondiale {{ $box['anno'] }}">
    <div class="scopri-torneo-testa">
        <div class="scopri-torneo-host">
            @if ($box['flag_host'])
                <img class="flag scopri-flag-host" src="{{ $box['flag_host'] }}"
                     alt="Bandiera {{ $box['host'] }}" onerror="this.style.display='none'">
            @endif
            <div class="scopri-nome">{{ $box['host'] }}<br>{{ $box['anno'] }}</div>
        </div>
        <img class="scopri-manifesto" src="{{ $box['manifesto'] }}"
             alt="Manifesto {{ $box['anno'] }}" loading="lazy"
             onerror="this.style.display='none'">
    </div>

    @if ($box['podio'])
        <div class="scopri-podio">
            @foreach ([2 => 'secondo', 1 => 'primo', 3 => 'terzo'] as $pos => $classe)
                @if (isset($box['podio'][$pos]))
                    @php $p = $box['podio'][$pos]; @endphp
                    <div class="posto {{ $classe }}">
                        <div class="team">
                            @if ($p['flag'])
                                <img class="flag" src="{{ $p['flag'] }}" alt="{{ $p['team_name'] }}"
                                     title="{{ $p['team_name'] }}" onerror="this.style.display='none'">
                            @endif
                        </div>
                        <div class="bar">{{ $pos }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</a>
