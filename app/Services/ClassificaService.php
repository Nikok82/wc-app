<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Classifiche della sezione Torneo (tab "Classifica") e classifica perpetua
 * globale (/classifica). Fonte unica: awc_results_for_year (compilata da
 * Niko), join con awc_teams per codice/nome squadra.
 *
 * - "Torneo": le squadre del singolo Mondiale ordinate per class_mond;
 *   la colonna Note mostra result_mond.
 * - "Perpetua": somma (SUM in query) delle righe di tutti i Mondiali fino
 *   all'anno richiesto incluso; ordine pt3 desc, poi differenza reti,
 *   gol fatti, nome. Le colonne perp_* della tabella NON vengono usate:
 *   la somma e' sempre coerente coi dati per-torneo (decisione 02/08).
 *   La colonna Note mostra le medaglie (class_mond 1/2/3 cumulate).
 */
class ClassificaService
{
    /**
     * Nazioni che nel tempo hanno cambiato status: nelle classifiche
     * perpetue compare una riga aggiuntiva "con asterisco" che cumula i
     * risultati dei componenti (le righe singole restano SEMPRE).
     *
     * - 'ids' in ordine cronologico; la riga unita compare solo quando
     *   ALMENO DUE componenti hanno gia' giocato un Mondiale (mai
     *   duplicati pre-debutto): Germania* dal 1954, Russia* dal 1994,
     *   Jugoslavia* e Cecoslovacchia* dal 2006, RD Congo* dal 2026.
     * - 'globale'  = nome/codice usati nella /classifica (nomi moderni);
     * - 'parziale' = nome/codice nelle perpetue dei tornei (nomi storici).
     *   Bandiera e link seguono il codice indicato (bandiera d'epoca via
     *   awc_flags col tournament_id; per gli anni scoperti il fallback di
     *   WcService sceglie la piu' recente, es. Germania* 1954-1990 ->
     *   GER.png, Jugoslavia* dal 2006 -> YUG-1.png).
     * NB dati: T-093/CON (RD Congo) non ha righe in awc_flags -> la riga
     * (singola e unita) resta senza bandiera finche' non viene aggiunta.
     */
    public const GRUPPI_UNITI = [
        [
            'ids'      => ['T-031', 'T-086'],           // Germania, Germania Ovest
            'globale'  => ['nome' => 'Germania*', 'code' => 'GER'],
            'parziale' => ['nome' => 'Germania*', 'code' => 'GER'],
        ],
        [
            'ids'      => ['T-072', 'T-062'],           // URSS, Russia
            'globale'  => ['nome' => 'Russia*', 'code' => 'RUS'],
            'parziale' => ['nome' => 'Russia*', 'code' => 'RUS'],
        ],
        [
            'ids'      => ['T-087', 'T-067', 'T-066'],  // Jugoslavia, Serbia e Montenegro, Serbia
            'globale'  => ['nome' => 'Serbia*', 'code' => 'SRB'],
            'parziale' => ['nome' => 'Jugoslavia*', 'code' => 'YUG'],
        ],
        [
            'ids'      => ['T-021', 'T-020'],           // Cecoslovacchia, Cechia
            'globale'  => ['nome' => 'Cechia*', 'code' => 'CZE'],
            'parziale' => ['nome' => 'Cecoslovacchia*', 'code' => 'CZK'],
        ],
        [
            'ids'      => ['T-088', 'T-093'],           // Zaire, RD del Congo
            'globale'  => ['nome' => 'Repubblica Democratica del Congo*', 'code' => 'CON'],
            'parziale' => ['nome' => 'Repubblica Democratica del Congo*', 'code' => 'CON'],
        ],
    ];

    public function __construct(protected WcService $wc)
    {
    }

    /** Classifica del singolo torneo, ordinata per class_mond. */
    public function torneo(string $tournamentId): array
    {
        $anno = $this->wc->anno($tournamentId);

        $righe = DB::table('awc_results_for_year as r')
            ->join('awc_teams as t', 't.team_id', '=', 'r.team_id')
            ->where('r.tournament_id', $tournamentId)
            ->orderByRaw('r.class_mond IS NULL, r.class_mond')
            ->orderByDesc('r.pt3')
            ->get([
                'r.class_mond', 'r.result_mond', 'r.partite_giocate',
                'r.vittorie', 'r.pareggi', 'r.sconfitte',
                'r.gol_fatti', 'r.gol_subiti', 'r.pt3', 'r.pt2',
                't.team_code', 't.team_name',
            ]);

        return $righe->map(function ($r) use ($tournamentId, $anno) {
            return [
                'pos'       => $r->class_mond,
                'team_code' => $r->team_code,
                'team_name' => $r->team_name,
                'flag'      => $this->wc->bandieraUrl($r->team_code, $tournamentId),
                // In "Torneo" il nome linka la scheda squadra-anno (es. ITA-1990)
                'url'       => ($r->team_code && $anno)
                    ? route('squadra_anno.show', ['code' => $r->team_code, 'year' => $anno])
                    : null,
                'pg'  => (int) $r->partite_giocate,
                'v'   => (int) $r->vittorie,
                'n'   => (int) $r->pareggi,
                'p'   => (int) $r->sconfitte,
                'gf'  => (int) $r->gol_fatti,
                'gs'  => (int) $r->gol_subiti,
                'dr'  => (int) $r->gol_fatti - (int) $r->gol_subiti,
                'pt3' => (int) ($r->pt3 ?? 0),
                'pt2' => (int) ($r->pt2 ?? 0),
                'nota'     => $r->result_mond,
                'medaglie' => null,
            ];
        })->all();
    }

    /**
     * Classifica perpetua "cristallizzata": somma di tutti i Mondiali con
     * anno <= $annoMax (null = tutti i tornei, per la /classifica globale).
     * $flagTid: torneo di riferimento per la bandiera d'epoca (null = la
     * bandiera piu' recente).
     */
    public function perpetua(?int $annoMax = null, int|string|null $flagTid = null): array
    {
        $q = DB::table('awc_results_for_year as r')
            ->join('awc_teams as t', 't.team_id', '=', 'r.team_id');

        if ($annoMax !== null) {
            // tournament_id = "WC-1990": l'anno e' la sottostringa dal 4° carattere
            $q->whereRaw('CAST(SUBSTRING(r.tournament_id, 4) AS UNSIGNED) <= ?', [$annoMax]);
        }

        $righe = $q->selectRaw('
                r.team_id, t.team_code, t.team_name,
                SUM(r.partite_giocate)                     AS pg,
                SUM(r.vittorie)                            AS v,
                SUM(r.pareggi)                             AS n,
                SUM(r.sconfitte)                           AS p,
                SUM(r.gol_fatti)                           AS gf,
                SUM(r.gol_subiti)                          AS gs,
                SUM(r.gol_fatti) - SUM(r.gol_subiti)       AS dr,
                COALESCE(SUM(r.pt3), 0)                    AS pt3,
                COALESCE(SUM(r.pt2), 0)                    AS pt2,
                SUM(r.class_mond = 1)                      AS ori,
                SUM(r.class_mond = 2)                      AS argenti,
                SUM(r.class_mond = 3)                      AS bronzi')
            ->groupBy('r.team_id', 't.team_code', 't.team_name')
            ->orderByDesc('pt3')
            ->orderByDesc('dr')
            ->orderByDesc('gf')
            ->orderBy('t.team_name')
            ->get();

        // Righe "nazioni unite": somma delle righe singole dei componenti
        // gia' presenti; se ne serve almeno DUE (mai duplicati pre-debutto).
        $globale = $annoMax === null;
        $perId   = $righe->keyBy('team_id');
        $unite   = collect();

        foreach (self::GRUPPI_UNITI as $g) {
            $comp = collect($g['ids'])
                ->map(fn ($id) => $perId->get($id))
                ->filter()
                ->values();
            if ($comp->count() < 2) {
                continue;
            }
            $info = $globale ? $g['globale'] : $g['parziale'];
            $gf = $comp->sum(fn ($c) => (int) $c->gf);
            $gs = $comp->sum(fn ($c) => (int) $c->gs);
            $unite->push((object) [
                'team_id'   => null,
                'team_code' => $info['code'],
                'team_name' => $info['nome'],
                'pg'  => $comp->sum(fn ($c) => (int) $c->pg),
                'v'   => $comp->sum(fn ($c) => (int) $c->v),
                'n'   => $comp->sum(fn ($c) => (int) $c->n),
                'p'   => $comp->sum(fn ($c) => (int) $c->p),
                'gf'  => $gf,
                'gs'  => $gs,
                'dr'  => $gf - $gs,
                'pt3' => $comp->sum(fn ($c) => (int) $c->pt3),
                'pt2' => $comp->sum(fn ($c) => (int) $c->pt2),
                'ori'     => $comp->sum(fn ($c) => (int) $c->ori),
                'argenti' => $comp->sum(fn ($c) => (int) $c->argenti),
                'bronzi'  => $comp->sum(fn ($c) => (int) $c->bronzi),
                'unita'   => true,
                // Componenti (in ordine cronologico) per la nota a pie' di
                // caption: solo quelli che hanno gia' giocato.
                'catena'  => $comp->pluck('team_name')->all(),
            ]);
        }

        // Riordino complessivo con le stesse chiavi della query (sort
        // stabile: a parita' totale le righe singole conservano l'ordine
        // SQL). Il nome di confronto ignora l'asterisco e gli accenti.
        $tutte = $righe->concat($unite)->sort(function ($a, $b) {
            return [(int) $b->pt3, (int) $b->dr, (int) $b->gf, $this->nomeOrdinabile($a->team_name)]
               <=> [(int) $a->pt3, (int) $a->dr, (int) $a->gf, $this->nomeOrdinabile($b->team_name)];
        })->values();

        return $tutte->map(function ($r, $i) use ($flagTid) {
            return [
                'pos'       => $i + 1,
                'team_code' => $r->team_code,
                'team_name' => $r->team_name,
                'flag'      => $this->wc->bandieraUrl($r->team_code, $flagTid),
                // In "Perpetua" il nome linka la scheda squadra (es. /squadra/ITA)
                'url'       => $r->team_code ? route('squadra.show', $r->team_code) : null,
                'pg'  => (int) $r->pg,
                'v'   => (int) $r->v,
                'n'   => (int) $r->n,
                'p'   => (int) $r->p,
                'gf'  => (int) $r->gf,
                'gs'  => (int) $r->gs,
                'dr'  => (int) $r->dr,
                'pt3' => (int) $r->pt3,
                'pt2' => (int) $r->pt2,
                'nota'     => null,
                'medaglie' => [(int) $r->ori, (int) $r->argenti, (int) $r->bronzi],
                'unita'    => (bool) ($r->unita ?? false),
                'catena'   => $r->catena ?? null,
            ];
        })->all();
    }

    /**
     * Nome usato per il confronto alfabetico: minuscolo, senza l'asterisco
     * delle righe unite e senza accenti (avvicina la collation *_ci di MySQL).
     */
    protected function nomeOrdinabile(?string $nome): string
    {
        $s = mb_strtolower(str_replace('*', '', (string) $nome));

        return strtr($s, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);
    }
}
