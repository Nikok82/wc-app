{{-- Bracket fase a eliminazione stile FotMob (handoff bracket-fotmob):
     due viste sulla stessa struttura dati.
     1) Vista a turni: pillole orizzontali + colonne di card scorrevoli
        (scroll-snap; su desktop si vedono piu' colonne affiancate).
     2) Vista albero: turni a cascata che convergono sulla card FINALE
        (trofeo + campione), finale 3° posto come ramo laterale.
     Bracket a 32 (2026): variante scelta da $opt32 (1..4), in prova.
     Variabili: $bracket, $rounds, $imp, $tournamentId, $opt32 --}}
@php
    $stages = array_keys($bracket);                       // senza 3° posto, senza replay
    $terzo = collect($rounds)->firstWhere('stage', 'finale per il terzo posto');
    $e32 = isset($bracket['sedicesimi di finale']);
    $opt = $e32 ? max(1, min(4, (int) $opt32)) : 0;

    // Campione (per la card FINALE dell'albero)
    $finale = $bracket['finale']['partite'][0] ?? null;
    $campione = null;
    if ($finale && $finale['winner']) {
        $lato = $finale['winner'] === $finale['home']['name'] ? 'home' : 'away';
        $campione = $finale[$lato];
    }

    // Colonne dell'albero secondo la variante 32
    $stagesAlbero = $stages;
    $sedicesimiLista = null;
    if ($opt === 4) {
        $stagesAlbero = array_values(array_diff($stages, ['sedicesimi di finale']));
        $sedicesimiLista = $bracket['sedicesimi di finale']['partite'];
    }
    $mostraAlbero = ! ($opt === 1);   // opzione 1: niente albero per i bracket a 32
@endphp

<div class="fm-bracket" data-vista="turni">

    {{-- pillole dei turni (vista a turni) --}}
    <div class="fm-pills">
        @foreach ($stages as $i => $s)
            <span class="fm-pill {{ $i === 0 ? 'active' : '' }}" data-round="{{ $s }}">{{ $bracket[$s]['label'] }}</span>
        @endforeach
        @if ($terzo)
            <span class="fm-pill" data-round="terzo">3° posto</span>
        @endif
    </div>

    {{-- ================= VISTA 1: turni ================= --}}
    <div class="fm-vista" data-vista="turni">
        <div class="fm-cols">
            @foreach ($stages as $si => $s)
                @php
                    $partite = $bracket[$s]['partite'];
                    $haSuccessivo = $si < count($stages) - 1;
                @endphp
                <div class="fm-col" data-round="{{ $s }}">
                    <div class="fm-col-titolo">{{ $bracket[$s]['label'] }}</div>
                    @foreach ($partite->chunk(2) as $coppia)
                        <div class="fm-pair {{ $haSuccessivo && $coppia->count() === 2 ? 'con-graffa' : '' }}">
                            @foreach ($coppia as $p)
                                @include('torneo.partials._fm_card', ['p' => $p, 'pid' => 'pop-fmt-'.$p['match_id']])
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach

            @if ($terzo)
                <div class="fm-col" data-round="terzo">
                    <div class="fm-col-titolo">Finale 3° posto</div>
                    @foreach ($terzo['partite'] as $p)
                        @if (! $p['e_replay'])
                            <div class="fm-pair">
                                @include('torneo.partials._fm_card', ['p' => $p, 'pid' => 'pop-fmt-'.$p['match_id']])
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ================= VISTA 2: albero completo ================= --}}
    @if ($mostraAlbero)
        <div class="fm-vista" data-vista="albero" style="display:none">

            @if ($opt === 4 && $sedicesimiLista)
                <div class="fm-lista-compatta">
                    <div class="fm-lista-titolo">Sedicesimi di finale</div>
                    <div class="fm-lista-griglia">
                        @foreach ($sedicesimiLista as $p)
                            @include('torneo.partials._fm_mini', ['p' => $p, 'pid' => 'pop-fma-'.$p['match_id']])
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="fm-tree-scroll">
                <div class="fm-tree {{ $opt === 3 ? 'compatta-iniziale' : '' }}">
                    @foreach ($stagesAlbero as $si => $s)
                        @php
                            $partite = $bracket[$s]['partite'];
                            $eFinale = $s === 'finale';
                            $compatta = $opt === 3 && in_array($s, ['sedicesimi di finale', 'ottavi di finale'], true);
                        @endphp
                        <div class="fm-tcol {{ $compatta ? 'compatta' : '' }} {{ $eFinale ? 'colonna-finale' : '' }}">
                            <div class="fm-tcol-titolo">
                                @if ($eFinale)<span class="badge-finale">FINALE</span>@else{{ $bracket[$s]['label'] }}@endif
                            </div>
                            <div class="fm-tcol-corpo">
                                @if ($eFinale)
                                    <div class="fm-finale-blocco">
                                        @foreach ($partite as $p)
                                            @if (! $p['e_replay'])
                                                @include('torneo.partials._fm_mini', ['p' => $p, 'pid' => 'pop-fma-'.$p['match_id']])
                                            @endif
                                        @endforeach
                                        <div class="fm-campione">
                                            <span class="trofeo">{!! \App\Support\Icons::svg('trophy') !!}</span>
                                            @if ($campione)
                                                @if ($campione['flag'])
                                                    <img src="{{ $campione['flag'] }}" alt="" onerror="this.style.display='none'">
                                                @endif
                                                <span>{{ $campione['name'] }}</span>
                                            @else
                                                <span class="ignoto">?</span>
                                            @endif
                                        </div>

                                        @if ($terzo)
                                            <div class="fm-terzo">
                                                <span class="badge-terzo">3°</span>
                                                @foreach ($terzo['partite'] as $p)
                                                    @if (! $p['e_replay'])
                                                        @include('torneo.partials._fm_mini', ['p' => $p, 'pid' => 'pop-fma3-'.$p['match_id']])
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    @foreach ($partite->chunk(2) as $coppia)
                                        <div class="fm-tpair {{ $coppia->count() === 2 ? 'con-graffa' : '' }}">
                                            @foreach ($coppia as $p)
                                                @include('torneo.partials._fm_mini', ['p' => $p, 'pid' => 'pop-fma-'.$p['match_id']])
                                            @endforeach
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- bottone circolare flottante: passa da una vista all'altra --}}
        <button class="fm-switch" type="button" title="Cambia vista">
            <span class="ico-albero">{!! \App\Support\Icons::svg('playoff') !!}</span>
            <span class="ico-turni">{!! \App\Support\Icons::svg('list') !!}</span>
        </button>
    @endif
</div>
