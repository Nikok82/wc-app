@extends('layouts.app')

@section('title', 'Club')

@section('content')
    <div class="team-head">
        <h1>Club</h1>
    </div>

    {{-- Tendina delle nazioni + ricerca per nome. Il form e' in GET: la
         pagina risultante e' un indirizzo condivisibile e la paginazione
         si porta dietro il filtro (withQueryString nel controller). --}}
    <form class="barra-ricerca" method="get" action="{{ route('club.index') }}">
        <label class="per-page">
            Nazione:
            <select name="stato" onchange="this.form.submit()">
                <option value="">Tutte</option>
                @foreach ($nazioni as $n)
                    <option value="{{ $n }}" @selected($stato === $n)>{{ $n }}</option>
                @endforeach
            </select>
        </label>
        <input type="search" name="q" value="{{ $q }}" placeholder="Cerca un club…" autocomplete="off">
        <button type="submit">
            <img src="{{ route('img', ['tipo' => 'icons', 'file' => 'search.svg']) }}" alt="">
            Cerca
        </button>
    </form>

    <div id="tab-content">
        @if ($items->isEmpty())
            <p>Nessun club corrisponde alla ricerca.</p>
        @else
            <div class="elenco elenco-club">
                @foreach ($items as $c)
                    <a class="voce" href="{{ route('club.show', $c['id']) }}">
                        <span class="nome">
                            @if ($c['flag'])
                                <img class="flag-riga" src="{{ $c['flag'] }}" alt="{{ $c['stato'] }}"
                                     onerror="this.style.display='none'">
                            @else
                                <span class="flag-riga vuota"></span>
                            @endif
                            @include('partials.stemma-club', ['logo' => $c['logo'], 'lato' => 16, 'alt' => $c['nome']])
                            {{ $c['nome'] }}
                        </span>
                        <span class="extra">{{ $c['stato'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if ($items->lastPage() > 1)
        <div class="paginazione">
            @if ($items->onFirstPage())
                <span class="pg disab">‹</span>
            @else
                <a class="pg" href="{{ $items->previousPageUrl() }}">‹</a>
            @endif

            <span class="pg-stato">Pagina {{ $items->currentPage() }} di {{ $items->lastPage() }}
                <small>({{ $items->total() }} club)</small></span>

            @if ($items->hasMorePages())
                <a class="pg" href="{{ $items->nextPageUrl() }}">›</a>
            @else
                <span class="pg disab">›</span>
            @endif
        </div>
    @endif

    @include('partials.club-css')
@endsection
