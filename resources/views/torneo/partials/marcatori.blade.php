{{-- Tab Marcatori (Fase 4): classifica marcatori dell'intero torneo,
     raggruppata per numero di gol decrescente, autogol in riga dedicata.
     Aperta di default (nessuna riga espandibile). --}}
<div id="torneo-marcatori">
    <div class="titolo-sezione">Marcatori</div>

    @if (empty($marc['gruppi']) && empty($marc['autogol']))
        <p>Nessun marcatore registrato per questo torneo.</p>
    @else
        <div class="marc-grid">
            <div class="grid-head gol">Gol</div>
            <div class="grid-head">Marcatore</div>

            @foreach ($marc['gruppi'] as $gol => $giocatori)
                <div class="grid-cell gol">{{ $gol }}</div>
                <div class="grid-cell marcatori">
                    @foreach ($giocatori as $g)
                        <span class="marcatore">
                            @if ($g['flag'])
                                <img class="flag" src="{{ $g['flag'] }}" alt="{{ $g['team_code'] }}"
                                     onerror="this.style.display='none'">
                            @endif
                            <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>
                        </span>
                    @endforeach
                </div>
            @endforeach

            @if (!empty($marc['autogol']))
                <div class="grid-cell gol"><span class="autogol">Autogol:</span> {{ $marc['autogol']['tot'] }}</div>
                <div class="grid-cell marcatori">
                    @foreach ($marc['autogol']['autori'] as $g)
                        <span class="marcatore">
                            @if ($g['flag'])
                                <img class="flag" src="{{ $g['flag'] }}" alt="{{ $g['team_code'] }}"
                                     onerror="this.style.display='none'">
                            @endif
                            <a href="{{ route('giocatore.show', $g['player_id']) }}">{{ $g['nome'] }}</a>
                            <em class="autogol">(autogol)</em>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

<style>
    #torneo-marcatori .marc-grid{display:grid;grid-template-columns:64px 1fr;gap:0;}
    #torneo-marcatori .grid-head{font-weight:700;padding:8px 4px;border-bottom:2px solid var(--verde2);
        color:var(--verde-scuro);}
    #torneo-marcatori .grid-head.gol,#torneo-marcatori .grid-cell.gol{text-align:center;}
    #torneo-marcatori .grid-cell{padding:9px 4px;border-bottom:1px solid #eee;font-size:14px;}
    #torneo-marcatori .grid-cell.gol{font-weight:800;font-size:17px;color:var(--verde-scuro);}
    #torneo-marcatori .marcatore{display:inline-flex;align-items:center;gap:5px;
        margin:3px 14px 3px 0;white-space:nowrap;}
    #torneo-marcatori .marcatore .flag{width:19px;height:19px;border-radius:50%;object-fit:cover;
        box-shadow:0 1px 2px rgba(0,0,0,.25);}
    #torneo-marcatori .autogol{font-size:.75em;font-style:italic;}
</style>
