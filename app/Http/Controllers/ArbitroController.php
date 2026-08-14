<?php

namespace App\Http\Controllers;

use App\Models\Referee;
use App\Models\RefereeAppearance;
use App\Models\RefereeAppointment;
use App\Services\WcService;
use Illuminate\Http\Request;

class ArbitroController extends Controller
{
    public function __construct(protected WcService $wc)
    {
    }

    /**
     * Elenco arbitri: ricerca + paginazione (20/50/100) + popup scheda.
     */
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100], true) ? $perPage : 20;

        $items = Referee::query()
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
            ->through(fn ($r) => [
                'id'    => $r->referee_id,
                'nome'  => trim(($r->given_name ?? '').' '.($r->family_name ?? '')),
                'extra' => '',
                // Bandiera dalla nazione (stessa risoluzione del popup scheda)
                'flag'  => $this->wc->bandieraUrl($r->country_name, null),
            ]);

        return view('elenco', [
            'titolo'      => 'Arbitri',
            'items'       => $items,
            'q'           => $q,
            'perPage'     => $perPage,
            'routeIndex'  => 'arbitri.index',
            'routeScheda' => 'arbitro.scheda',
            'routeShow'   => 'arbitro.show',
            'labelExtra'  => null,
        ]);
    }

    /** Pagina completa della scheda arbitro. */
    public function show(string $id)
    {
        return view('arbitro.show',
            array_merge($this->datiScheda($id), $this->navigazione($id)));
    }

    /** Solo il frammento scheda (usato dal popup degli elenchi). */
    public function scheda(string $id)
    {
        return view('arbitro.scheda', $this->datiScheda($id));
    }

    /* ------------------------------------------------------------------ */

    /**
     * Barra bottoni: scheda precedente e successiva in ordine alfabetico,
     * lo stesso dell'elenco da cui si arriva.
     */
    protected function navigazione(string $id): array
    {
        [$prev, $next] = $this->wc->vicini(
            'awc_referees', 'referee_id', ['family_name', 'given_name'], $id
        );

        $voce = function ($v) {
            if (! $v) {
                return null;
            }

            return [
                'url'   => route('arbitro.show', $v->referee_id),
                'img'   => $this->wc->bandieraUrl($v->country_name, null),
                'forma' => 'tonda',
                'label' => trim($v->given_name.' '.$v->family_name),
            ];
        };

        return ['barraPrev' => $voce($prev), 'barraNext' => $voce($next)];
    }

    protected function datiScheda(string $id): array
    {
        $r = Referee::where('referee_id', $id)->first();
        abort_if(! $r, 404, 'Arbitro non trovato');

        /* ---- Tornei: da awc_referee_appointments ---- */
        $appointments = RefereeAppointment::where('referee_id', $r->referee_id)
            ->orderBy('tournament_id')
            ->get();

        $tornei = $appointments->map(fn ($a) => [
            'anno'          => $this->wc->anno($a->tournament_id),
            'tid'           => $a->tournament_id,
            'confederazione' => $a->confederation_code,
        ]);

        /* ---- Gare arbitrate: da awc_referee_appearances ---- */
        $gare = RefereeAppearance::where('referee_id', $r->referee_id)
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
            'r'        => $r,
            'nome'     => trim(($r->given_name ?? '').' '.($r->family_name ?? '')),
            'bandiere' => $bandiere,
            'tornei'   => $tornei,
            'gare'     => $gare,
        ];
    }
}
