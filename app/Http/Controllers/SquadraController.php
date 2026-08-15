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
        $wc   = app(\App\Services\WcService::class);
        $flag = $wc->bandieraUrl($code, null);

        // F6 — prev/next con la BANDIERA della nazionale invece del nome (il
        // nome resta in title/alt). Le stesse bandiere popolano anche gli slot
        // contestuali della barra bottoni mobile, prima vuoti in /squadra.
        $prevFlag = $prev ? $wc->bandieraUrl($prev->team_code, null) : null;
        $nextFlag = $next ? $wc->bandieraUrl($next->team_code, null) : null;

        // 'forma' => come va ritagliata la miniatura nella barra bandiera
        // tonda per le squadre, quadrata per le locandine dei tornei.
        $barraPrev = $prev ? [
            'url'   => route('squadra.show', $prev->team_code),
            'img'   => $prevFlag,
            'forma' => 'tonda',
            'label' => $prev->team_name,
        ] : null;
        $barraNext = $next ? [
            'url'   => route('squadra.show', $next->team_code),
            'img'   => $nextFlag,
            'forma' => 'tonda',
            'label' => $next->team_name,
        ] : null;

        // A1 — scorrimento laterale: stesse mete delle frecce.
        $swipeNav = ['prev' => $barraPrev, 'next' => $barraNext];

        return view('squadra.show', compact(
            'team', 'geo', 'titolo', 'prev', 'next', 'code', 'flag',
            'prevFlag', 'nextFlag', 'barraPrev', 'barraNext', 'swipeNav'
        ));
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
        $wc   = app(\App\Services\WcService::class);

        $partite = \App\Models\TeamAppearance::where('team_code', $code)
            ->orderBy('match_date')
            ->get();

        $gol = $wc->golPerPartite($partite->pluck('match_id')->all());

        // Le partite sono raggruppate per edizione, come nella pagina
        // squadra-anno: su piu' di ottanta incontri un elenco piatto non
        // dice piu' nulla.
        $gruppi = [];
        foreach ($partite as $p) {
            $titolo = $p->tournament_name ?: $p->tournament_id;

            // In casa la squadra sta a sinistra, in trasferta a destra:
            // l'ordine e' quello reale della partita, non quello del
            // punto di vista della squadra.
            $casa = (bool) $p->home_team;

            $meta = collect([
                $p->match_date ? $p->match_date->format('d/m/Y') : null,
                $p->stage_name ? mb_convert_case($p->stage_name, MB_CASE_TITLE) : null,
                $p->stadium_name ?? null,
            ])->filter()->implode(' · ');

            $gruppi[$titolo][] = [
                'match_id' => $p->match_id,
                'meta'     => $meta,
                'casa' => [
                    'nome' => $casa ? $p->team_name : $p->opponent_name,
                    'code' => $casa ? $p->team_code : $p->opponent_code,
                    'gol'  => $casa ? $p->goals_for : $p->goals_against,
                    'flag' => $wc->bandieraUrl($casa ? $p->team_code : $p->opponent_code, $p->tournament_id),
                ],
                'ospite' => [
                    'nome' => $casa ? $p->opponent_name : $p->team_name,
                    'code' => $casa ? $p->opponent_code : $p->team_code,
                    'gol'  => $casa ? $p->goals_against : $p->goals_for,
                    'flag' => $wc->bandieraUrl($casa ? $p->opponent_code : $p->team_code, $p->tournament_id),
                ],
            ];
        }

        return view('squadra.partials.partite', compact('gruppi', 'gol', 'code'));
    }

    public function presenze(string $code)
    {
        $code = strtoupper($code);

        $wc = app(\App\Services\WcService::class);

        // Mappa nome squadra -> codice, per ricostruire i link interni dell'esito
        $mappa = \App\Models\Team::pluck('team_code', 'team_name')->all();

        // Host di ogni torneo (una query). host_country risolve la bandiera
        // d'epoca anche per i co-ospitati: "Corea del Sud e Giappone" -> CSG
        // (2002), "United" -> UTD (2026).
        $hostMap = \Illuminate\Support\Facades\DB::table('awc_tournaments')
            ->pluck('host_country', 'tournament_id')->all();

        $host = function ($tid) use ($wc, $hostMap) {
            $h = $hostMap[$tid] ?? null;
            return [
                'host'      => $h,
                'host_flag' => $h ? $wc->bandieraUrl($h, $tid) : null,
                'anno'      => $wc->anno($tid),
            ];
        };

        $qualificate = \App\Models\QualifiedTeam::where('team_code', $code)->get()
            ->map(function ($q) use ($mappa, $host, $code, $wc) {
                $anno = $wc->anno($q->tournament_id);
                return $host($q->tournament_id) + [
                    'tournament_id'   => $q->tournament_id,
                    'tournament_name' => $q->tournament_name,
                    'qualificata'     => true,
                    'count_matches'   => $q->count_matches,
                    'esito'           => $this->risolviLinkSquadra($q->performance, $mappa),
                    // Riga cliccabile verso la scheda squadra-anno (ha giocato).
                    'url'             => $anno
                        ? route('squadra_anno.show', ['code' => $code, 'year' => $anno])
                        : null,
                ];
            });

        $non_qualificate = \App\Models\NotQualifiedTeam::where('team_code', $code)->get()
            ->map(function ($n) use ($mappa, $host) {
                return $host($n->tournament_id) + [
                    'tournament_id'   => $n->tournament_id,
                    'tournament_name' => $n->tournament_name,
                    'qualificata'     => false,
                    'count_matches'   => null,
                    'esito'           => $this->risolviLinkSquadra($n->result, $mappa),
                    'url'             => null,   // niente scheda anno se non qualificata
                ];
            });

        $presenze = $qualificate->concat($non_qualificate)
            ->sortBy('tournament_id')
            ->values();

        // Serie del piazzamento (class_mond) per il grafico a linea + tutti gli
        // anni-edizione per l'asse X (i buchi = anni da non qualificata).
        $teamId = \App\Models\QualifiedTeam::where('team_code', $code)->value('team_id')
               ?? \App\Models\NotQualifiedTeam::where('team_code', $code)->value('team_id');
        $serie = [];
        if ($teamId) {
            foreach (\Illuminate\Support\Facades\DB::table('awc_results_for_year')
                        ->where('team_id', $teamId)->get() as $r) {
                $anno = $wc->anno($r->tournament_id);
                if ($anno && $r->class_mond !== null) {
                    $pos = (int) $r->class_mond;
                    $serie[$anno] = [
                        'pos'   => $pos,
                        'medal' => ($pos >= 1 && $pos <= 3) ? $pos : null,
                        'res'   => $r->result_mond,
                    ];
                }
            }
        }
        $anniEdizioni = \Illuminate\Support\Facades\DB::table('awc_tournaments')
            ->distinct()->orderBy('year')->pluck('year')->map(fn ($y) => (int) $y)->all();

        return view('squadra.partials.presenze', compact('presenze', 'code', 'serie', 'anniEdizioni'));
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
        $wc   = app(\App\Services\WcService::class);

        $giocatori = $rows->groupBy('player_id')->map(function ($conv, $pid) use ($players, $pg, $gol, $clubs, $oggi, $wc) {
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
                ->map(fn ($c) => ['nome' => $c->club_name, 'logo' => $wc->logoClubUrl($c->logo)])
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
     * Contenuto del tab "risultati" (ex "record"): numeri aggregati V/N/P della
     * squadra ai Mondiali, calcolati al volo da awc_team_appearances. Le 3 torte
     * 3D arriveranno in Fase B; la tab "Record" dettagliata in Fase D.
     */
    /**
     * Tab Record: primati di tutta la storia della squadra ai Mondiali,
     * non della singola edizione.
     */
    public function record(string $code, \App\Services\RecordService $record)
    {
        $code = strtoupper($code);
        $rec  = $record->perSquadra($code);

        return view('partials.record', compact('rec', 'code'));
    }

    public function risultati(string $code)
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

        // V/N/P per le 3 torte: totale, prime fasi (gironi) e altre fasi (KO),
        // dalle colonne group_stage / knockout_stage di awc_team_appearances.
        $vnp = fn ($coll) => [
            'v' => $coll->where('win', 1)->count(),
            'n' => $coll->where('draw', 1)->count(),
            'p' => $coll->where('lose', 1)->count(),
        ];
        $torte = [
            'totale' => $vnp($partite),
            'prime'  => $vnp($partite->where('group_stage', 1)),
            'altre'  => $vnp($partite->where('knockout_stage', 1)),
        ];

        return view('squadra.partials.risultati', compact('record', 'code', 'torte'));
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