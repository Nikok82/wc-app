<?php

namespace App\Http\Controllers;

use App\Services\PartitaService;

/**
 * Scheda partita (05/08): pagina guscio con testata fissa (risultato +
 * marcatori) e quattro tab caricate via fetch, come le altre sezioni:
 * Info / Formazioni / Eventi / Situazione. I dati arrivano tutti da
 * PartitaService.
 */
class PartitaController extends Controller
{
    public function __construct(protected PartitaService $srv)
    {
    }

    /** Pagina guscio: testata + bottoni tab. */
    public function show(string $matchId)
    {
        $m = $this->srv->partita($matchId);
        abort_unless($m, 404, 'Partita non trovata');

        return view('partita.show', [
            'matchId'    => $matchId,
            'm'          => $m,
            'testata'    => $this->srv->testata($m),
            'nonGiocata' => $this->srv->nonGiocata($m),
        ]);
    }

    /** Tab 1 — Info: data, ora, stadio, arbitro, maglie. */
    public function info(string $matchId)
    {
        $m = $this->srv->partita($matchId);
        abort_unless($m, 404);

        return view('partita.partials.info', [
            'm'    => $m,
            'info' => $this->srv->info($m),
        ]);
    }

    /** Tab 2 — Formazioni: campo + elenco delle rose. */
    public function formazioni(string $matchId)
    {
        $m = $this->srv->partita($matchId);
        abort_unless($m, 404);

        return view('partita.partials.formazioni', [
            'm'          => $m,
            'formazioni' => $this->srv->formazioni($m),
            'info'       => $this->srv->info($m),   // per le maglie di sfondo
        ]);
    }

    /** Tab 3 — Eventi: cronologia gol / cartellini / sostituzioni. */
    public function eventi(string $matchId)
    {
        $m = $this->srv->partita($matchId);
        abort_unless($m, 404);

        return view('partita.partials.eventi', [
            'm'      => $m,
            'eventi' => $this->srv->eventi($m),
        ]);
    }

    /** Tab 4 — Situazione: classifica girone (prima/dopo) o turno KO o podio. */
    public function situazione(string $matchId)
    {
        $m = $this->srv->partita($matchId);
        abort_unless($m, 404);

        return view('partita.partials.situazione', [
            'm'          => $m,
            'situazione' => $this->srv->situazione($m),
        ]);
    }
}
