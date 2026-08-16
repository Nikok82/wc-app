@extends('layouts.app')

@section('title', $nome)

@section('content')
    <div class="club-head">
        @include('partials.stemma-club', ['logo' => $logo, 'lato' => 60, 'alt' => $nome])
        <div>
            <h1>{{ $nome }}</h1>
            @if ($epoca)
                {{-- Come il club era chiamato all'epoca delle convocazioni.
                     Ricavato dalle rose, o scritto a mano in
                     resources/data/club-nomi-epoca.php per i casi che il
                     database non puo' sapere (le fusioni fra societa'). --}}
                <small class="club-epoca">{{ $epoca }}</small>
            @endif
            <div class="club-stato">
                @if ($flag)
                    <img src="{{ $flag }}" alt="{{ $club->stato }}" onerror="this.style.display='none'">
                @endif
                <span>{{ $club->stato ?: '—' }}</span>
            </div>
        </div>
    </div>

    <div id="tab-content">
        @if ($mondiali->isEmpty())
            <p>Nessun giocatore di questo club risulta convocato a un Mondiale.</p>
        @else
            <p class="club-somma">
                {{ $totale }} {{ $totale === 1 ? 'giocatore convocato' : 'giocatori convocati' }}
                mentre militava{{ $totale === 1 ? '' : 'no' }} in questo club.
            </p>

            {{-- Un blocco per edizione, coi giocatori nella stessa
                 impaginazione della sezione Giocatori: bandiera della
                 nazionale, nome, e a destra il numero di maglia. --}}
            @foreach ($mondiali as $m)
                <div class="club-mond">
                    <div class="titolo">
                        <a href="{{ route('torneo.show', $m['tid']) }}">{{ $m['nome'] }}</a>
                        <span class="n">{{ $m['righe']->count() }}
                            {{ $m['righe']->count() === 1 ? 'convocato' : 'convocati' }}</span>
                    </div>
                    <div class="elenco">
                        @foreach ($m['righe'] as $g)
                            <a class="voce" href="{{ $g['player_id'] ? route('giocatore.show', $g['player_id']) : '#' }}">
                                <span class="nome">
                                    @if ($g['flag'])
                                        <img class="flag-riga" src="{{ $g['flag'] }}" alt="{{ $g['code'] }}"
                                             onerror="this.style.display='none'">
                                    @else
                                        <span class="flag-riga vuota"></span>
                                    @endif
                                    {{ $g['nome'] }}
                                </span>
                                <span class="extra">
                                    {{ $g['squadra'] }}
                                    @if ($g['maglia'])
                                        <span class="maglia">#{{ $g['maglia'] }}</span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="footer-nav">
        <a href="{{ route('club.index') }}">‹ Tutti i club</a>
        @if ($club->wikipedia_page)
            <a href="{{ $club->wikipedia_page }}" target="_blank" rel="noopener">Wikipedia ›</a>
        @endif
    </div>

    @include('partials.club-css')

    <style>
        .club-somma { color:var(--muted); font-size:13px; margin:0 0 14px; }
    </style>
@endsection
