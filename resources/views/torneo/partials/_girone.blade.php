{{-- Un girone completo: titolo, card partite, classifica, marcatori.
     Variabili: $g (da TorneoPartiteService::gironi), $tournamentId, $ctx --}}
@php
    $nome = explode(' ', $g['group_name'], 2);
    // A2 (15/08): ancora del girone, bersaglio della riga "Gruppi: A • B • C".
    // Il contesto ($ctx) entra nell'id perche' prima e seconda fase a gironi
    // convivono nella stessa pagina e possono avere lettere uguali.
    $ancora = 'girone-'.$ctx.'-'.\Illuminate\Support\Str::slug($g['group_name']);
@endphp

<div class="groups" id="{{ $ancora }}">
    <div class="title-group">
        @if ($g['e_finale_1950'])
            <span class="g1">Girone</span><span class="g2 finale-1950">Finale</span>
        @else
            <span class="g1">{{ $nome[0] }}</span><span class="g2">{{ $nome[1] ?? '' }}</span>
        @endif
    </div>

    <div class="body-group">
        {{-- B3 (15/08): le partite stanno raccolte e si aprono al clic, come
             gia' fa il riquadro dei marcatori. <details> invece di uno script:
             il frammento arriva via fetch e i <script> non verrebbero eseguiti,
             mentre il comportamento nativo funziona sempre. --}}
        <details class="part-box">
            <summary>
                <span class="label">Partite</span>
                <span class="arrow">▼</span>
            </summary>
            <div class="partite-list">
                @foreach ($g['partite'] as $p)
                    @include('torneo.partials._match_card', [
                        'p'   => $p,
                        'pid' => 'pop-'.$ctx.'-'.$p['match_id'],
                        // Nei gironi una sconfitta non elimina: niente barratura.
                        // Nel girone finale del 1950 vale lo stesso principio.
                        'barra' => false,
                    ])
                @endforeach
            </div>
        </details>

        <div class="body-table">
            <div class="table-gironi">
                <div class="row head">
                    <div class="cel c1"><span>#</span></div>
                    <div class="cel c2"><span>Team</span></div>
                    <div class="cel"><span>G</span></div>
                    <div class="cel"><span>W</span></div>
                    <div class="cel"><span>N</span></div>
                    <div class="cel"><span>P</span></div>
                    <div class="cel"><span>GF</span></div>
                    <div class="cel"><span>GS</span></div>
                    <div class="cel c9"><span>+/-</span></div>
                    <div class="cel"><span>PT</span></div>
                </div>
                @foreach ($g['classifica'] as $r)
                    <div class="row body {{ $r->advanced ? 'advanced' : '' }}">
                        <div class="cel c1"><span>{{ $r->position }}</span></div>
                        <div class="cel c2">
                            @if ($r->flag)
                                <img class="flag" src="{{ $r->flag }}" alt="{{ $r->team_code }}"
                                     onerror="this.style.display='none'">
                            @endif
                            <a href="{{ route('squadra.show', $r->team_code) }}">
                                <span class="tname puntini">{{ $r->team_name }}</span><span class="tcode">{{ $r->team_code }}</span>
                            </a>
                        </div>
                        <div class="cel"><span>{{ $r->played }}</span></div>
                        <div class="cel"><span>{{ $r->wins }}</span></div>
                        <div class="cel"><span>{{ $r->draws }}</span></div>
                        <div class="cel"><span>{{ $r->losses }}</span></div>
                        <div class="cel"><span>{{ $r->goals_for }}</span></div>
                        <div class="cel"><span>{{ $r->goals_against }}</span></div>
                        <div class="cel c9"><span>{{ $r->goal_difference }}</span></div>
                        <div class="cel"><span>{{ $r->points }}</span></div>
                    </div>
                @endforeach
            </div>
        </div>

        @include('torneo.partials._marcatori_box', ['marc' => $g['marcatori']])
    </div>
</div>
