<?php

namespace App\Services;

use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dati del tab Partite della pagina Torneo (Fase 2), tradotti dal vecchio
 * tema WP (ajaxcampionato.php / function-varie.php) su Laravel/Eloquent.
 *
 * L'impostazione del torneo (quali fasi esistono, dimensione del bracket)
 * NON e' hardcodata per anno: viene derivata dalle colonne fase di
 * awc_tournaments, validate sui 23 tornei (combaciano con la mappa
 * dell'handoff al 100%).
 *
 * NB schema (verificato sul dump reale):
 * - awc_groups / awc_group_standings usano "prima fase a gruppi" per la
 *   prima fase del 1974-82, mentre awc_matches / awc_goals usano
 *   "fase a gruppi": la mappatura e' gestita da $mappaStageMatch.
 * - 1950: prima fase "first round", girone finale "girone finale"
 *   (stesso group_name "Gruppo 1" del first round: filtrare sempre
 *   anche per stage).
 * - Replay 1934/38: la gara d'andata ha replayed=1, la ripetizione
 *   replay=1; la coppia condivide stage_name e position.
 */
class TorneoPartiteService
{
    /** Ordine canonico delle fasi a eliminazione (stage DB => etichetta). */
    public const KO_STAGES = [
        'sedicesimi di finale'       => 'Sedicesimi',
        'ottavi di finale'           => 'Ottavi',
        'quarti di finale'           => 'Quarti',
        'semifinali'                 => 'Semifinali',
        'finale'                     => 'Finale',
        'finale per il terzo posto'  => 'Finale 3° posto',
    ];

    /** Stage in awc_groups/standings => stage in awc_matches/awc_goals. */
    protected array $mappaStageMatch = [
        'prima fase a gruppi'   => 'fase a gruppi',
        'fase a gruppi'         => 'fase a gruppi',
        'first round'           => 'first round',
        'seconda fase a gruppi' => 'seconda fase a gruppi',
        'girone finale'         => 'girone finale',
    ];

    /** Cache per richiesta: gol del torneo raggruppati per match_id. */
    protected array $golPerMatch = [];

    public function __construct(protected WcService $wc)
    {
    }

    /* ------------------------------------------------------------------ */
    /*  Impostazione torneo                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Deriva l'impostazione dalle colonne fase di awc_tournaments.
     *
     * @return array{fase1:bool,fase2:bool,girone_finale:bool,ko_start:?string,
     *               bracket:int,bracket_grafico:bool,terzo_posto:bool,solo_ko:bool}
     */
    public function impostazioni(Tournament $t): array
    {
        $fase1 = (bool) $t->fase_a_gruppi;
        $fase2 = (bool) $t->seconda_fase_a_gruppi;
        $gironeFinale = (bool) $t->final_round;   // solo 1950

        $bracket = 0;
        $koStart = null;
        if ($t->sedicesimi) {
            [$bracket, $koStart] = [32, 'sedicesimi di finale'];
        } elseif ($t->ottavi_finale) {
            [$bracket, $koStart] = [16, 'ottavi di finale'];
        } elseif ($t->quarti_di_finale) {
            [$bracket, $koStart] = [8, 'quarti di finale'];
        } elseif ($t->semifinali) {
            [$bracket, $koStart] = [4, 'semifinali'];
        } elseif ($t->finale) {
            // 1974/78: solo finale (+3° posto) dopo le due fasi a gruppi
            [$bracket, $koStart] = [2, 'finale'];
        }

        return [
            'fase1'           => $fase1,
            'fase2'           => $fase2,
            'girone_finale'   => $gironeFinale,
            'ko_start'        => $koStart,
            'bracket'         => $bracket,
            // bracket grafico solo per 4/8/16/32 squadre (1974/78 = solo elenco)
            'bracket_grafico' => in_array($bracket, [4, 8, 16, 32], true),
            'terzo_posto'     => (bool) $t->finale_per_il_terzo_posto,
            // 1934/38: nessun girone -> il tab si apre sull'eliminazione
            'solo_ko'         => ! $fase1 && ! $gironeFinale,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Gironi                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Stage della prima fase a gruppi in awc_groups per questo torneo
     * ('fase a gruppi' | 'prima fase a gruppi' | 'first round').
     */
    public function stageFase1(string $tid): ?string
    {
        return DB::table('awc_groups')
            ->where('tournament_id', $tid)
            ->whereIn('stage_name', ['fase a gruppi', 'prima fase a gruppi', 'first round'])
            ->value('stage_name');
    }

    /**
     * Gironi di una fase, ciascuno con partite, classifica e marcatori.
     * $stageGroups e' lo stage come appare in awc_groups.
     */
    public function gironi(string $tid, ?string $stageGroups): Collection
    {
        if (! $stageGroups) {
            return collect();
        }

        $stageMatch = $this->mappaStageMatch[$stageGroups] ?? $stageGroups;

        return DB::table('awc_groups')
            ->where('tournament_id', $tid)
            ->where('stage_name', $stageGroups)
            ->orderBy('key_id')
            ->get()
            ->map(fn ($g) => [
                'group_name' => $g->group_name,
                'e_finale_1950' => $stageGroups === 'girone finale',
                'partite'    => $this->partiteGruppo($tid, $g->group_name, $stageMatch),
                'classifica' => $this->classifica($tid, $g->group_name, $stageGroups),
                'marcatori'  => $this->marcatori($tid, $stageMatch, $g->group_name),
            ]);
    }

    protected function partiteGruppo(string $tid, string $group, string $stageMatch): Collection
    {
        $matches = DB::table('awc_matches')
            ->where('tournament_id', $tid)
            ->where('group_name', $group)
            ->where('stage_name', $stageMatch)
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->get();

        return $matches->map(fn ($m) => $this->rigaPartita($m, $matches));
    }

    public function classifica(string $tid, string $group, string $stageGroups): Collection
    {
        $anno = $this->wc->anno($tid);

        return DB::table('awc_group_standings')
            ->where('tournament_id', $tid)
            ->where('group_name', $group)
            ->where('stage_name', $stageGroups)
            ->orderBy('position')
            ->get()
            ->map(function ($r) use ($tid) {
                $r->flag = $this->wc->bandieraUrl($r->team_code, $tid);

                return $r;
            });
    }

    /* ------------------------------------------------------------------ */
    /*  Fase a eliminazione (vista elenco)                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Round della fase a eliminazione in ordine, con partite annotate
     * (dts / dcr / d.R.) e riga marcatori per fase.
     */
    public function eliminazione(string $tid, array $imp): array
    {
        if (! $imp['ko_start']) {
            return [];
        }

        $rounds = [];
        foreach (self::KO_STAGES as $stage => $label) {
            if ($stage === 'finale per il terzo posto' && ! $imp['terzo_posto']) {
                continue;
            }

            $matches = DB::table('awc_matches')
                ->where('tournament_id', $tid)
                ->where('stage_name', $stage)
                ->orderBy('position')
                ->orderBy('key_id')
                ->get();

            if ($matches->isEmpty()) {
                continue;
            }

            $rounds[] = [
                'stage'     => $stage,
                'label'     => $label,
                'partite'   => $matches->map(fn ($m) => $this->rigaPartita($m, $matches)),
                'marcatori' => $this->marcatori($tid, $stage),
            ];
        }

        return $rounds;
    }

    /**
     * Normalizza una riga di awc_matches per le view (card partita).
     * $sameStage (righe della stessa fase) serve a risolvere i replay
     * 1934/38: coppia con stesso stage+position, andata replayed=1,
     * ripetizione replay=1.
     */
    public function rigaPartita(object $m, ?Collection $sameStage = null): array
    {
        $anno = $this->wc->anno($m->tournament_id);

        // Risultato mostrato: rigori se d.c.r., altrimenti gol (come il vecchio sito)
        if ($m->penalty_shootout) {
            $ris = $m->home_team_score_penalties.'-'.$m->away_team_score_penalties;
            $winner = $m->home_team_score_penalties > $m->away_team_score_penalties
                ? $m->home_team_name : $m->away_team_name;
        } else {
            $ris = $m->home_team_score.'-'.$m->away_team_score;
            $winner = null;
            if ($m->home_team_score != $m->away_team_score) {
                $winner = $m->home_team_score > $m->away_team_score
                    ? $m->home_team_name : $m->away_team_name;
            }
        }

        // Replay 1934/38
        $replay = null;
        $eReplay = ! empty($m->replay) && (int) $m->replay === 1;
        $eReplayed = ! empty($m->replayed) && (int) $m->replayed === 1;
        if ($eReplayed && $sameStage) {
            $linked = $sameStage->first(fn ($x) => (int) ($x->replay ?? 0) === 1
                && $x->position == $m->position && $x->key_id != $m->key_id);
            if ($linked) {
                $replay = [
                    'score'    => $linked->home_team_score.'-'.$linked->away_team_score,
                    'match_id' => $linked->match_id,
                ];
                if (! $winner) {
                    $winner = $linked->home_team_score > $linked->away_team_score
                        ? $linked->home_team_name : $linked->away_team_name;
                }
            }
        }

        return [
            'match_id'   => $m->match_id,
            'date'       => $m->match_date,
            'time'       => $m->match_time,
            'stadium'    => $m->stadium_name,
            'city'       => $m->city_name ?? null,
            'stage'      => $m->stage_name,
            'group'      => $m->group_name,
            'home'       => [
                'code'  => $m->home_team_code,
                'name'  => $m->home_team_name,
                'score' => $m->home_team_score,
                'flag'  => $this->wc->bandieraUrl($m->home_team_code, $anno),
            ],
            'away'       => [
                'code'  => $m->away_team_code,
                'name'  => $m->away_team_name,
                'score' => $m->away_team_score,
                'flag'  => $this->wc->bandieraUrl($m->away_team_code, $anno),
            ],
            'ris'        => $ris,
            'ris_gol'    => $m->home_team_score.'-'.$m->away_team_score,
            'ris_rigori' => $m->penalty_shootout
                ? $m->home_team_score_penalties.'-'.$m->away_team_score_penalties : null,
            'winner'     => $winner,
            'dts'        => (bool) $m->extra_time,
            'dcr'        => (bool) $m->penalty_shootout,
            'e_replay'   => $eReplay,     // questa card E' la ripetizione
            'replay'     => $replay,      // sulla card d'andata: dati della ripetizione
            'marcatori_match' => $this->golPartita($m->tournament_id, $m->match_id, $anno),
        ];
    }

    /** Gol di una singola partita (per il popup), da cache per torneo. */
    public function golPartita(string $tid, string $matchId, ?int $anno): array
    {
        if (! array_key_exists($tid, $this->golPerMatch)) {
            $this->golPerMatch[$tid] = DB::table('awc_goals')
                ->where('tournament_id', $tid)
                ->orderBy('key_id')
                ->get()
                ->groupBy('match_id');
        }

        return collect($this->golPerMatch[$tid]->get($matchId, collect()))
            ->map(fn ($g) => [
                'player_id' => $g->player_id,
                'nome'      => trim(mb_substr((string) $g->given_name, 0, 1).'. '.$g->family_name, '. '),
                'minuto'    => $g->minute_label,
                'own_goal'  => (bool) $g->own_goal,
                'penalty'   => (bool) $g->penalty,
                'team_code' => $g->player_team_code ?: $g->team_code,
                'flag'      => $this->wc->bandieraUrl($g->team_code, $anno),
            ])->all();
    }

    /* ------------------------------------------------------------------ */
    /*  Bracket grafico                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Dati per il bracket grafico: per ogni fase (in ordine di torneo)
     * squadre ORDER BY position (meta' sinistra / meta' destra) e partite.
     * I replay NON entrano nel bracket come slot: la coppia replayed/replay
     * viene fusa nella card d'andata (score + score replay + d.R.).
     */
    public function bracket(string $tid, array $imp): array
    {
        if (! $imp['bracket_grafico']) {
            return [];
        }

        $stages = array_keys(self::KO_STAGES);
        $stages = array_slice($stages, array_search($imp['ko_start'], $stages, true));
        $stages = array_values(array_diff($stages, ['finale per il terzo posto']));

        $out = [];
        foreach ($stages as $stage) {
            $matches = DB::table('awc_matches')
                ->where('tournament_id', $tid)
                ->where('stage_name', $stage)
                ->orderBy('position')
                ->orderBy('key_id')
                ->get();
            if ($matches->isEmpty()) {
                continue;
            }

            // Slot bracket: escludi le ripetizioni (replay=1)
            $slot = $matches->filter(fn ($m) => (int) ($m->replay ?? 0) !== 1)->values();

            $teams = [];
            foreach ($slot as $m) {
                foreach ([$m->home_team_code, $m->away_team_code] as $code) {
                    if ($code && ! in_array($code, $teams, true)) {
                        $teams[] = $code;
                    }
                }
            }

            $anno = $this->wc->anno($tid);

            $out[$stage] = [
                'label'   => self::KO_STAGES[$stage],
                'teams'   => array_map(fn ($c) => [
                    'code' => $c,
                    'flag' => $this->wc->bandieraUrl($c, $anno),
                ], $teams),
                'partite' => $slot->map(fn ($m) => $this->rigaPartita($m, $matches))->values(),
            ];
        }

        return $out;
    }

    /* ------------------------------------------------------------------ */
    /*  Marcatori (per girone / per fase / per torneo)                     */
    /* ------------------------------------------------------------------ */

    /**
     * Marcatori raggruppati per numero di gol decrescente; autogol in riga
     * dedicata "Autogol: N" con l'elenco degli autori (come il vecchio sito).
     * $stageMatch e $group sono opzionali (null = tutto il torneo).
     *
     * @return array{gruppi: array<int, array<int, array>>, autogol: ?array}
     */
    public function marcatori(string $tid, ?string $stageMatch = null, ?string $group = null): array
    {
        $anno = $this->wc->anno($tid);

        $base = DB::table('awc_goals')->where('tournament_id', $tid);
        if ($stageMatch !== null) {
            $base->where('stage_name', $stageMatch);
        }
        if ($group !== null) {
            $base->where('group_name', $group);
        }

        $rows = (clone $base)
            ->where('own_goal', 0)
            ->selectRaw('COUNT(*) AS gol, player_id, player_team_code AS team_code,
                         given_name, family_name')
            ->groupBy('player_id', 'family_name', 'given_name', 'player_team_code')
            ->orderByDesc('gol')
            ->orderBy('family_name')
            ->orderBy('given_name')
            ->get();

        $gruppi = [];
        foreach ($rows as $r) {
            $gruppi[(int) $r->gol][] = [
                'player_id' => $r->player_id,
                'team_code' => $r->team_code,
                'flag'      => $this->wc->bandieraUrl($r->team_code, $anno),
                'nome'      => $this->nomeComposto($r->given_name, (string) $r->family_name),
            ];
        }
        krsort($gruppi);

        $autogol = null;
        $autori = (clone $base)
            ->where('own_goal', 1)
            ->select('player_id', 'player_team_code', 'team_code', 'given_name', 'family_name')
            ->orderBy('family_name')
            ->orderBy('given_name')
            ->get();
        if ($autori->isNotEmpty()) {
            $autogol = [
                'tot'    => $autori->count(),
                'autori' => $autori->map(fn ($r) => [
                    'player_id' => $r->player_id,
                    'team_code' => $r->player_team_code,
                    // bandiera della squadra a cui e' stato segnato... no:
                    // come il vecchio sito, bandiera della squadra del giocatore
                    'flag'      => $this->wc->bandieraUrl($r->player_team_code, $anno),
                    'nome'      => $this->nomeComposto($r->given_name, (string) $r->family_name),
                ])->all(),
            ];
        }

        return ['gruppi' => $gruppi, 'autogol' => $autogol];
    }

    /** "Rossi, P.G." come il CONCAT_WS del vecchio codice. */
    protected function nomeComposto(?string $given, string $family): string
    {
        $given = trim((string) $given);
        if ($given === '') {
            return $family;
        }
        $iniziali = mb_substr($given, 0, 1);
        if (str_contains($given, ' ')) {
            $iniziali .= '.'.mb_substr((string) strrchr($given, ' '), 1, 1);
        }

        return $family.', '.$iniziali.'.';
    }
}
