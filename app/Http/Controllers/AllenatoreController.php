<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Models\ManagerAppearance;
use App\Models\ManagerAppointment;
use App\Services\WcService;
use Illuminate\Http\Request;

class AllenatoreController extends Controller
{
    public function __construct(protected WcService $wc)
    {
    }

    /**
     * Elenco allenatori: ricerca + paginazione (20/50/100) + popup scheda.
     */
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100], true) ? $perPage : 20;

        $items = Manager::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('given_name', 'like', '%'.$q.'%')
                      ->orWhere('family_name', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('family_name')
            ->orderBy('given_name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($m) => [
                'id'    => $m->manager_id,
                'nome'  => trim(($m->given_name ?? '').' '.($m->family_name ?? '')),
                'extra' => '',
                // Bandiera dalla nazione (stessa risoluzione del popup scheda)
                'flag'  => $this->wc->bandieraUrl($m->country_name, null),
            ]);

        return view('elenco', [
            'titolo'      => 'Allenatori',
            'items'       => $items,
            'q'           => $q,
            'perPage'     => $perPage,
            'routeIndex'  => 'allenatori.index',
            'routeScheda' => 'allenatore.scheda',
            'routeShow'   => 'allenatore.show',
            'labelExtra'  => null,
        ]);
    }

    /** Pagina completa della scheda allenatore. */
    public function show(string $id)
    {
        return view('allenatore.show',
            array_merge($this->datiScheda($id), $this->navigazione($id)));
    }

    /** Solo il frammento scheda (usato dal popup degli elenchi). */
    public function scheda(string $id)
    {
        return view('allenatore.scheda', $this->datiScheda($id));
    }

    /* ------------------------------------------------------------------ */

    /**
     * Barra bottoni: scheda precedente e successiva in ordine alfabetico,
     * lo stesso dell'elenco da cui si arriva.
     */
    protected function navigazione(string $id): array
    {
        [$prev, $next] = $this->wc->vicini(
            'awc_managers', 'manager_id', ['family_name', 'given_name'], $id
        );

        $voce = function ($v) {
            if (! $v) {
                return null;
            }

            return [
                'url'   => route('allenatore.show', $v->manager_id),
                'img'   => $this->wc->bandieraUrl($v->country_name, null),
                'forma' => 'tonda',
                'label' => trim($v->given_name.' '.$v->family_name),
            ];
        };

        return ['barraPrev' => $voce($prev), 'barraNext' => $voce($next)];
    }

    protected function datiScheda(string $id): array
    {
        $m = Manager::where('manager_id', $id)->first();
        abort_if(! $m, 404, 'Allenatore non trovato');

        /* ---- Tornei: da awc_manager_appointments ---- */
        $appointments = ManagerAppointment::where('manager_id', $m->manager_id)
            ->orderBy('tournament_id')
            ->get();

        $tornei = $appointments->map(fn ($a) => [
            'anno'   => $this->wc->anno($a->tournament_id),
            'tid'    => $a->tournament_id,
            'squadra' => $a->team_name,
            'flag'   => $this->wc->bandieraUrl($a->team_name, $a->tournament_id),
        ]);

        /* ---- Gare allenate: da awc_manager_appearances ---- */
        $gare = ManagerAppearance::where('manager_id', $m->manager_id)
            ->orderBy('match_date')
            ->get()
            ->map(fn ($a) => [
                'data'     => $a->match_date ? $a->match_date->format('d/m/Y') : '',
                'stage'    => $a->stage_name,
                'match'    => $this->wc->celleMatch($a->match_name, $a->tournament_id, $a->home_team, $a->away_team),
                'match_id' => $a->match_id,
            ]);

        /* ---- Bandiere in alto: dalla nazionalita' (country_name) ---- */
        $bandiere = $appointments
            ->map(fn ($a) => $this->wc->bandieraUrl($a->country_name, $a->tournament_id))
            ->filter()->unique()->values()->all();

        return [
            'm'        => $m,
            // Se il manager è stato matchato con un giocatore (awc_managers.player_id),
            // la scheda mostra anche il ruolo "Giocatore" con link alla scheda giocatore.
            'playerId' => $m->player_id ?: null,
            'nome'     => trim(($m->given_name ?? '').' '.($m->family_name ?? '')),
            'bandiere' => $bandiere,
            'tornei'   => $tornei,
            'gare'     => $gare,
            'wikipedia' => $this->wc->haColonna('awc_managers', 'manager_wikipedia_link')
                ? $m->manager_wikipedia_link : null,
        ];
    }
}
