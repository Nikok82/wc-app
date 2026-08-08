<?php

namespace App\Http\Controllers;

use App\Services\TorneoPartiteService;
use App\Services\TorneoService;

/**
 * Pagina Torneo: guscio con barra dei 5 tab (Info, Partite, Squadre,
 * Record, Marcatori) caricati via fetch, sullo stesso impianto della
 * sezione squadra. Per ora e' funzionante il tab Info; gli altri quattro
 * mostrano un segnaposto e verranno costruiti nelle fasi successive.
 *
 * URL canonico: /torneo/WC-1990 . Si accetta anche /torneo/1990 con
 * redirect 301 al formato canonico (compatibilita' coi vecchi link).
 */
class TorneoController extends Controller
{
    public function __construct(protected TorneoService $svc)
    {
    }

    /** Guscio pagina torneo. */
    public function show(string $tournamentId)
    {
        // /torneo/1990 -> /torneo/WC-1990
        if (preg_match('/^\d{4}$/', $tournamentId)) {
            return redirect()->route('torneo.show', 'WC-'.$tournamentId, 301);
        }

        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404, 'Torneo non trovato');

        $anno = $this->svc->anno($tournamentId);

        return view('torneo.show', [
            'tournamentId' => $tournamentId,
            'torneo'       => $torneo,
            'anno'         => $anno,
            'titolo'       => $this->svc->titolo($torneo),
            'sfondo'       => $this->svc->manifestoUrl($anno),
            'prev'         => $this->svc->adiacente($anno, 'prev'),
            'next'         => $this->svc->adiacente($anno, 'next'),
        ]);
    }

    /** Tab Info (frammento HTML via fetch). */
    public function info(string $tournamentId)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        return view('torneo.partials.info', [
            'tournamentId' => $tournamentId,
            'torneo'       => $torneo,
            'podio'        => $this->svc->podio($tournamentId),
            'righe'        => $this->svc->infoRighe($torneo),
            'premi'        => $this->svc->premi($tournamentId),
        ]);
    }

    /** Tab Partite (frammento HTML via fetch): gironi + eliminazione + bracket. */
    public function partite(string $tournamentId, TorneoPartiteService $partite)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        $imp = $partite->impostazioni($torneo);

        return view('torneo.partials.partite', [
            'tournamentId' => $tournamentId,
            'imp'          => $imp,
            'fase1'        => $imp['fase1']
                ? $partite->gironi($tournamentId, $partite->stageFase1($tournamentId))
                : collect(),
            'fase2'        => $imp['fase2']
                ? $partite->gironi($tournamentId, 'seconda fase a gruppi')
                : collect(),
            'gironeFinale' => $imp['girone_finale']
                ? $partite->gironi($tournamentId, 'girone finale')
                : collect(),
            'rounds'       => $partite->eliminazione($tournamentId, $imp),
            'bracket'      => $partite->bracket($tournamentId, $imp),
            // Variante di layout per l'albero dei bracket a 32 (2026):
            // 1 = solo vista a turni, 2 = albero scrollabile, 3 = albero con
            // primi turni compattati, 4 = ibrido (sedicesimi a lista).
            // In prova su ?opt32=N finche' Niko non sceglie.
            'opt32'        => (int) request()->query('opt32', 1),
        ]);
    }

    /** Tab Squadre (frammento HTML via fetch): card + mappa Leaflet. */
    public function squadre(string $tournamentId)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        $squadre = $this->svc->squadre($tournamentId);

        return view('torneo.partials.squadre', [
            'tournamentId' => $tournamentId,
            'squadre'      => $squadre,
            'paesi'        => $this->svc->mappaPaesi($squadre),
        ]);
    }

    public function record(string $tournamentId)
    {
        return view('torneo.partials.placeholder', ['sezione' => 'Record']);
    }

    /**
     * Tab Classifica (frammento HTML via fetch): sub-tab "Torneo"
     * (class_mond di awc_results_for_year) e "Perpetua" (somma di tutti
     * i Mondiali fino a questo, con le medaglie cumulate).
     */
    public function classifica(string $tournamentId, \App\Services\ClassificaService $cls)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        $anno = $this->svc->anno($tournamentId);

        return view('torneo.partials.classifica', [
            'tournamentId'  => $tournamentId,
            'anno'          => $anno,
            'torneoRighe'   => $cls->torneo($tournamentId),
            'perpetuaRighe' => $cls->perpetua($anno, $tournamentId),
        ]);
    }

    /**
     * Tab Arbitri (frammento HTML via fetch, 04/08): gli arbitri convocati
     * al torneo da awc_referee_appointments, con popup scheda arbitro.
     */
    public function arbitri(string $tournamentId, \App\Services\WcService $wc)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        $arbitri = \Illuminate\Support\Facades\DB::table('awc_referee_appointments')
            ->where('tournament_id', $tournamentId)
            ->orderBy('family_name')->orderBy('given_name')
            ->get()
            ->map(fn ($a) => [
                'id'    => $a->referee_id,
                'nome'  => trim(($a->given_name ?? '').' '.($a->family_name ?? '')),
                'extra' => trim(($a->country_name ?? '').($a->confederation_code ? ' — '.$a->confederation_code : '')),
                'flag'  => $wc->bandieraUrl($a->country_name, $tournamentId),
            ]);

        return view('torneo.partials.persone', [
            'titolo'      => 'Arbitri del Mondiale '.$this->svc->anno($tournamentId),
            'vuoto'       => 'Nessun arbitro trovato per questo torneo.',
            'persone'     => $arbitri,
            'routeScheda' => 'arbitro.scheda',
            'routeShow'   => 'arbitro.show',
        ]);
    }

    /**
     * Tab Managers (frammento HTML via fetch, 04/08): i commissari tecnici
     * del torneo da awc_manager_appointments, con la squadra allenata e
     * popup scheda allenatore.
     */
    public function managers(string $tournamentId, \App\Services\WcService $wc)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        $anno = $this->svc->anno($tournamentId);

        $managers = \Illuminate\Support\Facades\DB::table('awc_manager_appointments')
            ->where('tournament_id', $tournamentId)
            ->orderBy('team_name')->orderBy('family_name')
            ->get()
            ->map(fn ($m) => [
                'id'        => $m->manager_id,
                'nome'      => trim(($m->given_name ?? '').' '.($m->family_name ?? '')),
                'extra'     => $m->team_name,
                // Bandiera d'epoca della squadra allenata, link squadra-anno
                'flag'      => $wc->bandieraUrl($m->team_code, $tournamentId),
                'extra_url' => ($m->team_code && $anno)
                    ? route('squadra_anno.show', ['code' => $m->team_code, 'year' => $anno])
                    : null,
            ]);

        return view('torneo.partials.persone', [
            'titolo'      => 'Managers del Mondiale '.$anno,
            'vuoto'       => 'Nessun manager trovato per questo torneo.',
            'persone'     => $managers,
            'routeScheda' => 'allenatore.scheda',
            'routeShow'   => 'allenatore.show',
        ]);
    }

    /**
     * Tab Stadi (frammento HTML via fetch, 04/08): gli stadi del torneo dagli
     * awc_matches, con mappa a marker multipli (wc.js, .mappa-stadi) e
     * popup scheda stadio (riusa stadio.scheda della sezione /stadi).
     */
    public function stadi(string $tournamentId, \App\Services\WcService $wc)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        $stadi = \Illuminate\Support\Facades\DB::table('awc_matches as m')
            ->join('awc_stadiums as s', 's.stadium_id', '=', 'm.stadium_id')
            ->where('m.tournament_id', $tournamentId)
            ->groupBy('s.stadium_id', 's.stadium_name', 's.city_name', 's.country_name',
                's.stadium_capacity', 's.lat', 's.lng')
            ->selectRaw('s.stadium_id, s.stadium_name, s.city_name, s.country_name,
                s.stadium_capacity, s.lat, s.lng, COUNT(*) AS partite')
            ->orderBy('s.stadium_name')
            ->get();

        // Dati per la mappa: [lat, lng, "Stadio — Città (N partite)", url scheda]
        $marker = $stadi->filter(fn ($s) => $s->lat !== null && $s->lng !== null)
            ->map(fn ($s) => [
                (float) $s->lat, (float) $s->lng,
                $s->stadium_name.' — '.$s->city_name.' ('.$s->partite.' '.($s->partite == 1 ? 'partita' : 'partite').')',
                route('stadio.show', $s->stadium_id),
            ])->values();

        return view('torneo.partials.stadi', [
            'anno'   => $this->svc->anno($tournamentId),
            'stadi'  => $stadi,
            'marker' => $marker,
        ]);
    }

    /** Tab Marcatori (frammento HTML via fetch): classifica dell'intero torneo. */
    public function marcatori(string $tournamentId, TorneoPartiteService $partite)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        return view('torneo.partials.marcatori', [
            'tournamentId' => $tournamentId,
            'marc'         => $partite->marcatori($tournamentId),
        ]);
    }

    /**
     * Tab Maglie (frammento HTML via fetch): le maglie di tutte le squadre del
     * torneo, a blocchi per squadra (ordine alfabetico), solo immagini.
     */
    public function maglie(string $tournamentId, \App\Services\MaglieService $maglie)
    {
        $tournamentId = strtoupper($tournamentId);
        $torneo = $this->svc->torneo($tournamentId);
        abort_if(! $torneo, 404);

        return view('torneo.partials.maglie', [
            'tournamentId' => $tournamentId,
            'blocchi'      => $maglie->perTorneo($tournamentId),
        ]);
    }
}
