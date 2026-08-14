<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Calcola i primati mostrati nella tab Record.
 *
 * Tre ambiti, stessa logica:
 *   - una squadra in una singola edizione   perSquadraAnno()
 *   - una squadra in tutta la sua storia    perSquadra()
 *   - un'intera edizione                    perTorneo()
 *
 * Nel torneo il capocannoniere non compare: esiste gia' una tab
 * Marcatori dedicata e la ripetizione non aggiunge nulla.
 *
 * Le eta' sono sempre calcolate al giorno della partita, non a oggi
 * ne' all'inizio del torneo. L'unica eccezione dichiarata e' l'eta'
 * media, che per definizione riguarda i convocati e non gli schierati,
 * quindi viene riferita alla prima partita della squadra nel torneo.
 *
 * Cartellini, rigori e sostituzioni esistono nel database solo dal
 * 1970 in poi. Per le edizioni precedenti i primati relativi restano
 * vuoti, senza essere nascosti: l'assenza del dato e' essa stessa
 * un'informazione.
 */
class RecordService
{
    /** Prima edizione con i dati sui cartellini. */
    public const PRIMO_ANNO_CARTELLINI = 1970;

    /** Quota di presenze sotto la quale un portiere non entra in classifica. */
    public const QUOTA_MINIMA_PORTIERE = 0.60;

    public function perSquadraAnno(string $teamCode, string $tid): array
    {
        return $this->calcola($teamCode, [$tid], true);
    }

    public function perSquadra(string $teamCode): array
    {
        $tornei = DB::table('awc_team_appearances')
            ->where('team_code', $teamCode)
            ->distinct()->pluck('tournament_id')->all();

        return $this->calcola($teamCode, $tornei, true);
    }

    public function perTorneo(string $tid): array
    {
        return $this->calcola(null, [$tid], false);
    }

    /* ------------------------------------------------------------------ */

    /**
     * @param  string|null  $teamCode  null = tutte le squadre del torneo
     * @param  array        $tornei    elenco di tournament_id
     * @param  bool         $conGoleador  false per l'ambito torneo
     */
    protected function calcola(?string $teamCode, array $tornei, bool $conGoleador): array
    {
        if (empty($tornei)) {
            return ['vuoto' => true, 'voci' => []];
        }

        $partite = $this->partite($teamCode, $tornei);
        if ($partite->isEmpty()) {
            return ['vuoto' => true, 'voci' => []];
        }

        $matchIds = $partite->pluck('match_id')->all();
        $nascite  = $this->dateDiNascita();

        $voci = [];

        /* --- eta' degli schierati ------------------------------------- */
        $eta = $this->etaSchierati($matchIds, $teamCode, $nascite);

        $voci[] = $this->voceEta('piu_vecchio', 'Giocatore più vecchio schierato',
            $eta['max'] ?? null, $partite);
        $voci[] = $this->voceEta('piu_giovane', 'Giocatore più giovane schierato',
            $eta['min'] ?? null, $partite);

        /* --- eta' media dei convocati --------------------------------- */
        $voci[] = $this->etaMediaConvocati($teamCode, $tornei, $partite, $nascite);

        /* --- eta' dei marcatori --------------------------------------- */
        $etaGol = $this->etaMarcatori($matchIds, $teamCode, $nascite);

        $voci[] = $this->voceEta('marcatore_vecchio', 'Marcatore più vecchio',
            $etaGol['max'] ?? null, $partite);
        $voci[] = $this->voceEta('marcatore_giovane', 'Marcatore più giovane',
            $etaGol['min'] ?? null, $partite);

        /* --- cartellini ----------------------------------------------- */
        $primoAnno = min(array_map(fn ($t) => (int) substr($t, 3), $tornei));
        $senzaDati = $primoAnno < self::PRIMO_ANNO_CARTELLINI;

        $cart = $this->cartellini($matchIds, $teamCode);
        $voci[] = $this->voceConteggio('ammoniti', 'Ammonizioni', $cart['gialli'],
            $cart['dett_gialli'], $senzaDati);
        $voci[] = $this->voceConteggio('espulsi', 'Espulsioni', $cart['rossi'],
            $cart['dett_rossi'], $senzaDati);

        /* --- rigori e autogol ----------------------------------------- */
        $rig = $this->rigoriEAutogol($matchIds, $teamCode);

        $voci[] = $this->voceConteggio('rigori_segnati', 'Rigori segnati',
            $rig['rigori_pro'], $rig['dett_rigori_pro'], false);
        $voci[] = $this->voceConteggio('rigori_subiti', 'Rigori subiti',
            $rig['rigori_contro'], $rig['dett_rigori_contro'], false);
        $voci[] = $this->voceConteggio('autogol_favore', 'Autogol a favore',
            $rig['autogol_pro'], $rig['dett_autogol_pro'], false);
        $voci[] = $this->voceConteggio('autogol_contro', 'Autogol commessi',
            $rig['autogol_contro'], $rig['dett_autogol_contro'], false);

        /* --- capocannonieri ------------------------------------------- */
        if ($conGoleador) {
            $voci[] = $this->goleador($matchIds, $teamCode);
        }

        /* --- portieri -------------------------------------------------- */
        $voci[] = $this->portieri($matchIds, $teamCode, $partite);

        return [
            'vuoto'      => false,
            'voci'       => array_values(array_filter($voci)),
            'partite'    => $partite->count(),
            'senza_dati' => $senzaDati,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Sorgenti                                                           */
    /* ------------------------------------------------------------------ */

    /** Partite dell'ambito, indicizzate per match_id. */
    protected function partite(?string $teamCode, array $tornei)
    {
        $q = DB::table('awc_matches')
            ->select('match_id', 'match_date', 'match_name', 'stage_name',
                'home_team_code', 'away_team_code', 'home_team_name',
                'away_team_name', 'home_team_score', 'away_team_score',
                'tournament_id')
            ->whereIn('tournament_id', $tornei);

        if ($teamCode !== null) {
            $q->where(fn ($w) => $w->where('home_team_code', $teamCode)
                ->orWhere('away_team_code', $teamCode));
        }

        return $q->orderBy('match_date')->get()->keyBy('match_id');
    }

    protected function dateDiNascita(): array
    {
        return DB::table('awc_players')
            ->whereNotNull('birth_date')
            ->pluck('birth_date', 'player_id')->all();
    }

    /* ------------------------------------------------------------------ */
    /*  Calcoli                                                            */
    /* ------------------------------------------------------------------ */

    /** Estremi di eta' fra chi e' sceso in campo. */
    protected function etaSchierati(array $matchIds, ?string $teamCode, array $nascite): array
    {
        $q = DB::table('awc_player_appearances')
            ->select('player_id', 'family_name', 'given_name', 'match_id',
                'match_date', 'team_code', 'team_name')
            ->whereIn('match_id', $matchIds);

        if ($teamCode !== null) {
            $q->where('team_code', $teamCode);
        }

        return $this->estremiEta($q->get(), $nascite);
    }

    /** Estremi di eta' fra chi ha segnato (autogol esclusi). */
    protected function etaMarcatori(array $matchIds, ?string $teamCode, array $nascite): array
    {
        $q = DB::table('awc_goals')
            ->select('player_id', 'family_name', 'given_name', 'match_id',
                'match_date', 'player_team_code as team_code',
                'player_team_name as team_name')
            ->whereIn('match_id', $matchIds)
            ->where('own_goal', 0);

        if ($teamCode !== null) {
            $q->where('player_team_code', $teamCode);
        }

        return $this->estremiEta($q->get(), $nascite);
    }

    /** Scorre le righe e tiene il piu' vecchio e il piu' giovane. */
    protected function estremiEta($righe, array $nascite): array
    {
        $max = $min = null;

        foreach ($righe as $r) {
            $nascita = $nascite[$r->player_id] ?? null;
            if (! $nascita || ! $r->match_date) {
                continue;
            }
            $giorni = $this->giorniFra($nascita, $r->match_date);
            if ($giorni === null || $giorni <= 0) {
                continue;
            }
            $voce = [
                'player_id' => $r->player_id,
                'nome'      => trim(($r->given_name ?? '') . ' ' . ($r->family_name ?? '')),
                'match_id'  => $r->match_id,
                'data'      => $r->match_date,
                'team_code' => $r->team_code ?? null,
                'team_name' => $r->team_name ?? null,
                'giorni'    => $giorni,
            ];
            if ($max === null || $giorni > $max['giorni']) {
                $max = $voce;
            }
            if ($min === null || $giorni < $min['giorni']) {
                $min = $voce;
            }
        }

        return ['max' => $max, 'min' => $min];
    }

    /**
     * Eta' media dei convocati, riferita alla prima partita della squadra
     * in ciascuna edizione. Con piu' edizioni la media e' complessiva.
     */
    protected function etaMediaConvocati(?string $teamCode, array $tornei, $partite, array $nascite): array
    {
        $q = DB::table('awc_squads')
            ->select('player_id', 'tournament_id', 'team_code')
            ->whereIn('tournament_id', $tornei);

        if ($teamCode !== null) {
            $q->where('team_code', $teamCode);
        }

        // Data di riferimento: prima partita disputata in quell'edizione.
        $riferimento = [];
        foreach ($partite as $p) {
            if (! isset($riferimento[$p->tournament_id])) {
                $riferimento[$p->tournament_id] = $p->match_date;
            }
        }

        $somma = 0;
        $n = 0;
        foreach ($q->get() as $r) {
            $nascita = $nascite[$r->player_id] ?? null;
            $data    = $riferimento[$r->tournament_id] ?? null;
            if (! $nascita || ! $data) {
                continue;
            }
            $giorni = $this->giorniFra($nascita, $data);
            if ($giorni === null || $giorni <= 0) {
                continue;
            }
            $somma += $giorni;
            $n++;
        }

        if ($n === 0) {
            return [
                'chiave'    => 'eta_media',
                'etichetta' => 'Età media dei convocati',
                'valore'    => null,
                'nota'      => 'Dato non disponibile',
            ];
        }

        $media = (int) round($somma / $n);

        return [
            'chiave'    => 'eta_media',
            'etichetta' => 'Età media dei convocati',
            'valore'    => $this->formattaEta($media),
            'nota'      => $n . ' convocati, età alla prima partita',
        ];
    }

    protected function cartellini(array $matchIds, ?string $teamCode): array
    {
        $q = DB::table('awc_bookings')
            ->select('family_name', 'given_name', 'match_id', 'yellow_card',
                'red_card', 'second_yellow_card', 'sending_off')
            ->whereIn('match_id', $matchIds);

        if ($teamCode !== null) {
            $q->where('team_code', $teamCode);
        }

        $righe  = $q->get();
        $gialli = $rossi = 0;
        $dg = $dr = [];

        foreach ($righe as $r) {
            $nome = trim(($r->given_name ?? '') . ' ' . ($r->family_name ?? ''));
            if ($r->yellow_card) {
                $gialli++;
                $dg[] = ['nome' => $nome, 'match_id' => $r->match_id];
            }
            // Un'espulsione e' tale sia per rosso diretto sia per doppia
            // ammonizione: sending_off copre entrambi i casi.
            if ($r->sending_off || $r->red_card || $r->second_yellow_card) {
                $rossi++;
                $dr[] = ['nome' => $nome, 'match_id' => $r->match_id];
            }
        }

        return ['gialli' => $gialli, 'rossi' => $rossi,
            'dett_gialli' => $dg, 'dett_rossi' => $dr];
    }

    protected function rigoriEAutogol(array $matchIds, ?string $teamCode): array
    {
        $righe = DB::table('awc_goals')
            ->select('family_name', 'given_name', 'match_id', 'team_code',
                'player_team_code', 'own_goal', 'penalty')
            ->whereIn('match_id', $matchIds)
            ->where(fn ($w) => $w->where('penalty', 1)->orWhere('own_goal', 1))
            ->get();

        $out = ['rigori_pro' => 0, 'rigori_contro' => 0,
            'autogol_pro' => 0, 'autogol_contro' => 0,
            'dett_rigori_pro' => [], 'dett_rigori_contro' => [],
            'dett_autogol_pro' => [], 'dett_autogol_contro' => []];

        foreach ($righe as $r) {
            $nome = trim(($r->given_name ?? '') . ' ' . ($r->family_name ?? ''));
            $d = ['nome' => $nome, 'match_id' => $r->match_id];

            // team_code indica chi beneficia della rete,
            // player_team_code la squadra di chi l'ha realizzata.
            $aFavore = $teamCode === null || $r->team_code === $teamCode;
            $miei    = $teamCode === null || $r->player_team_code === $teamCode;

            if ($r->penalty) {
                if ($aFavore) {
                    $out['rigori_pro']++;
                    $out['dett_rigori_pro'][] = $d;
                } else {
                    $out['rigori_contro']++;
                    $out['dett_rigori_contro'][] = $d;
                }
            }
            if ($r->own_goal) {
                if ($miei) {
                    $out['autogol_contro']++;
                    $out['dett_autogol_contro'][] = $d;
                } else {
                    $out['autogol_pro']++;
                    $out['dett_autogol_pro'][] = $d;
                }
            }
        }

        return $out;
    }

    /** I tre giocatori con piu' reti (autogol esclusi). */
    protected function goleador(array $matchIds, ?string $teamCode): array
    {
        $q = DB::table('awc_goals')
            ->select('player_id', 'family_name', 'given_name',
                DB::raw('COUNT(*) as reti'))
            ->whereIn('match_id', $matchIds)
            ->where('own_goal', 0)
            ->groupBy('player_id', 'family_name', 'given_name')
            ->orderByDesc('reti')
            ->limit(10);

        if ($teamCode !== null) {
            $q->where('player_team_code', $teamCode);
        }

        $righe = $q->get();
        if ($righe->isEmpty()) {
            return [
                'chiave'    => 'goleador',
                'etichetta' => 'Migliori marcatori',
                'valore'    => null,
                'nota'      => 'Nessuna rete',
            ];
        }

        // Si tengono i primi tre piazzamenti: a parita' di reti entrano
        // tutti, quindi la lista puo' superare i tre nomi.
        $sogliaReti = $righe->pluck('reti')->unique()->take(3)->last();

        $lista = $righe->filter(fn ($r) => $r->reti >= $sogliaReti)
            ->map(fn ($r) => [
                'player_id' => $r->player_id,
                'nome'      => trim(($r->given_name ?? '') . ' ' . ($r->family_name ?? '')),
                'reti'      => (int) $r->reti,
            ])->values()->all();

        return [
            'chiave'    => 'goleador',
            'etichetta' => 'Migliori marcatori',
            'valore'    => $lista[0]['reti'] . ($lista[0]['reti'] == 1 ? ' rete' : ' reti'),
            'classifica' => $lista,
        ];
    }

    /**
     * Portiere con la media di reti subite piu' bassa.
     *
     * Entra in classifica solo chi ha giocato almeno il 60% delle partite
     * dell'ambito. Se nessuno raggiunge quella quota, si mostrano tutti i
     * portieri con la rispettiva media, senza proclamare un migliore.
     */
    protected function portieri(array $matchIds, ?string $teamCode, $partite): array
    {
        $q = DB::table('awc_player_appearances')
            ->select('player_id', 'family_name', 'given_name', 'match_id', 'team_code')
            ->whereIn('match_id', $matchIds)
            ->where('position_code', 'GK');

        if ($teamCode !== null) {
            $q->where('team_code', $teamCode);
        }

        $righe = $q->get();
        if ($righe->isEmpty()) {
            return [
                'chiave'    => 'portiere',
                'etichetta' => 'Miglior portiere',
                'valore'    => null,
                'nota'      => 'Dato non disponibile',
            ];
        }

        // Reti incassate in ogni partita, dal punto di vista della squadra.
        $subiti = [];
        foreach ($partite as $p) {
            if ($teamCode !== null) {
                $subiti[$p->match_id] = $p->home_team_code === $teamCode
                    ? (int) $p->away_team_score
                    : (int) $p->home_team_score;
            }
        }

        $agg = [];
        foreach ($righe as $r) {
            $k = $r->player_id;
            if (! isset($agg[$k])) {
                $agg[$k] = [
                    'player_id' => $r->player_id,
                    'nome'      => trim(($r->given_name ?? '') . ' ' . ($r->family_name ?? '')),
                    'partite'   => 0,
                    'subiti'    => 0,
                ];
            }
            $agg[$k]['partite']++;

            if ($teamCode !== null) {
                $agg[$k]['subiti'] += $subiti[$r->match_id] ?? 0;
            } else {
                // Ambito torneo: si guarda la squadra del portiere.
                $p = $partite[$r->match_id] ?? null;
                if ($p) {
                    $agg[$k]['subiti'] += $p->home_team_code === $r->team_code
                        ? (int) $p->away_team_score
                        : (int) $p->home_team_score;
                }
            }
        }

        $totPartite = $partite->count();
        $soglia     = max(1, (int) ceil($totPartite * self::QUOTA_MINIMA_PORTIERE));

        foreach ($agg as &$a) {
            $a['media'] = $a['partite'] > 0
                ? round($a['subiti'] / $a['partite'], 2)
                : null;
        }
        unset($a);

        $ammessi = array_filter($agg, fn ($a) => $a['partite'] >= $soglia);

        if (! empty($ammessi)) {
            usort($ammessi, fn ($x, $y) => $x['media'] <=> $y['media']);
            $primo = $ammessi[0];

            return [
                'chiave'     => 'portiere',
                'etichetta'  => 'Miglior portiere',
                'valore'     => number_format($primo['media'], 2, ',', '') . ' reti a partita',
                'nome'       => $primo['nome'],
                'player_id'  => $primo['player_id'],
                'nota'       => $primo['partite'] . ' partite su ' . $totPartite,
                'classifica' => array_slice($ammessi, 0, 5),
            ];
        }

        // Nessuno raggiunge la quota: si elencano tutti.
        $tutti = array_values($agg);
        usort($tutti, fn ($x, $y) => $x['media'] <=> $y['media']);

        return [
            'chiave'     => 'portiere',
            'etichetta'  => 'Portieri impiegati',
            'valore'     => null,
            'nota'       => 'Nessun portiere ha giocato almeno il '
                            . (int) (self::QUOTA_MINIMA_PORTIERE * 100)
                            . '% delle partite: media di ciascuno',
            'classifica' => $tutti,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Aiutanti                                                           */
    /* ------------------------------------------------------------------ */

    protected function giorniFra(?string $nascita, ?string $data): ?int
    {
        if (! $nascita || ! $data) {
            return null;
        }
        try {
            $a = new \DateTimeImmutable(substr($nascita, 0, 10));
            $b = new \DateTimeImmutable(substr($data, 0, 10));
        } catch (\Exception $e) {
            return null;
        }

        return (int) $a->diff($b)->days * ($b < $a ? -1 : 1);
    }

    /**
     * Da giorni ad "anni e giorni", come si usa per i primati sportivi.
     *
     * La conversione passa da una data reale e non da una divisione per
     * 365: con gli anni bisestili la scorciatoia sfalsa il conteggio di
     * una decina di giorni, e su un primato e' una differenza che si
     * nota. Zoff nella finale del 1982 aveva 40 anni e 133 giorni, non
     * 143.
     */
    protected function formattaEta(int $giorni): string
    {
        // Si ricostruisce una nascita fittizia a ritroso da oggi, cosi'
        // il conteggio degli anni tiene conto dei bisestili attraversati.
        $fine   = new \DateTimeImmutable('2000-01-01');
        $inizio = $fine->modify('-' . $giorni . ' days');
        $d      = $inizio->diff($fine);

        $anni = $d->y;
        $dopoCompleanno = $inizio->modify('+' . $anni . ' years');
        $residuo = (int) $dopoCompleanno->diff($fine)->days;

        return $anni . ' anni e ' . $residuo . ' giorni';
    }

    protected function voceEta(string $chiave, string $etichetta, ?array $dato, $partite): array
    {
        if (! $dato) {
            return [
                'chiave'    => $chiave,
                'etichetta' => $etichetta,
                'valore'    => null,
                'nota'      => 'Dato non disponibile',
            ];
        }

        $p = $partite[$dato['match_id']] ?? null;

        return [
            'chiave'    => $chiave,
            'etichetta' => $etichetta,
            'valore'    => $this->formattaEta($dato['giorni']),
            'nome'      => $dato['nome'],
            'player_id' => $dato['player_id'],
            'team_name' => $dato['team_name'] ?? null,
            'match_id'  => $dato['match_id'],
            'partita'   => $p ? $this->descriviPartita($p) : null,
            'data'      => $dato['data'],
        ];
    }

    protected function voceConteggio(string $chiave, string $etichetta, int $n, array $dettaglio, bool $senzaDati): array
    {
        $voce = [
            'chiave'    => $chiave,
            'etichetta' => $etichetta,
            'valore'    => $n,
            'dettaglio' => array_slice($dettaglio, 0, 12),
            'altri'     => max(0, count($dettaglio) - 12),
        ];

        if ($n === 0 && $senzaDati) {
            $voce['nota'] = 'Non registrato prima del ' . self::PRIMO_ANNO_CARTELLINI;
        }

        return $voce;
    }

    protected function descriviPartita($p): string
    {
        return trim(($p->home_team_name ?? '') . ' ' . (int) $p->home_team_score
            . '-' . (int) $p->away_team_score . ' ' . ($p->away_team_name ?? ''));
    }
}
