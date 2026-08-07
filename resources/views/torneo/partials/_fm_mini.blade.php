{{-- Mini-card per l'albero completo: due righe compatte
     (bandiera + codice + punteggio), click -> popup partita.
     Variabili: $p (rigaPartita), $pid --}}
@php
    $daGiocare = ! $p['home']['code'] || ! $p['away']['code'];
    $homeCls = $p['winner'] === $p['home']['name'] ? 'vince' : ($p['winner'] === $p['away']['name'] ? 'eliminata' : '');
    $awayCls = $p['winner'] === $p['away']['name'] ? 'vince' : ($p['winner'] === $p['home']['name'] ? 'eliminata' : '');
@endphp

<div class="fm-mini" data-popup="{{ $pid }}"
     title="{{ ($p['home']['name'] ?: 'da definire').' - '.($p['away']['name'] ?: 'da definire') }}">
    <div class="r {{ $homeCls }}">
        @if ($p['home']['flag'])
            <img src="{{ $p['home']['flag'] }}" alt="" onerror="this.classList.add('rotta')">
        @else
            <span class="scudo"></span>
        @endif
        <span class="cod">{{ $p['home']['code'] ?: 'TBD' }}</span>
        <b>{{ $daGiocare ? '' : $p['home']['score'] }}</b>
    </div>
    <div class="r {{ $awayCls }}">
        @if ($p['away']['flag'])
            <img src="{{ $p['away']['flag'] }}" alt="" onerror="this.classList.add('rotta')">
        @else
            <span class="scudo"></span>
        @endif
        <span class="cod">{{ $p['away']['code'] ?: 'TBD' }}</span>
        <b>{{ $daGiocare ? '' : $p['away']['score'] }}</b>
    </div>
    @if ($p['replay'])
        <div class="nota">{{ $p['replay']['score'] }} d.R.</div>
    @elseif ($p['dcr'])
        <div class="nota">{{ $p['ris_rigori'] }} d.c.r.</div>
    @elseif ($p['dts'])
        <div class="nota">d.t.s.</div>
    @endif
</div>

@include('torneo.partials._popup_partita', ['p' => $p, 'pid' => $pid])
