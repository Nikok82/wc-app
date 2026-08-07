{{-- Bracket grafico (vista "playoff"): struttura a specchio come il vecchio
     tema — meta' alta dei turni verso la finale al centro, poi meta' bassa
     a scendere. Bandierine tonde, box risultato cliccabili (popup partita).
     Variabili: $bracket (da TorneoPartiteService::bracket), $tournamentId --}}
@php
    // $bracket: [stage => ['label','teams','partite']] gia' in ordine di torneo.
    $stages = array_keys($bracket);
    $finaleKey = 'finale';
    $pre = array_values(array_filter($stages, fn ($s) => $s !== $finaleKey));
    $meta = [];   // per ogni stage pre-finale: partite divise in meta' alta/bassa
    foreach ($pre as $s) {
        $ps = $bracket[$s]['partite'];
        $half = intdiv($ps->count(), 2);
        $meta[$s] = [
            'top'    => $ps->slice(0, $half)->values(),
            'bottom' => $ps->slice($half)->values(),
        ];
    }
    $finale = $bracket[$finaleKey]['partite'][0] ?? null;

    $rigaFlags = function ($partite) {
        $flags = [];
        foreach ($partite as $p) {
            $flags[] = $p['home'];
            $flags[] = $p['away'];
        }
        return $flags;
    };
@endphp

<div class="bracket-wrap">
    {{-- meta' alta --}}
    @foreach ($pre as $s)
        @php $top = $meta[$s]['top']; @endphp
        <div class="br-row br-flags" data-n="{{ count($rigaFlags($top)) }}">
            @foreach ($rigaFlags($top) as $f)
                <span class="bflag effetto_luce" title="{{ $f['name'] }}"
                      @if($f['flag']) style="background-image:url('{{ $f['flag'] }}')" @endif></span>
            @endforeach
        </div>
        <div class="br-row br-results" data-n="{{ $top->count() }}">
            @foreach ($top as $p)
                @include('torneo.partials._bracket_ris', ['p' => $p, 'pid' => 'pop-br-'.$p['match_id']])
            @endforeach
        </div>
    @endforeach

    {{-- finale al centro --}}
    @if ($finale)
        <div class="br-finale">
            <span class="bflag grande effetto_luce" title="{{ $finale['home']['name'] }}"
                  @if($finale['home']['flag']) style="background-image:url('{{ $finale['home']['flag'] }}')" @endif></span>
            @include('torneo.partials._bracket_ris', ['p' => $finale, 'pid' => 'pop-br-'.$finale['match_id']])
            <span class="bflag grande effetto_luce" title="{{ $finale['away']['name'] }}"
                  @if($finale['away']['flag']) style="background-image:url('{{ $finale['away']['flag'] }}')" @endif></span>
        </div>
        <div class="br-finale-label">Finale</div>
    @endif

    {{-- meta' bassa (specchiata) --}}
    @foreach (array_reverse($pre) as $s)
        @php $bottom = $meta[$s]['bottom']; @endphp
        <div class="br-row br-results" data-n="{{ $bottom->count() }}">
            @foreach ($bottom as $p)
                @include('torneo.partials._bracket_ris', ['p' => $p, 'pid' => 'pop-br-'.$p['match_id']])
            @endforeach
        </div>
        <div class="br-row br-flags" data-n="{{ count($rigaFlags($bottom)) }}">
            @foreach ($rigaFlags($bottom) as $f)
                <span class="bflag effetto_luce" title="{{ $f['name'] }}"
                      @if($f['flag']) style="background-image:url('{{ $f['flag'] }}')" @endif></span>
            @endforeach
        </div>
    @endforeach
</div>
