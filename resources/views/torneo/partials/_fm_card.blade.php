{{-- Card partita stile FotMob (vista a turni): stadio in alto con barra
     colorata, due righe squadra (bandiera + nome + punteggio; data/ora se
     non giocata; "da definire" se squadra ignota), eliminata barrata,
     annotazioni d.t.s./d.c.r./replay come da handoff §4.
     Variabili: $p (rigaPartita), $pid --}}
@php
    $daGiocare = ! $p['home']['code'] || ! $p['away']['code'];
    $homeCls = $p['winner'] === $p['home']['name'] ? 'vince' : ($p['winner'] === $p['away']['name'] ? 'eliminata' : '');
    $awayCls = $p['winner'] === $p['away']['name'] ? 'vince' : ($p['winner'] === $p['home']['name'] ? 'eliminata' : '');
    $dataBreve = $p['date'] ? \Illuminate\Support\Carbon::parse($p['date'])->format('d/m/Y') : '';
    $oraBreve = $p['time'] ? substr($p['time'], 0, 5) : '';
@endphp

<div class="fm-card" data-popup="{{ $pid }}">
    @if ($p['stadium'])
        {{-- D1: lo stadio porta alla sua scheda. La card apre il popup
             partita, ma wc.js ignora i click partiti da un <a>. --}}
        <div class="fm-stadio"><i></i>
            @if (!empty($p['stadium_id']))
                <a href="{{ route('stadio.show', $p['stadium_id']) }}">{{ $p['stadium'] }}</a>
            @else
                <span>{{ $p['stadium'] }}</span>
            @endif
        </div>
    @endif

    <div class="fm-sq {{ $homeCls }}">
        @if ($p['home']['flag'])
            <img class="fm-flag" src="{{ $p['home']['flag'] }}" alt="" onerror="this.classList.add('rotta')">
        @else
            <span class="fm-flag scudo"></span>
        @endif
        <span class="fm-nome">{{ $p['home']['name'] ?: 'da definire' }}</span>
        <span class="fm-punti">{{ $daGiocare ? '' : $p['home']['score'] }}</span>
    </div>
    <div class="fm-sq {{ $awayCls }}">
        @if ($p['away']['flag'])
            <img class="fm-flag" src="{{ $p['away']['flag'] }}" alt="" onerror="this.classList.add('rotta')">
        @else
            <span class="fm-flag scudo"></span>
        @endif
        <span class="fm-nome">{{ $p['away']['name'] ?: 'da definire' }}</span>
        <span class="fm-punti">{{ $daGiocare ? '' : $p['away']['score'] }}</span>
    </div>

    @if ($daGiocare)
        <div class="fm-nota data">{{ $dataBreve }} {{ $oraBreve }}</div>
    @elseif ($p['replay'])
        <div class="fm-nota"><span class="risultato-replay">{{ $p['replay']['score'] }}</span>
            <small class="nota-replay">dopo replay match</small></div>
    @elseif ($p['dcr'])
        <div class="fm-nota">({{ $p['ris_rigori'] }} d.c.r.)</div>
    @elseif ($p['dts'])
        <div class="fm-nota">(d.t.s.)</div>
    @endif
</div>

@include('torneo.partials._popup_partita', ['p' => $p, 'pid' => $pid])
