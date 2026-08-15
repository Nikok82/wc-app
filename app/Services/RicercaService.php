<?php

namespace App\Services;

use App\Support\RicercaQuery;
use Illuminate\Support\Facades\DB;

/**
 * Ricerca globale (A3 del todo 15/08).
 *
 * Interroga nove tipi di entita' e li restituisce nell'ordine deciso da
 * Niko, dal piu' specifico al piu' generico: Torneo, Squadra, Squadre-anno,
 * Partite, Giocatori, Allenatori, Arbitri, Stadi, Club.
 *
 * Dieci risultati per tipo; oltre i dieci si impagina, sempre a dieci.
 * Il chiamante puo' chiedere una pagina diversa di UN SOLO tipo passando
 * $tipoPagina e $pagina: gli altri tipi restano alla prima pagina.
 *
 * Ricerca per numero di maglia: la sorgente e' awc_player_appearances (il
 * numero partita per partita) e non awc_squads (il numero per torneo).
 * Scelta dichiarata: la prima permette di distinguere chi ha cambiato
 * numero durante la competizione, e con "#10 Argentina 1986" restituisce il
 * singolo giocatore giusto.
 */
class RicercaService
{
    public const PER_PAGINA = 10;

    /** Ordine dei tipi nel riquadro dei risultati. */
    public const TIPI = [
        'tornei'       => 'Torneo',
        'squadre'      => 'Squadra',
        'squadre_anno' => 'Squadre-anno',
        'partite'      => 'Partite',
        'giocatori'    => 'Giocatori',
        'allenatori'   => 'Allenatori',
        'arbitri'      => 'Arbitri',
        'stadi'        => 'Stadi',
        'club'         => 'Club',
    ];

    public function __construct(protected WcService $wc)
    {
    }

    /**
     * @return array ['gruppi' => [chiave => ['titolo','voci','pagina','pagine','totale']],
     *                'totale' => int, 'avviso' => ?string]
     */
    public function cerca(string $q, ?string $tipoPagina = null, int $pagina = 1): array
    {
        $r = RicercaQuery::leggi($q);

        if ($r->vuota()) {
            return ['gruppi' => [], 'totale' => 0, 'avviso' => null];
        }

        // Gli anni a due cifre si risolvono sulle edizioni realmente
        // esistenti: "90" -> 1990, "26" -> 2026. Se nessuna edizione finisce
        // con quelle cifre non c'e' anno, e la ricerca resta testuale.
        $anniTornei = DB::table('awc_tournaments')->orderBy('year')
            ->pluck('year')->map(fn ($y) => (int) $y)->all();

        $anni = $r->anniPieni;
        foreach ($r->anniBrevi as $dd) {
            foreach ($anniTornei as $y) {
                if (substr((string) $y, -2) === $dd) {
                    $anni[] = $y;
                }
            }
        }
        $anni = array_values(array_unique(array_filter($anni)));

        /* ---- Ricerca per numero di maglia: unico tipo, Giocatori ---- */
        if ($r->perMaglia()) {
            if (! $r->magliaValida()) {
                return [
                    'gruppi' => [],
                    'totale' => 0,
                    'avviso' => 'Il numero di maglia va accompagnato da una nazione o da un anno.',
                ];
            }

            // Dal 1954 in poi: per le quattro edizioni senza numeri il
            // risultato e' vuoto, e lo si dice invece di inventare.
            $anniMaglie = array_values(array_filter($anni, fn ($y) => $y >= RicercaQuery::ANNO_MAGLIE));
            if ($anni && ! $anniMaglie) {
                return [
                    'gruppi' => [],
                    'totale' => 0,
                    'avviso' => 'Prima del '.RicercaQuery::ANNO_MAGLIE
                        .' i numeri di maglia non esistono nei dati dei Mondiali.',
                ];
            }

            $voci = $this->perMaglia($r->maglia, $r->testo, $anniMaglie, $tipoPagina === 'giocatori' ? $pagina : 1);

            return [
                'gruppi' => $voci['totale'] ? ['giocatori' => $voci] : [],
                'totale' => $voci['totale'],
                'avviso' => null,
            ];
        }

        /* ---- Ricerca ordinaria ---- */
        $gruppi = [];
        foreach (array_keys(self::TIPI) as $tipo) {
            $p = ($tipoPagina === $tipo) ? max(1, $pagina) : 1;
            $g = $this->gruppo($tipo, $r->testo, $anni, $p);
            if ($g['totale'] > 0) {
                $gruppi[$tipo] = $g;
            }
        }

        return [
            'gruppi' => $gruppi,
            'totale' => array_sum(array_column($gruppi, 'totale')),
            'avviso' => null,
        ];
    }

    /* ------------------------------------------------------------------ */

    /** Confeziona un gruppo: titolo, voci della pagina, conteggi. */
    protected function impagina(string $tipo, $query, callable $voce, int $pagina): array
    {
        // Il conteggio passa da una sottoquery: con ->distinct() su piu'
        // colonne (le squadre-anno, i giocatori per numero di maglia) un
        // count(*) diretto conterebbe anche i duplicati che la distinct
        // toglie, e i numeri non tornerebbero con le righe mostrate.
        $totale = DB::query()->fromSub((clone $query)->reorder(), 'sub')->count();
        $pagine = max(1, (int) ceil($totale / self::PER_PAGINA));
        $pagina = min(max(1, $pagina), $pagine);

        $righe = $totale
            ? $query->forPage($pagina, self::PER_PAGINA)->get()
            : collect();

        return [
            'titolo' => self::TIPI[$tipo],
            'voci'   => $righe->map($voce)->values(),
            'pagina' => $pagina,
            'pagine' => $pagine,
            'totale' => $totale,
        ];
    }

    protected function gruppo(string $tipo, string $testo, array $anni, int $pagina): array
    {
        $like = '%'.$testo.'%';
        $conTesto = $testo !== '';

        switch ($tipo) {

            case 'tornei':
                $q = DB::table('awc_tournaments')
                    ->when($conTesto, fn ($x) => $x->where('host_country', 'like', $like))
                    ->when($anni, fn ($x) => $x->whereIn('year', $anni))
                    ->orderBy('year');

                return $this->impagina($tipo, $q, fn ($t) => [
                    'titolo'      => trim(($t->host_country ?? '').' '.$t->year),
                    'sottotitolo' => 'Coppa del Mondo',
                    'url'         => route('torneo.show', $t->tournament_id),
                    'img'         => $this->wc->bandieraUrl($t->host_country, $t->tournament_id),
                ], $pagina);

            case 'squadre':
                // Senza testo un elenco di tutte le nazionali non dice nulla:
                // il solo anno non e' una domanda sulle squadre.
                if (! $conTesto) {
                    return $this->vuoto($tipo);
                }
                $q = DB::table('awc_teams')
                    ->where('visibility', 0)
                    ->where('team_name', 'like', $like)
                    ->orderBy('team_name');

                return $this->impagina($tipo, $q, fn ($t) => [
                    'titolo'      => $t->team_name,
                    'sottotitolo' => $t->team_code,
                    'url'         => route('squadra.show', $t->team_code),
                    'img'         => $this->wc->bandieraUrl($t->team_code, null),
                ], $pagina);

            case 'squadre_anno':
                if (! $conTesto && ! $anni) {
                    return $this->vuoto($tipo);
                }
                // awc_squads: una riga per convocato, quindi qui si passa dai
                // valori distinti torneo+squadra.
                $q = DB::table('awc_squads')
                    ->select('tournament_id', 'team_code', 'team_name')
                    ->when($conTesto, fn ($x) => $x->where('team_name', 'like', $like))
                    ->when($anni, fn ($x) => $x->whereIn('tournament_id',
                        array_map(fn ($y) => 'WC-'.$y, $anni)))
                    ->whereNotNull('team_code')
                    ->distinct()
                    ->orderBy('team_name')
                    ->orderBy('tournament_id');

                return $this->impagina($tipo, $q, function ($s) {
                    $anno = $this->wc->anno($s->tournament_id);

                    return [
                        'titolo'      => $s->team_name.' '.$anno,
                        'sottotitolo' => 'Rosa e partite dell\'edizione',
                        'url'         => $anno
                            ? route('squadra_anno.show', ['code' => $s->team_code, 'year' => $anno])
                            : route('squadra.show', $s->team_code),
                        'img'         => $this->wc->bandieraUrl($s->team_code, $s->tournament_id),
                    ];
                }, $pagina);

            case 'partite':
                if (! $conTesto) {
                    return $this->vuoto($tipo);
                }
                $q = DB::table('awc_matches')
                    ->where(fn ($x) => $x->where('home_team_name', 'like', $like)
                                         ->orWhere('away_team_name', 'like', $like))
                    ->when($anni, fn ($x) => $x->whereIn('tournament_id',
                        array_map(fn ($y) => 'WC-'.$y, $anni)))
                    ->orderBy('match_date')
                    ->orderBy('match_id');

                return $this->impagina($tipo, $q, fn ($m) => [
                    'titolo'      => $m->home_team_name.' '.$m->home_team_score
                                     .'-'.$m->away_team_score.' '.$m->away_team_name,
                    'sottotitolo' => collect([
                        $m->match_date ? date('d/m/Y', strtotime($m->match_date)) : null,
                        $m->stage_name,
                    ])->filter()->implode(' · '),
                    'url'         => route('partita.show', $m->match_id),
                    'img'         => $this->wc->bandieraUrl($m->home_team_code, $m->tournament_id),
                ], $pagina);

            case 'giocatori':
                if (! $conTesto) {
                    return $this->vuoto($tipo);
                }
                $q = DB::table('awc_players')
                    ->where(fn ($x) => $x->where('family_name', 'like', $like)
                                         ->orWhere('given_name', 'like', $like))
                    ->orderBy('family_name')->orderBy('given_name');

                return $this->impagina($tipo, $q, fn ($p) => [
                    'titolo'      => trim(($p->given_name ?? '').' '.($p->family_name ?? '')),
                    'sottotitolo' => $p->birth_date ? date('d/m/Y', strtotime($p->birth_date)) : '',
                    'url'         => route('giocatore.show', $p->player_id),
                    'img'         => null,
                ], $pagina);

            case 'allenatori':
                if (! $conTesto) {
                    return $this->vuoto($tipo);
                }
                $q = DB::table('awc_managers')
                    ->where(fn ($x) => $x->where('family_name', 'like', $like)
                                         ->orWhere('given_name', 'like', $like))
                    ->orderBy('family_name')->orderBy('given_name');

                return $this->impagina($tipo, $q, fn ($m) => [
                    'titolo'      => trim(($m->given_name ?? '').' '.($m->family_name ?? '')),
                    'sottotitolo' => '',
                    'url'         => route('allenatore.show', $m->manager_id),
                    'img'         => null,
                ], $pagina);

            case 'arbitri':
                if (! $conTesto) {
                    return $this->vuoto($tipo);
                }
                $q = DB::table('awc_referees')
                    ->where(fn ($x) => $x->where('family_name', 'like', $like)
                                         ->orWhere('given_name', 'like', $like))
                    ->orderBy('family_name')->orderBy('given_name');

                return $this->impagina($tipo, $q, fn ($a) => [
                    'titolo'      => trim(($a->given_name ?? '').' '.($a->family_name ?? '')),
                    'sottotitolo' => $a->country_name ?? '',
                    'url'         => route('arbitro.show', $a->referee_id),
                    'img'         => $this->wc->bandieraUrl($a->country_name ?? null, null),
                ], $pagina);

            case 'stadi':
                if (! $conTesto) {
                    return $this->vuoto($tipo);
                }
                $q = DB::table('awc_stadiums')
                    ->where(fn ($x) => $x->where('stadium_name', 'like', $like)
                                         ->orWhere('city_name', 'like', $like))
                    ->orderBy('stadium_name');

                return $this->impagina($tipo, $q, fn ($s) => [
                    'titolo'      => $s->stadium_name,
                    'sottotitolo' => trim(($s->city_name ?? '').' ('.($s->country_name ?? '').')', ' ()'),
                    'url'         => route('stadio.show', $s->stadium_id),
                    'img'         => $this->wc->bandieraUrl($s->country_name ?? null, null),
                ], $pagina);

            case 'club':
                if (! $conTesto) {
                    return $this->vuoto($tipo);
                }
                $q = DB::table('awc_clubs')
                    ->where('club_name', 'like', $like)
                    ->orderBy('club_name');

                return $this->impagina($tipo, $q, fn ($c) => [
                    'titolo'      => $c->club_name,
                    'sottotitolo' => $c->stato ?? '',
                    'url'         => route('club.show', $c->id),
                    'img'         => $this->wc->bandieraUrl($c->stato ?? null, null),
                ], $pagina);
        }

        return $this->vuoto($tipo);
    }

    /**
     * Giocatori che hanno indossato un certo numero, eventualmente per una
     * nazione e/o in una edizione. Sorgente: awc_player_appearances.
     */
    protected function perMaglia(int $numero, string $nazione, array $anni, int $pagina): array
    {
        $q = DB::table('awc_player_appearances as pa')
            ->join('awc_players as p', 'p.player_id', '=', 'pa.player_id')
            ->where('pa.shirt_number', $numero)
            // Le edizioni senza numeri non hanno la colonna popolata, ma il
            // filtro sull'anno chiude comunque la porta a valori spuri.
            ->whereRaw('CAST(SUBSTRING(pa.tournament_id, -4) AS UNSIGNED) >= ?',
                [RicercaQuery::ANNO_MAGLIE])
            ->when($anni, fn ($x) => $x->whereIn('pa.tournament_id',
                array_map(fn ($y) => 'WC-'.$y, $anni)))
            ->when($nazione !== '', function ($x) use ($nazione) {
                // La nazione si puo' scrivere col nome o col codice.
                $codici = DB::table('awc_teams')
                    ->where('team_name', 'like', '%'.$nazione.'%')
                    ->pluck('team_code')->all();
                $codici[] = strtoupper($nazione);

                $x->whereIn('pa.team_code', array_unique($codici));
            })
            ->select('pa.player_id', 'pa.team_code', 'pa.tournament_id',
                     'p.given_name', 'p.family_name')
            ->distinct()
            ->orderBy('p.family_name')->orderBy('p.given_name')
            ->orderBy('pa.tournament_id');

        return $this->impagina('giocatori', $q, function ($g) use ($numero) {
            $anno = $this->wc->anno($g->tournament_id);

            return [
                'titolo'      => trim(($g->given_name ?? '').' '.($g->family_name ?? '')),
                'sottotitolo' => '#'.$numero.' · '.$g->team_code.' '.$anno,
                'url'         => route('giocatore.show', $g->player_id),
                'img'         => $this->wc->bandieraUrl($g->team_code, $g->tournament_id),
            ];
        }, $pagina);
    }

    protected function vuoto(string $tipo): array
    {
        return [
            'titolo' => self::TIPI[$tipo],
            'voci'   => collect(),
            'pagina' => 1,
            'pagine' => 1,
            'totale' => 0,
        ];
    }
}
