<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Tournament;
use App\Services\TorneoService;
use App\Services\WcService;
use Illuminate\Support\Facades\DB;

/**
 * Home provvisoria: menu con i collegamenti alle sezioni realizzate finora,
 * piu' i due box "Scopri una nazionale" / "Scopri un torneo" (03/08):
 * a ogni caricamento estraggono a sorte una squadra e un torneo; il bottone
 * "Mostra un'altra/o" ricarica il solo frammento via fetch (rotte
 * home.box.squadra / home.box.torneo, delega .scopri-altro in wc.js).
 */
class HomeController extends Controller
{
    public function __construct(protected WcService $wc, protected TorneoService $torneoSvc)
    {
    }

    public function index()
    {
        $squadre = Team::where('visibility', 0)
            ->orderBy('team_name')
            ->get(['team_code', 'team_name']);

        $tornei = Tournament::whereNotNull('tournament_id')
            ->orderByDesc('year')
            ->get(['tournament_id', 'year', 'host_country']);

        return view('home', [
            'squadre'    => $squadre,
            'tornei'     => $tornei,
            'boxSquadra' => $this->squadraCasuale(),
            'boxTorneo'  => $this->torneoCasuale(),
        ]);
    }

    /** Frammento del box squadra (fetch dal bottone "Mostra un'altra squadra"). */
    public function boxSquadra()
    {
        return view('partials.box-squadra', ['box' => $this->squadraCasuale()]);
    }

    /** Frammento del box torneo (fetch dal bottone "Mostra un altro torneo"). */
    public function boxTorneo()
    {
        return view('partials.box-torneo', ['box' => $this->torneoCasuale()]);
    }

    /**
     * Squadra a sorte tra quelle che hanno giocato almeno un Mondiale
     * (awc_results_for_year): WC = partecipazioni, poi PG V N P GF GS
     * cumulati e medaglie (class_mond 1/2/3). Bandiera attuale.
     */
    protected function squadraCasuale(): array
    {
        $r = DB::table('awc_results_for_year as r')
            ->join('awc_teams as t', 't.team_id', '=', 'r.team_id')
            ->selectRaw('t.team_code, t.team_name,
                COUNT(*)              AS wc,
                SUM(r.partite_giocate) AS pg,
                SUM(r.vittorie)        AS v,
                SUM(r.pareggi)         AS n,
                SUM(r.sconfitte)       AS p,
                SUM(r.gol_fatti)       AS gf,
                SUM(r.gol_subiti)      AS gs,
                SUM(r.class_mond = 1)  AS ori,
                SUM(r.class_mond = 2)  AS argenti,
                SUM(r.class_mond = 3)  AS bronzi')
            ->groupBy('r.team_id', 't.team_code', 't.team_name')
            ->inRandomOrder()
            ->first();

        return [
            'team_code' => $r->team_code,
            'team_name' => $r->team_name,
            'flag'      => $this->wc->bandieraUrl($r->team_code, null),
            'url'       => route('squadra.show', $r->team_code),
            'stats'     => [
                'WC' => (int) $r->wc,  'PG' => (int) $r->pg,
                'V'  => (int) $r->v,   'N'  => (int) $r->n,  'P' => (int) $r->p,
                'GF' => (int) $r->gf,  'GS' => (int) $r->gs,
            ],
            'medaglie'  => [(int) $r->ori, (int) $r->argenti, (int) $r->bronzi],
        ];
    }

    /**
     * Torneo a sorte: bandiera d'epoca del paese ospitante (match per nome
     * in awc_flags; per host multipli o "United" 2026 resta null e non si
     * mostra), mini manifesto e podio. Per il 2026 awc_tournament_standings
     * e' vuota: fallback sul class_mond 1-3 di awc_results_for_year.
     */
    protected function torneoCasuale(): array
    {
        $t = Tournament::whereNotNull('tournament_id')->inRandomOrder()->first();
        $tid = $t->tournament_id;

        $podio = $this->torneoSvc->podio($tid);

        if (! $podio) {
            $righe = DB::table('awc_results_for_year as r')
                ->join('awc_teams as t', 't.team_id', '=', 'r.team_id')
                ->where('r.tournament_id', $tid)
                ->whereIn('r.class_mond', [1, 2, 3])
                ->orderBy('r.class_mond')
                ->get(['r.class_mond', 't.team_code', 't.team_name']);

            foreach ($righe as $r) {
                $podio[(int) $r->class_mond] = [
                    'posizione'   => (int) $r->class_mond,
                    'team_code'   => $r->team_code,
                    'team_name'   => $r->team_name,
                    'flag'        => $this->wc->bandieraUrl($r->team_code, $tid),
                    'squadra_url' => $r->team_code ? route('squadra.show', $r->team_code) : null,
                ];
            }
        }

        return [
            'tournament_id' => $tid,
            'anno'          => $t->year,
            'host'          => $t->host_country,
            'flag_host'     => $this->wc->bandieraUrl($t->host_country, $tid),
            'manifesto'     => route('img', ['tipo' => 'tornei', 'file' => 'mini-'.$t->year.'.jpg']),
            'url'           => route('torneo.show', $tid),
            'podio'         => $podio,
        ];
    }
}
