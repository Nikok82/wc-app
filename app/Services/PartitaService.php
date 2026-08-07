<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dati della scheda partita (/partita/{matchId}): testata fissa
 * (risultato + marcatori) e le quattro tab Info / Formazioni / Eventi /
 * Situazione.
 *
 * NB dati (verificato sul dump reale):
 * - awc_bookings e awc_substitutions partono dal 1970 (prima i cambi non
 *   esistevano e i cartellini non erano ancora stati introdotti): per i
 *   tornei 1930-1966 la tab Eventi mostra i soli gol, ed e' corretto cosi'.
 * - awc_player_appearances contiene chi ha GIOCATO (11 titolari con
 *   starter=1 + subentrati con substitute=1); il resto della rosa arriva
 *   da awc_squads.
 * - shirt_number e' 0 per 1930-1950: per quei tornei si assegnano gli
 *   1-11 d'ufficio (1 al portiere, poi difensori, centrocampisti,
 *   attaccanti nell'ordine dell'elenco), come da scelta di Niko.
 * - Punti dei gironi: 2 a vittoria fino al 1990 compreso, 3 dal 1994.
 */
class PartitaService
{
    /** position_code granulari -> reparto (P/D/C/A). */
    protected const REPARTO = [
        'GK' => 'P',
        'DF' => 'D', 'CB' => 'D', 'RB' => 'D', 'LB' => 'D', 'SW' => 'D',
        'RWB' => 'D', 'LWB' => 'D',
        'MF' => 'C', 'DM' => 'C', 'CM' => 'C', 'AM' => 'C', 'RM' => 'C', 'LM' => 'C',
        'FW' => 'A', 'CF' => 'A', 'SS' => 'A', 'RW' => 'A', 'LW' => 'A',
        'RF' => 'A', 'LF' => 'A',
    ];

    /** Ordine dei periodi di gioco per la cronologia. */
    protected const PERIODI = [
        'primo tempo'                             => 1,
        'primo tempo, stoppage time'              => 1,
        'secondo tempo'                           => 2,
        'secondo tempo, stoppage time'            => 2,
        'supplementari, primo tempo'              => 3,
        'supplementari, primo tempo, stoppage t'  => 3,
        'supplementari, secondo tempo'            => 4,
        'supplementari, secondo tempo, stoppage'  => 4,
    ];

    public function __construct(
        protected WcService $wc,
        protected TorneoPartiteService $tps,
    ) {
    }

    /* ------------------------------------------------------------------ */
    /*  Riga base                                                          */
    /* ------------------------------------------------------------------ */

    public function partita(string $matchId): ?object
    {
        return DB::table('awc_matches')->where('match_id', $matchId)->first();
    }

    /** true se la partita non e' mai stata giocata (walkover SWE-AUT 1938). */
    public function nonGiocata(object $m): bool
    {
        return ! DB::table('awc_player_appearances')
            ->where('match_id', $m->match_id)->exists()
            && ! DB::table('awc_goals')->where('match_id', $m->match_id)->exists()
            && $m->match_id === 'M-1938-19';
    }

    /* ------------------------------------------------------------------ */
    /*  Testata (fissa sopra le tab)                                       */
    /* ------------------------------------------------------------------ */

    public function testata(object $m): array
    {
        $anno = $this->wc->anno($m->tournament_id);

        $gol = DB::table('awc_goals')
            ->where('match_id', $m->match_id)
            ->orderBy('minute_regulation')->orderBy('minute_stoppage')->orderBy('key_id')
            ->get();

        $marcatori = ['home' => [], 'away' => []];
        foreach ($gol as $g) {
            // Il gol e' accreditato a team_code (per gli autogol e' la squadra
            // che ne beneficia, come nei tabellini ufficiali).
            $side = $g->team_code === $m->home_team_code ? 'home' : 'away';
            $marcatori[$side][] = [
                'minuto'    => $g->minute_label,
                'player_id' => $g->player_id,
                'nome'      => $this->nomeBreve($g->given_name, $g->family_name),
                'autogol'   => (bool) $g->own_goal,
                'rigore'    => (bool) $g->penalty,
            ];
        }

        $stage = ucfirst((string) $m->stage_name);
        if (! empty($m->group_name) && $m->group_name !== 'sconosciuto/a') {
            $stage .= ' · '.$m->group_name;
        }

        return [
            'anno'       => $anno,
            'tid'        => $m->tournament_id,
            'stage'      => $stage,
            'home'       => $this->squadraTestata($m->home_team_code, $m->home_team_name, $anno),
            'away'       => $this->squadraTestata($m->away_team_code, $m->away_team_name, $anno),
            'score'      => trim((string) $m->score) !== '' ? $m->score : '–',
            'dts'        => (bool) $m->extra_time,
            'dcr'        => (bool) $m->penalty_shootout,
            'ris_rigori' => $m->penalty_shootout
                ? $m->home_team_score_penalties.'-'.$m->away_team_score_penalties : null,
            'replay'     => (int) ($m->replay ?? 0) === 1,
            'replayed'   => (int) ($m->replayed ?? 0) === 1,
            'marcatori'  => $marcatori,
        ];
    }

    protected function squadraTestata(?string $code, ?string $name, ?int $anno): array
    {
        return [
            'code' => $code,
            'name' => $name ?: 'da definire',
            'flag' => $this->wc->bandieraUrl($code, $anno),
            // Link alla scheda squadra-anno (es. /squadra/ITA-1990)
            'url'  => $code && $anno ? route('squadra_anno.show', ['code' => $code, 'year' => $anno]) : null,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Tab 1 — Info                                                       */
    /* ------------------------------------------------------------------ */

    public function info(object $m): array
    {
        $anno = $this->wc->anno($m->tournament_id);

        $arbitro = DB::table('awc_referee_appearances')
            ->where('match_id', $m->match_id)
            ->first();

        $stadio = $m->stadium_id
            ? DB::table('awc_stadiums')->where('stadium_id', $m->stadium_id)->first()
            : null;

        return [
            'data'    => $m->match_date ? date('d/m/Y', strtotime($m->match_date)) : null,
            'ora'     => $m->match_time ? substr($m->match_time, 0, 5) : null,
            'stadio'  => $stadio ? [
                'id'    => $stadio->stadium_id,
                'nome'  => $stadio->stadium_name,
                'citta' => $stadio->city_name,
            ] : ($m->stadium_name ? ['id' => null, 'nome' => $m->stadium_name, 'citta' => $m->city_name] : null),
            'arbitro' => $arbitro ? [
                'id'    => $arbitro->referee_id,
                'nome'  => trim($arbitro->given_name.' '.$arbitro->family_name),
                'paese' => $arbitro->country_name,
            ] : null,
            'maglie'  => [
                'home' => $this->kitUrl($m->home_kit, $anno),
                'away' => $this->kitUrl($m->away_kit, $anno),
            ],
        ];
    }

    protected function kitUrl(?string $file, ?int $anno): ?string
    {
        $file = trim((string) $file);
        if ($file === '' || ! $anno) {
            return null;
        }

        return route('img.kit', ['anno' => $anno, 'file' => $file]);
    }

    /* ------------------------------------------------------------------ */
    /*  Tab 2 — Formazioni                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * @return array{home: array, away: array} per lato:
     *   team (code/name/flag/url), manager, campo (righe P/D/C/A di pallini),
     *   elenco (titolari + manager + subentrati + resto rosa)
     */
    public function formazioni(object $m): array
    {
        $anno = $this->wc->anno($m->tournament_id);

        $apps = DB::table('awc_player_appearances')
            ->where('match_id', $m->match_id)
            ->orderBy('key_id')
            ->get()
            ->groupBy('team_code');

        $eventi = $this->eventiGiocatore($m);

        $out = [];
        foreach (['home', 'away'] as $side) {
            $code = $side === 'home' ? $m->home_team_code : $m->away_team_code;
            $name = $side === 'home' ? $m->home_team_name : $m->away_team_name;

            $squadApps = collect($apps->get($code, collect()));
            $titolari = $squadApps->where('starter', 1)->values();
            $subentrati = $squadApps->where('substitute', 1)->values();

            // Numeri d'ufficio 1-11 per i tornei senza numeri nel DB (1930-1950)
            $numeriUfficio = $this->numeriUfficio($titolari);

            $pallino = function ($a, array $flags = []) use ($anno, $eventi, $numeriUfficio) {
                return $this->pallino($a, $anno, $eventi, $numeriUfficio, $flags);
            };

            // Campo: 4 righe per reparto (P al centro della sua riga)
            $campo = ['P' => [], 'D' => [], 'C' => [], 'A' => []];
            foreach ($titolari as $a) {
                $campo[$this->reparto($a)][] = $pallino($a);
            }

            // Manager della partita
            $mgr = DB::table('awc_manager_appearances')
                ->where('match_id', $m->match_id)
                ->where('team_code', $code)
                ->first();

            // Resto della rosa (awc_squads) senza chi ha giocato
            $giocati = $squadApps->pluck('player_id')->all();
            $ordina = fn ($r) => sprintf('%03d|%s', (int) $r->shirt_number ?: 999, $r->family_name);
            $panchina = DB::table('awc_squads')
                ->where('tournament_id', $m->tournament_id)
                ->where('team_code', $code)
                ->whereNotIn('player_id', $giocati)
                ->get()
                ->sortBy($ordina)
                ->values();

            // Ordine dei subentrati: minuto d'ingresso
            $subentrati = $subentrati->sortBy(function ($a) use ($eventi) {
                return $eventi[$a->player_id]['in_ord'] ?? 999;
            })->values();

            $out[$side] = [
                'team'       => $this->squadraTestata($code, $name, $anno),
                'manager'    => $mgr ? [
                    'id'    => $mgr->manager_id,
                    'nome'  => trim($mgr->given_name.' '.$mgr->family_name),
                    'flag'  => $this->wc->bandieraUrl($mgr->country_name, $anno),
                ] : null,
                'campo'      => $campo,
                // Nell'elenco i titolari sono in ordine di numero di maglia
                'titolari'   => $titolari->map(fn ($a) => $pallino($a))
                    ->sortBy(fn ($p) => $p['numero'] ?? 99)->values()->all(),
                'subentrati' => $subentrati->map(fn ($a) => $pallino($a))->all(),
                'panchina'   => $panchina->map(fn ($r) => $pallino($r, ['panchina' => true]))->all(),
            ];
        }

        return $out;
    }

    protected function reparto(object $a): string
    {
        $code = strtoupper(trim((string) ($a->position_code ?? '')));

        return self::REPARTO[$code] ?? match (mb_strtolower(trim((string) ($a->position_name ?? '')))) {
            'portiere'       => 'P',
            'difensore'      => 'D',
            'centrocampista' => 'C',
            'attaccante'     => 'A',
            default          => 'C',
        };
    }

    /**
     * Numeri 1-11 d'ufficio quando il DB non li ha (1930-1950): 1 al
     * portiere, poi difensori, centrocampisti e attaccanti nell'ordine
     * dell'elenco. Ritorna player_id => numero (o [] se i numeri veri esistono).
     */
    protected function numeriUfficio(Collection $titolari): array
    {
        if ($titolari->isEmpty() || $titolari->contains(fn ($a) => (int) $a->shirt_number > 0)) {
            return [];
        }

        $numero = 1;
        $out = [];
        foreach (['P', 'D', 'C', 'A'] as $rep) {
            foreach ($titolari as $a) {
                if ($this->reparto($a) === $rep) {
                    $out[$a->player_id] = $numero++;
                }
            }
        }

        return $out;
    }

    /**
     * Eventi per giocatore della partita: gol segnati, cartellini,
     * entrata/uscita (per le icone dei pallini).
     * @return array<string, array> player_id => dati
     */
    protected function eventiGiocatore(object $m): array
    {
        $out = [];

        foreach (DB::table('awc_goals')->where('match_id', $m->match_id)->get() as $g) {
            if ((int) $g->own_goal === 1) {
                continue;   // l'autogol non "conta" come gol del giocatore sul pallino
            }
            $out[$g->player_id]['gol'] = ($out[$g->player_id]['gol'] ?? 0) + 1;
        }

        foreach (DB::table('awc_bookings')->where('match_id', $m->match_id)->get() as $b) {
            $rosso = ((int) $b->red_card + (int) $b->second_yellow_card + (int) $b->sending_off) > 0;
            $attuale = $out[$b->player_id]['card'] ?? null;
            $out[$b->player_id]['card'] = $rosso ? 'rosso' : ($attuale ?: 'giallo');
        }

        $ord = 0;
        foreach (DB::table('awc_substitutions')->where('match_id', $m->match_id)
            ->orderBy('key_id')->get() as $s) {
            if ((int) $s->going_off === 1) {
                $out[$s->player_id]['out'] = $s->minute_label;
            }
            if ((int) $s->coming_on === 1) {
                $out[$s->player_id]['in'] = $s->minute_label;
                $out[$s->player_id]['in_ord'] = $ord++;
            }
        }

        return $out;
    }

    /** Dati di un singolo pallino giocatore (per campo ed elenco). */
    protected function pallino(object $r, ?int $anno, array $eventi, array $numeriUfficio, array $flags = []): array
    {
        $ev = $eventi[$r->player_id] ?? [];
        $numero = (int) ($r->shirt_number ?? 0) ?: ($numeriUfficio[$r->player_id] ?? null);

        return [
            'player_id' => $r->player_id,
            'nome'      => trim(trim((string) $r->given_name).' '.$r->family_name),
            'numero'    => $numero,
            'flag'      => $this->wc->bandieraUrl($r->team_code, $anno),
            'gol'       => $ev['gol'] ?? 0,
            'card'      => $ev['card'] ?? null,
            'entrato'   => isset($ev['in']) ? $ev['in'] : null,
            'uscito'    => isset($ev['out']) ? $ev['out'] : null,
            'panchina'  => ! empty($flags['panchina']),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Tab 3 — Eventi                                                     */
    /* ------------------------------------------------------------------ */

    /**
     * Cronologia della partita: gol, cartellini e sostituzioni ordinati
     * per periodo e minuto, con separatori di periodo gia' inseriti.
     *
     * @return array<int, array> righe: ['sep' => label] oppure
     *   ['side' => home|away, 'minuto' => label, 'tipo' => ..., ...]
     */
    public function eventi(object $m): array
    {
        $righe = [];

        foreach (DB::table('awc_goals')->where('match_id', $m->match_id)->get() as $g) {
            $righe[] = [
                'side'    => $g->team_code === $m->home_team_code ? 'home' : 'away',
                'tipo'    => 'gol',
                'minuto'  => $g->minute_label,
                'ord'     => $this->ordEvento($g, 0),
                'player'  => ['id' => $g->player_id, 'nome' => $this->nomeBreve($g->given_name, $g->family_name)],
                'autogol' => (bool) $g->own_goal,
                'rigore'  => (bool) $g->penalty,
            ];
        }

        foreach (DB::table('awc_bookings')->where('match_id', $m->match_id)->get() as $b) {
            $rosso = ((int) $b->red_card + (int) $b->sending_off) > 0;
            $doppia = (int) $b->second_yellow_card === 1;
            $righe[] = [
                'side'   => $b->team_code === $m->home_team_code ? 'home' : 'away',
                'tipo'   => ($rosso || $doppia) ? 'rosso' : 'giallo',
                'doppia' => $doppia,
                'minuto' => $b->minute_label,
                'ord'    => $this->ordEvento($b, 1),
                'player' => ['id' => $b->player_id, 'nome' => $this->nomeBreve($b->given_name, $b->family_name)],
            ];
        }

        // Sostituzioni: coppie out/in della stessa squadra allo stesso minuto
        $subs = DB::table('awc_substitutions')->where('match_id', $m->match_id)
            ->orderBy('key_id')->get();
        $inAttesa = [];   // team|minuto => riga out
        foreach ($subs as $s) {
            $key = $s->team_code.'|'.$s->minute_label;
            $player = ['id' => $s->player_id, 'nome' => $this->nomeBreve($s->given_name, $s->family_name)];
            if ((int) $s->going_off === 1) {
                $inAttesa[$key][] = [
                    'side'   => $s->team_code === $m->home_team_code ? 'home' : 'away',
                    'tipo'   => 'sub',
                    'minuto' => $s->minute_label,
                    'ord'    => $this->ordEvento($s, 2),
                    'out'    => $player,
                    'in'     => null,
                ];
            } elseif ((int) $s->coming_on === 1) {
                // accoppia col primo out della stessa squadra/minuto ancora senza in
                $trovata = false;
                if (! empty($inAttesa[$key])) {
                    foreach ($inAttesa[$key] as $i => $riga) {
                        if ($riga['in'] === null) {
                            $inAttesa[$key][$i]['in'] = $player;
                            $trovata = true;
                            break;
                        }
                    }
                }
                if (! $trovata) {
                    $inAttesa[$key][] = [
                        'side'   => $s->team_code === $m->home_team_code ? 'home' : 'away',
                        'tipo'   => 'sub',
                        'minuto' => $s->minute_label,
                        'ord'    => $this->ordEvento($s, 2),
                        'out'    => null,
                        'in'     => $player,
                    ];
                }
            }
        }
        foreach ($inAttesa as $gruppo) {
            foreach ($gruppo as $riga) {
                $righe[] = $riga;
            }
        }

        usort($righe, fn ($a, $b) => $a['ord'] <=> $b['ord']);

        // Separatori di periodo (Secondo tempo / Tempi supplementari)
        $con = [];
        $periodo = 1;
        foreach ($righe as $r) {
            $p = $r['ord'][0];
            if ($p >= 2 && $periodo < 2) {
                $con[] = ['sep' => 'Secondo tempo'];
                $periodo = 2;
            }
            if ($p >= 3 && $periodo < 3) {
                $con[] = ['sep' => 'Tempi supplementari'];
                $periodo = 3;
            }
            $con[] = $r;
        }

        return $con;
    }

    /** Chiave di ordinamento [periodo, minuto, recupero, priorita', key_id]. */
    protected function ordEvento(object $r, int $prio): array
    {
        $periodo = self::PERIODI[trim((string) $r->match_period)] ?? $this->periodoDaMinuto((int) $r->minute_regulation);

        return [$periodo, (int) $r->minute_regulation, (int) $r->minute_stoppage, $prio, (int) $r->key_id];
    }

    protected function periodoDaMinuto(int $min): int
    {
        return $min <= 45 ? 1 : ($min <= 90 ? 2 : ($min <= 105 ? 3 : 4));
    }

    /* ------------------------------------------------------------------ */
    /*  Tab 4 — Situazione                                                 */
    /* ------------------------------------------------------------------ */

    public function situazione(object $m): array
    {
        // Partita di girone (comprese le seconde fasi, il first round e il
        // girone finale 1950): classifica prima/dopo
        if ((int) $m->group_stage === 1 && ! empty($m->group_name)) {
            return [
                'tipo'   => 'girone',
                'girone' => $m->group_name,
                'stage'  => ucfirst((string) $m->stage_name),
                'prima'  => $this->classificaGirone($m, false),
                'dopo'   => $this->classificaGirone($m, true),
            ];
        }

        // Finale / finale 3° posto: podio + le due finali
        if (in_array($m->stage_name, ['finale', 'finale per il terzo posto'], true)) {
            return [
                'tipo'    => 'finale',
                'podio'   => $this->podio($m),
                'partite' => $this->partiteTurno($m, ['finale per il terzo posto', 'finale']),
            ];
        }

        // Turno a eliminazione: tutte le partite dello stesso turno
        return [
            'tipo'    => 'ko',
            'stage'   => ucfirst((string) $m->stage_name),
            'partite' => $this->partiteTurno($m, [$m->stage_name]),
        ];
    }

    /**
     * Classifica del girone calcolata dalle partite giocate prima della
     * partita corrente ($inclusa = false) o fino alla partita corrente
     * compresa ($inclusa = true). Punti: 2 a vittoria fino al 1990, 3 dal
     * 1994. A pari punti: differenza reti, gol fatti, nome (semplificazione
     * rispetto ai criteri storici dei singoli tornei).
     */
    protected function classificaGirone(object $m, bool $inclusa): array
    {
        $anno = (int) $this->wc->anno($m->tournament_id);
        $ptVittoria = $anno >= 1994 ? 3 : 2;

        $partite = DB::table('awc_matches')
            ->where('tournament_id', $m->tournament_id)
            ->where('stage_name', $m->stage_name)
            ->where('group_name', $m->group_name)
            ->orderBy('match_date')->orderBy('match_time')->orderBy('key_id')
            ->get();

        // Squadre del girone (tutte, anche a inizio girone)
        $righe = [];
        foreach ($partite as $p) {
            foreach ([[$p->home_team_code, $p->home_team_name], [$p->away_team_code, $p->away_team_name]] as [$code, $name]) {
                if ($code && ! isset($righe[$code])) {
                    $righe[$code] = [
                        'code' => $code, 'name' => $name,
                        'flag' => $this->wc->bandieraUrl($code, $anno),
                        'url'  => route('squadra_anno.show', ['code' => $code, 'year' => $anno]),
                        'pg' => 0, 'v' => 0, 'n' => 0, 'p' => 0, 'gf' => 0, 'gs' => 0, 'pt' => 0,
                    ];
                }
            }
        }

        foreach ($partite as $p) {
            // Confronto per data+ora soltanto: le partite in contemporanea
            // (ultima giornata dei gironi) contano entrambe nel "dopo" e
            // nessuna delle due nel "prima".
            $prima = [$p->match_date, (string) $p->match_time]
                 <=> [$m->match_date, (string) $m->match_time];
            $conta = $inclusa ? $prima <= 0 : $prima < 0;
            if (! $conta || trim((string) $p->score) === '' || $p->home_team_score === null) {
                continue;
            }

            $h = &$righe[$p->home_team_code];
            $a = &$righe[$p->away_team_code];
            $h['pg']++; $a['pg']++;
            $h['gf'] += $p->home_team_score; $h['gs'] += $p->away_team_score;
            $a['gf'] += $p->away_team_score; $a['gs'] += $p->home_team_score;
            if ($p->home_team_score > $p->away_team_score) {
                $h['v']++; $a['p']++; $h['pt'] += $ptVittoria;
            } elseif ($p->home_team_score < $p->away_team_score) {
                $a['v']++; $h['p']++; $a['pt'] += $ptVittoria;
            } else {
                $h['n']++; $a['n']++; $h['pt']++; $a['pt']++;
            }
            unset($h, $a);
        }

        $righe = array_values($righe);
        usort($righe, function ($x, $y) {
            return [$y['pt'], $y['gf'] - $y['gs'], $y['gf'], $x['name']]
               <=> [$x['pt'], $x['gf'] - $x['gs'], $x['gf'], $y['name']];
        });

        return $righe;
    }

    /** Podio del mondiale da awc_tournament_standings (posizioni 1-3). */
    protected function podio(object $m): array
    {
        $anno = $this->wc->anno($m->tournament_id);

        return DB::table('awc_tournament_standings')
            ->where('tournament_id', $m->tournament_id)
            ->whereIn('position', [1, 2, 3])
            ->orderBy('position')
            ->get()
            ->map(fn ($r) => [
                'position' => (int) $r->position,
                'code'     => $r->team_code,
                'name'     => $r->team_name,
                'flag'     => $this->wc->bandieraUrl($r->team_code, $anno),
                'url'      => route('squadra_anno.show', ['code' => $r->team_code, 'year' => $anno]),
            ])->all();
    }

    /** Le partite di uno o piu' stage (per la tab Situazione dei turni KO). */
    protected function partiteTurno(object $m, array $stages): array
    {
        $out = [];
        foreach ($stages as $stage) {
            $matches = DB::table('awc_matches')
                ->where('tournament_id', $m->tournament_id)
                ->where('stage_name', $stage)
                ->orderBy('position')->orderBy('key_id')
                ->get();

            foreach ($matches as $p) {
                $riga = $this->tps->rigaPartita($p, $matches);
                $riga['label_stage'] = TorneoPartiteService::KO_STAGES[$stage] ?? ucfirst($stage);
                $riga['corrente'] = $p->match_id === $m->match_id;
                $out[] = $riga;
            }
        }

        return $out;
    }

    /* ------------------------------------------------------------------ */

    /** "F. Baresi" (iniziale puntata + cognome). */
    protected function nomeBreve(?string $given, ?string $family): string
    {
        return trim(mb_substr((string) $given, 0, 1).'. '.$family, '. ');
    }
}
