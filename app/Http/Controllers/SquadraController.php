<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\GeoDemo;

class SquadraController extends Controller
{
    /**
     * Pagina squadra (guscio con barra dei tab).
     */
    public function show(string $code)
    {
        $code = strtoupper($code);

        $team = Team::where('team_code', $code)->first();
        abort_if(! $team, 404, 'Squadra non trovata');

        $geo    = GeoDemo::where('team_code', $code)->first();
        $titolo = $geo->original ?? $team->team_name;

        // Navigazione alfabetica tra le squadre visibili (visibility = 0)
        $prev = Team::where('visibility', 0)
            ->where('team_name', '<', $team->team_name)
            ->orderByDesc('team_name')
            ->first();

        $next = Team::where('visibility', 0)
            ->where('team_name', '>', $team->team_name)
            ->orderBy('team_name')
            ->first();

        // Bandiera più recente per il banner (stile banner torneo)
        $flag = app(\App\Services\WcService::class)->bandieraUrl($code, null);

        return view('squadra.show', compact('team', 'geo', 'titolo', 'prev', 'next', 'code', 'flag'));
    }

    /**
     * Contenuto del tab "info" (restituisce un frammento HTML, caricato via AJAX).
     */
    public function info(string $code)
    {
        $code = strtoupper($code);

        $team = Team::where('team_code', $code)->first();
        abort_if(! $team, 404);

        $geo = GeoDemo::where('team_code', $code)->first();

        // Mappa della nazione (stessi geojson del tab Squadre del torneo)
        $geoCode = \App\Services\TorneoService::ALIAS_GEOJSON[$code] ?? $code;
        $geojsonUrl = is_file(resource_path('geojson/'.$geoCode.'.geojson'))
            ? route('geojson', $geoCode)
            : null;

        return view('squadra.partials.info', compact('team', 'geo', 'geojsonUrl'));
    }

    /**
     * Contenuto del tab "partite": elenco delle partite della squadra.
     */
    public function partite(string $code)
    {
        $code = strtoupper($code);

        $partite = \App\Models\TeamAppearance::where('team_code', $code)
            ->orderBy('match_date')
            ->get();

        return view('squadra.partials.partite', compact('partite', 'code'));
    }public function presenze(string $code)
    {
        $code = strtoupper($code);

        // Mappa nome squadra -> codice, per ricostruire i link interni
        $mappa = \App\Models\Team::pluck('team_code', 'team_name')->all();

        $qualificate = \App\Models\QualifiedTeam::where('team_code', $code)->get()
            ->map(fn ($q) => [
                'tournament_id'   => $q->tournament_id,
                'tournament_name' => $q->tournament_name,
                'qualificata'     => true,
                'count_matches'   => $q->count_matches,
                'esito'           => $this->risolviLinkSquadra($q->performance, $mappa),
            ]);

        $non_qualificate = \App\Models\NotQualifiedTeam::where('team_code', $code)->get()
            ->map(fn ($n) => [
                'tournament_id'   => $n->tournament_id,
                'tournament_name' => $n->tournament_name,
                'qualificata'     => false,
                'count_matches'   => null,
                'esito'           => $this->risolviLinkSquadra($n->result, $mappa),
            ]);

        $presenze = $qualificate->concat($non_qualificate)
            ->sortBy('tournament_id')
            ->values();

        return view('squadra.partials.presenze', compact('presenze', 'code'));
    }

    /**
     * Sostituisce i link interni scritti coi NOMI (es. /squadra/Serbia) con i
     * link corretti basati sui CODICI (es. /squadra/SRB), cercati in awc_teams.
     * Se la squadra non si trova, lascia solo il testo senza link.
     */
    private function risolviLinkSquadra(?string $testo, array $mappa): string
    {
        $testo = (string) $testo;
        if ($testo === '') {
            return '';
        }

        return preg_replace_callback(
            "/<a\\s+href=['\"]\\/squadra\\/([^'\"]+)['\"]\\s*>(.*?)<\\/a>/i",
            function ($m) use ($mappa) {
                $nome          = urldecode($m[1]);
                $testoVisibile = $m[2];
                $codice        = $mappa[$nome] ?? null;

                if ($codice) {
                    $url = url('/squadra/' . $codice);
                    return "<a href=\"" . e($url) . "\">" . e($testoVisibile) . "</a>";
                }

                return e($testoVisibile);
            },
            $testo
        );
    }
    /**
     * Contenuto del tab "giocatori": tutti i giocatori convocati per questa
     * nazionale in tutti i Mondiali (da awc_squads), con partite giocate,
     * gol, elenco dei Mondiali (link squadra-anno + numero di maglia dal
     * 1954 in poi) e loghi dei club di provenienza.
     * Ordinamento, filtro e paginazione sono client-side (wc.js).
     */
    public function giocatori(string $code)
    {
        $code = strtoupper($code);

        $team = \App\Models\Team::where('team_code', $code)->first();
        abort_if(! $team, 404);

        $rows = \App\Models\Squad::where('team_code', $code)->get();

        $playerIds = $rows->pluck('player_id')->filter()->unique()->values();
        $players   = \App\Models\Player::whereIn('player_id', $playerIds)
            ->get()->keyBy('player_id');

        // Partite giocate con QUESTA nazionale (una query per tutti)
        $pg = \App\Models\PlayerAppearance::where('team_code', $code)
            ->whereIn('player_id', $playerIds)
            ->select('player_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) n'))
            ->groupBy('player_id')->pluck('n', 'player_id');

        // Gol fatti con questa nazionale (autogol esclusi)
        $gol = \App\Models\Goal::where('player_team_code', $code)
            ->whereIn('player_id', $playerIds)
            ->where(fn ($q) => $q->whereNull('own_goal')->orWhere('own_goal', 0))
            ->select('player_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) n'))
            ->groupBy('player_id')->pluck('n', 'player_id');

        // Loghi dei club di provenienza (tutti i club della carriera in nazionale)
        $clubIds = $rows->pluck('team_past_id')->filter()->unique()->values();
        $clubs   = \Illuminate\Support\Facades\DB::table('awc_clubs')
            ->whereIn('id', $clubIds)->get()->keyBy('id');

        $oggi = now();

        $giocatori = $rows->groupBy('player_id')->map(function ($conv, $pid) use ($players, $pg, $gol, $clubs, $oggi) {
            $p     = $players[$pid] ?? null;
            $birth = $p?->birth_date;

            // Un Mondiale per riga di rosa, in ordine di anno; numero di
            // maglia tra parentesi solo dal 1954 in poi (1930-1950: niente)
            $mondiali = $conv
                ->map(fn ($r) => [
                    'anno'   => (int) substr((string) $r->tournament_id, 3),
                    'maglia' => $r->shirt_number ?: null,
                ])
                ->sortBy('anno')->values()
                ->map(fn ($m) => [
                    'anno'   => $m['anno'],
                    'maglia' => $m['anno'] >= 1954 ? $m['maglia'] : null,
                ]);

            // Ruolo: lettere P/D/C/A dalle rose (puo' variare tra tornei)
            $ruoli = $conv->pluck('position_code')->filter()->unique()
                ->map(fn ($c) => SquadraAnnoController::RUOLI[$c] ?? null)
                ->filter()->sortBy(fn ($r) => $r[1])->values();

            // Club distinti (dedup per id club; ignora righe senza club)
            $clubList = $conv->pluck('team_past_id')->filter()->unique()
                ->map(fn ($id) => $clubs[$id] ?? null)->filter()
                ->map(fn ($c) => ['nome' => $c->club_name, 'logo' => $c->logo ?: null])
                ->values();

            return [
                'player_id'  => $pid,
                'given'      => $conv->first()->given_name,
                'family'     => $conv->first()->family_name,
                'nascita'    => $birth,
                'eta'        => $birth ? $birth->diff($oggi)->y : null,
                'ruolo'      => $ruoli->pluck(0)->implode('/') ?: null,
                'ruolo_ord'  => $ruoli->isNotEmpty() ? $ruoli->first()[1] : 9,
                'mondiali'   => $mondiali,
                'n_mondiali' => $mondiali->count(),
                'pg'         => (int) ($pg[$pid] ?? 0),
                'gol'        => (int) ($gol[$pid] ?? 0),
                'club'       => $clubList,
            ];
        })->values()
          // Ordine iniziale: cognome crescente (poi nome)
          ->sortBy(fn ($g) => mb_strtolower(($g['family'] ?? '').'|'.($g['given'] ?? '')))
          ->values();

        return view('squadra.partials.giocatori', compact('giocatori', 'code'));
    }

    /**
     * Contenuto del tab "managers": gli allenatori di questa nazionale
     * (da awc_manager_appointments), come l'elenco generale ma con
     * l'elenco dei Mondiali in cui hanno allenato la nazionale al posto
     * della bandiera. Ordine iniziale: primo Mondiale crescente.
     */
    public function managers(string $code)
    {
        $code = strtoupper($code);

        $team = \App\Models\Team::where('team_code', $code)->first();
        abort_if(! $team, 404);

        $managers = \App\Models\ManagerAppointment::where('team_code', $code)
            ->get()
            ->groupBy('manager_id')
            ->map(function ($apps) {
                $primo = $apps->first();
                $anni  = $apps
                    ->map(fn ($a) => (int) substr((string) $a->tournament_id, 3))
                    ->unique()->sort()->values();

                return [
                    'id'    => $primo->manager_id,
                    'nome'  => trim(($primo->given_name ?? '').' '.($primo->family_name ?? '')),
                    'anni'  => $anni,
                    'primo' => $anni->first(),
                ];
            })
            ->values()
            ->sortBy(fn ($m) => sprintf('%04d|%s', $m['primo'] ?? 9999, mb_strtolower($m['nome'])))
            ->values();

        return view('squadra.partials.managers', compact('managers', 'code'));
    }

    /**
     * Contenuto del tab "record": numeri aggregati della squadra ai Mondiali,
     * calcolati al volo da awc_team_appearances.
     */
    public function record(string $code)
    {
        $code = strtoupper($code);

        $partite = \App\Models\TeamAppearance::where('team_code', $code)->get();

        $giocate   = $partite->count();
        $vittorie  = $partite->where('win', 1)->count();
        $pareggi   = $partite->where('draw', 1)->count();
        $sconfitte = $partite->where('lose', 1)->count();
        $gol_fatti   = $partite->sum('goals_for');
        $gol_subiti  = $partite->sum('goals_against');

        $perc = fn ($n) => $giocate > 0 ? round($n / $giocate * 100) : 0;

        $record = [
            'giocate'     => $giocate,
            'vittorie'    => $vittorie,
            'pareggi'     => $pareggi,
            'sconfitte'   => $sconfitte,
            'gol_fatti'   => $gol_fatti,
            'gol_subiti'  => $gol_subiti,
            'perc_vittorie'  => $perc($vittorie),
            'perc_pareggi'   => $perc($pareggi),
            'perc_sconfitte' => $perc($sconfitte),
        ];

        return view('squadra.partials.record', compact('record', 'code'));
    }

    /**
     * Contenuto del tab "maglie": le maglie della nazionale a blocchi per
     * torneo (dal piu' vecchio al piu' recente), solo immagini.
     */
    public function maglie(string $code, \App\Services\MaglieService $maglie)
    {
        $code = strtoupper($code);

        $team = \App\Models\Team::where('team_code', $code)->first();
        abort_if(! $team, 404);

        return view('squadra.partials.maglie', [
            'code'    => $code,
            'blocchi' => $maglie->perSquadra($code),
        ]);
    }
}