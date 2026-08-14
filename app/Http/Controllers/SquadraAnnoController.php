<?php

namespace App\Http\Controllers;

use App\Models\GeoDemo;
use App\Models\Goal;
use App\Models\ManagerAppointment;
use App\Models\Player;
use App\Models\PlayerAppearance;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamAppearance;
use App\Models\Tournament;
use App\Services\MaglieService;
use App\Services\WcService;
use Illuminate\Support\Facades\DB;

/**
 * Scheda "squadra-anno": una nazionale in un singolo Mondiale
 * (es. /squadra/ITA-1990). Stessa impostazione della scheda squadra,
 * ma con tab Partite/Record limitate al torneo e la tab Convocati
 * (rosa completa con club di provenienza) al posto di Presenze.
 */
class SquadraAnnoController extends Controller
{
    /** position_code (awc_squads) -> lettera ruolo e ordine P,D,C,A. */
    public const RUOLI = [
        'GK' => ['P', 0],
        'DF' => ['D', 1],
        'MF' => ['C', 2],
        'FW' => ['A', 3],
    ];

    public function __construct(
        protected WcService $wc,
        protected MaglieService $maglie,
    ) {
    }

    /** Risolve squadra + torneo o 404. */
    protected function base(string $code, string $year): array
    {
        $code = strtoupper($code);
        $tid  = 'WC-'.$year;

        $team = Team::where('team_code', $code)->first();
        abort_if(! $team, 404, 'Squadra non trovata');

        $torneo = Tournament::where('tournament_id', $tid)->first();
        abort_if(! $torneo, 404, 'Torneo non trovato');

        // La squadra-anno esiste solo se la nazionale ha partecipato al torneo
        $partecipa = DB::table('awc_qualified_teams')
            ->where('tournament_id', $tid)->where('team_code', $code)->exists();
        abort_if(! $partecipa, 404, 'La squadra non ha partecipato a questo torneo');

        return [$code, $tid, $team, $torneo];
    }

    /** Guscio pagina squadra-anno. */
    public function show(string $code, string $year)
    {
        [$code, $tid, $team, $torneo] = $this->base($code, $year);

        $geo    = GeoDemo::where('team_code', $code)->first();
        $titolo = ($geo->original ?? $team->team_name).' ('.$year.')';

        // Navigazione: partecipazione precedente/successiva della stessa nazionale
        $anni = DB::table('awc_qualified_teams')
            ->where('team_code', $code)->pluck('tournament_id')
            ->map(fn ($t) => (int) substr($t, 3))->sort()->values();
        $pos  = $anni->search((int) $year);
        $prev = ($pos !== false && $pos > 0) ? $anni[$pos - 1] : null;
        $next = ($pos !== false && $pos < $anni->count() - 1) ? $anni[$pos + 1] : null;

        return view('squadra_anno.show', [
            'team'   => $team,
            'geo'    => $geo,
            'titolo' => $titolo,
            'code'   => $code,
            'year'   => (int) $year,
            'tid'    => $tid,
            // Bandiera dell'epoca per il banner (stessa logica delle schede)
            'flag'   => $this->wc->bandieraUrl($code, $tid),
            'prev'   => $prev,
            'next'   => $next,
            // Barra bottoni globale: si salta all'edizione precedente e
            // successiva della stessa nazionale, con la locandina del
            // Mondiale di destinazione come miniatura quadrata.
            'barraPrev' => $prev ? [
                'url'   => route('squadra_anno.show', ['code' => $code, 'year' => $prev]),
                'img'   => $this->wc->bandieraUrl($code, 'WC-'.$prev),
                'forma' => 'tonda',
                'label' => $titolo.' '.$prev,
            ] : null,
            'barraNext' => $next ? [
                'url'   => route('squadra_anno.show', ['code' => $code, 'year' => $next]),
                'img'   => $this->wc->bandieraUrl($code, 'WC-'.$next),
                'forma' => 'tonda',
                'label' => $titolo.' '.$next,
            ] : null,
        ]);
    }

    /** Tab Info: identica alla scheda squadra (stesso partial). */
    public function info(string $code, string $year)
    {
        [$code] = $this->base($code, $year);

        $team = Team::where('team_code', $code)->first();
        $geo  = GeoDemo::where('team_code', $code)->first();

        $geoCode    = \App\Services\TorneoService::ALIAS_GEOJSON[$code] ?? $code;
        $geojsonUrl = is_file(resource_path('geojson/'.$geoCode.'.geojson'))
            ? route('geojson', $geoCode)
            : null;

        return view('squadra.partials.info', compact('team', 'geo', 'geojsonUrl'));
    }

    /** Tab Partite: solo le partite del torneo. */
    public function partite(string $code, string $year)
    {
        [$code, $tid] = $this->base($code, $year);

        $partite = TeamAppearance::where('team_code', $code)
            ->where('tournament_id', $tid)
            ->orderBy('match_date')
            ->get();

        return view('squadra.partials.partite', compact('partite', 'code'));
    }

    /** Tab Convocati: rosa + allenatori + statistiche della spedizione. */
    public function convocati(string $code, string $year)
    {
        [$code, $tid, $team, $torneo] = $this->base($code, $year);

        $rows = Squad::where('tournament_id', $tid)
            ->where('team_code', $code)
            ->get();

        $playerIds = $rows->pluck('player_id')->filter()->unique()->values();
        $players   = Player::whereIn('player_id', $playerIds)->get()->keyBy('player_id');

        // Presenze e gol nel torneo (una query per metrica)
        $pg = PlayerAppearance::where('tournament_id', $tid)
            ->whereIn('player_id', $playerIds)
            ->select('player_id', DB::raw('COUNT(*) n'))
            ->groupBy('player_id')->pluck('n', 'player_id');

        $gol = Goal::where('tournament_id', $tid)
            ->whereIn('player_id', $playerIds)
            ->where(fn ($q) => $q->whereNull('own_goal')->orWhere('own_goal', 0))
            ->select('player_id', DB::raw('COUNT(*) n'))
            ->groupBy('player_id')->pluck('n', 'player_id');

        // Loghi dei club di provenienza
        $clubIds = $rows->pluck('team_past_id')->filter()->unique()->values();
        $clubs   = DB::table('awc_clubs')->whereIn('id', $clubIds)->get()->keyBy('id');

        $inizio = $torneo->start_date;   // età calcolata all'inizio del torneo

        $convocati = $rows->map(function ($r) use ($players, $pg, $gol, $clubs, $inizio) {
            $p     = $r->player_id ? ($players[$r->player_id] ?? null) : null;
            $birth = $p?->birth_date;
            $eta   = ($birth && $inizio) ? $birth->diff($inizio)->y : null;
            $ruolo = self::RUOLI[$r->position_code] ?? [null, 9];
            $club  = $r->team_past_id ? ($clubs[$r->team_past_id] ?? null) : null;

            return [
                'player_id'  => $r->player_id,
                'given'      => $r->given_name,
                'family'     => $r->family_name,
                // Nel DB "senza numero di maglia" è 0 (tornei anteguerra): lo
                // normalizziamo a null per far scattare l'ordinamento per ruolo.
                'numero'     => $r->shirt_number ?: null,
                'nascita'    => $birth,
                'eta'        => $eta,
                'ruolo'      => $ruolo[0],
                'ruolo_ord'  => $ruolo[1],
                'pg'         => (int) ($r->player_id ? ($pg[$r->player_id] ?? 0) : 0),
                'gol'        => (int) ($r->player_id ? ($gol[$r->player_id] ?? 0) : 0),
                'club'       => $r->team_past ?: null,
                'club_logo'  => $this->wc->logoClubUrl($club->logo ?? null),
                'club_stato' => $club->stato ?? null,
            ];
        });

        // Ordine iniziale: numeri di maglia se presenti, altrimenti ruolo P,D,C,A
        // con i giocatori in ordine alfabetico dentro il ruolo.
        $haNumeri = $convocati->contains(fn ($c) => $c['numero'] !== null);
        $convocati = $haNumeri
            ? $convocati->sortBy(fn ($c) => $c['numero'] ?? 999)->values()
            : $convocati->sortBy(fn ($c) => sprintf('%d|%s|%s', $c['ruolo_ord'], mb_strtolower($c['family'] ?? ''), mb_strtolower($c['given'] ?? '')))->values();

        /* ---- Allenatori della spedizione ---- */
        $allenatori = ManagerAppointment::where('tournament_id', $tid)
            ->where('team_code', $code)->get()
            ->map(fn ($a) => [
                'id'   => $a->manager_id,
                'nome' => trim(($a->given_name ?? '').' '.($a->family_name ?? '')),
                'flag' => $this->wc->bandieraUrl($a->country_name, $tid),
            ]);

        /* ---- Statistiche della spedizione ---- */
        $gare = TeamAppearance::where('team_code', $code)->where('tournament_id', $tid)->get();

        $eta = $convocati->pluck('eta')->filter(fn ($e) => $e !== null);

        // Autogol subiti = autogol segnati da giocatori di questa squadra
        $autogol = Goal::where('tournament_id', $tid)
            ->where('player_team_code', $code)
            ->where('own_goal', 1)->count();

        // Club della nazione vs estero: confronto con il nome-paese moderno
        // (awc_geo_demo) oltre che col nome squadra (es. "Germania Ovest").
        $geo   = GeoDemo::where('team_code', $code)->first();
        $paesi = array_filter(array_unique([$team->team_name, $geo->team_name ?? null, $geo->original ?? null]));
        $conClub  = $convocati->filter(fn ($c) => $c['club'] !== null);
        $inPatria = $conClub->filter(fn ($c) => $c['club_stato'] !== null && in_array($c['club_stato'], $paesi, true))->count();

        $stats = [
            'convocati'    => $convocati->count(),
            'eta_media'    => $eta->isNotEmpty() ? round($eta->avg(), 1) : null,
            'gol_fatti'    => (int) $gare->sum('goals_for'),
            'autogol'      => $autogol,
            'club_patria'  => $inPatria,
            'club_estero'  => $conClub->count() - $inPatria,
            'club_ignoti'  => $convocati->count() - $conClub->count(),
        ];

        return view('squadra_anno.partials.convocati', [
            'convocati'  => $convocati,
            'allenatori' => $allenatori,
            'stats'      => $stats,
            'haNumeri'   => $haNumeri,
            'code'       => $code,
            'year'       => (int) $year,
        ]);
    }

    /**
     * Tab Maglie: le maglie indossate dalla nazionale nel torneo, ordinate per
     * numero di partite (la piu' usata per prima), con sotto ciascuna le
     * partite in cui e' stata indossata, linkate alla scheda partita.
     */
    public function maglie(string $code, string $year)
    {
        [$code, $tid] = $this->base($code, $year);

        return view('squadra_anno.partials.maglie', [
            'maglie' => $this->maglie->perSquadraTorneo($code, $tid),
            'code'   => $code,
            'year'   => (int) $year,
        ]);
    }

    /** Tab Record: gli stessi numeri della scheda squadra, sul solo torneo. */
    public function record(string $code, string $year, \App\Services\RecordService $record)
    {
        [$code, $tid] = $this->base($code, $year);

        // I primati dell'edizione, calcolati sulle sole partite di questa
        // squadra in questo torneo.
        $rec = $record->perSquadraAnno($code, $tid);

        return view('partials.record', compact('rec', 'code'));
    }
}
