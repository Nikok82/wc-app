<?php

namespace App\Http\Controllers;

use App\Services\WcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Sezione Stadi (04/08): elenco /stadi nello stile di /arbitri (ricerca +
 * paginazione + popup scheda) e scheda stadio con dati, mappa Leaflet
 * (lat/lng aggiunte ad awc_stadiums il 04/08) e TUTTE le partite dei
 * Mondiali giocate in quello stadio (awc_matches.stadium_id).
 */
class StadioController extends Controller
{
    public function __construct(protected WcService $wc)
    {
    }

    /** Elenco stadi: ricerca per nome/citta'/paese + paginazione (20/50/100). */
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100], true) ? $perPage : 20;

        $items = DB::table('awc_stadiums')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('stadium_name', 'like', '%'.$q.'%')
                      ->orWhere('city_name', 'like', '%'.$q.'%')
                      ->orWhere('country_name', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('stadium_name')
            ->orderBy('city_name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($s) => [
                'id'    => $s->stadium_id,
                'nome'  => $s->stadium_name,
                'extra' => trim($s->city_name.' ('.$s->country_name.')'),
                // Bandiera attuale del paese (match per nome in awc_flags)
                'flag'  => $this->wc->bandieraUrl($s->country_name, null),
            ]);

        return view('elenco', [
            'titolo'      => 'Stadi',
            'items'       => $items,
            'q'           => $q,
            'perPage'     => $perPage,
            'routeIndex'  => 'stadi.index',
            'routeScheda' => 'stadio.scheda',
            'routeShow'   => 'stadio.show',
            'labelExtra'  => null,
            'placeholder' => 'Cerca per stadio, città o paese…',
        ]);
    }

    /** Pagina completa della scheda stadio. */
    public function show(string $id)
    {
        return view('stadio.show', $this->datiScheda($id));
    }

    /** Solo il frammento scheda (popup dell'elenco e della tab torneo). */
    public function scheda(string $id)
    {
        return view('stadio.scheda', $this->datiScheda($id));
    }

    /* ------------------------------------------------------------------ */

    protected function datiScheda(string $id): array
    {
        $s = DB::table('awc_stadiums')->where('stadium_id', $id)->first();
        abort_if(! $s, 404, 'Stadio non trovato');

        /* ---- Tutte le partite giocate in questo stadio ---- */
        $partite = DB::table('awc_matches')
            ->where('stadium_id', $s->stadium_id)
            ->orderBy('match_date')
            ->orderBy('match_id')
            ->get()
            ->map(fn ($m) => [
                'anno'     => $this->wc->anno($m->tournament_id),
                'tid'      => $m->tournament_id,
                'data'     => $m->match_date ? date('d/m/Y', strtotime($m->match_date)) : '',
                'stage'    => $m->stage_name,
                'score'    => $m->score,
                'match'    => $this->wc->celleMatch($m->match_name, $m->tournament_id,
                    $m->home_team_win, $m->away_team_win),
                'match_id' => $m->match_id,
            ]);

        /* ---- Mondiali ospitati (per la riga riassuntiva) ---- */
        $tornei = $partite->groupBy('tid')->map(fn ($g, $tid) => [
            'tid'  => $tid,
            'anno' => $this->wc->anno($tid),
            'n'    => $g->count(),
        ])->sortBy('anno')->values();

        return [
            's'       => $s,
            'nome'    => $s->stadium_name,
            'flag'    => $this->wc->bandieraUrl($s->country_name, null),
            'partite' => $partite,
            'tornei'  => $tornei,
        ];
    }
}
