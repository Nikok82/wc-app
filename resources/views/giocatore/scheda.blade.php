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

    @if (!empty($club))
        <div class="riga">
            <span class="lbl">Club</span>
            <span class="val club-lista">
                @foreach ($club as $c)
                    <span class="club-voce">
                        @if ($c['logo'])
                            <img src="{{ $c['logo'] }}" alt="" width="18" height="18"
                                 loading="lazy" onerror="this.style.display='none'">
                        @endif
                        <span class="club-nome">{{ $c['nome'] }}</span>
                        <span class="club-anno">{{ $c['anno'] }}</span>
                    </span>
                @endforeach
            </span>
        </div>
        <style>
            /* I nomi lunghi non devono allargare la riga oltre il box:
               ogni voce e' un blocco a se' che va a capo per intero. */
            .club-lista{display:flex;flex-wrap:wrap;gap:6px 14px;min-width:0;}
            .club-voce{display:inline-flex;align-items:center;gap:6px;
                white-space:nowrap;max-width:100%;}
            .club-voce img{flex:none;border-radius:2px;}
            .club-nome{overflow:hidden;text-overflow:ellipsis;}
            .club-anno{color:var(--muted);font-size:12px;}
        </style>
    @endif

    <div class="riga riga-partite">
        <span class="lbl">Gare giocate</span>
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

    @if ($wikipedia)
        <div class="riga">
            <span class="lbl">Wikipedia</span>
            <span class="val"><a href="{{ $wikipedia }}" target="_blank" rel="noopener">{{ $wikipedia }}</a></span>
        </div>
    @endif
</div>
