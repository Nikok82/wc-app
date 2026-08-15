<?php

namespace App\Http\Controllers;

use App\Services\RicercaService;
use Illuminate\Http\Request;

/**
 * Ricerca globale (A3 del todo 15/08).
 *
 * Restituisce un FRAMMENTO HTML, non JSON: il riquadro dei risultati viene
 * infilato nella pagina come gia' avviene per i tab e per i popup delle
 * schede, e non serve un secondo motore di impaginazione lato browser.
 *
 * Parametri: q (la stringa digitata), tipo e pagina (per sfogliare i
 * risultati di un solo tipo, dieci per volta).
 */
class RicercaController extends Controller
{
    public function __construct(protected RicercaService $svc)
    {
    }

    public function cerca(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $tipo   = $request->query('tipo');
        $pagina = max(1, (int) $request->query('pagina', 1));

        // Sotto i due caratteri qualunque ricerca restituirebbe mezzo
        // database: si risponde con l'invito a scrivere ancora un po'.
        if (mb_strlen($q) < 2) {
            return view('ricerca.risultati', [
                'q'      => $q,
                'gruppi' => [],
                'totale' => 0,
                'avviso' => $q === '' ? null : 'Scrivi almeno due caratteri.',
            ]);
        }

        $esito = $this->svc->cerca($q, is_string($tipo) ? $tipo : null, $pagina);

        return view('ricerca.risultati', [
            'q'      => $q,
            'gruppi' => $esito['gruppi'],
            'totale' => $esito['totale'],
            'avviso' => $esito['avviso'],
        ]);
    }
}
