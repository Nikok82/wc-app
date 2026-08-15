<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Goal;
use App\Models\Player;
use App\Models\PlayerAppearance;
use App\Models\Squad;
use App\Models\Substitution;
use App\Services\WcService;
use Illuminate\Http\Request;

class GiocatoreController extends Controller
{
    public function __construct(protected WcService $wc)
    {
    }

    /**
     * Elenco giocatori: ricerca + paginazione (20/50/100) + popup scheda.
     */
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100], true) ? $perPage : 20;

        $items = Player::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('given_name', 'like', '%'.$q.'%')
                      ->orWhere('family_name', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('family_name')
            ->orderBy('given_name')
            ->paginate($perPage)
            ->withQueryString();

        // Bandiera per riga (stessa logica della scheda): nazionale piu'
        // recente dalle rose, una sola query per pagina.
        $rose = Squad::whereIn('player_id', collect($items->items())->pluck('player_id'))
            ->get()
            ->groupBy('player_id');

        $items = $items->through(function ($p) use ($rose) {
            $ultima = $rose->get($p->player_id, collect())
                ->sortByDesc(fn ($s) => $this->wc->anno($s->tournament_id) ?? 0)
                ->first();

            return [
                'id'    => $p->player_id,
                'nome'  => trim(($p->given_name ?? '').' '.($p->family_name ?? '')),
                'extra' => $p->birth_date ? $p->birth_date->format('d/m/Y') : '',
                'flag'  => $ultima ? $this->wc->bandieraUrl($ultima->team_code, $ultima->tournament_id) : null,
            ];
        });

        return view('elenco', [
            'titolo'      => 'Giocatori',
            'items'       => $items,
            'q'           => $q,
            'perPage'     => $perPage,
            'routeIndex'  => 'giocatori.index',
            'routeScheda' => 'giocatore.scheda',
            'routeShow'   => 'giocatore.show',
            'labelExtra'  => 'Data di nascita',
        ]);
    }

    /** Pagina completa della scheda giocatore. */
    public function show(string $id)
    {
        $dati = array_merge($this->datiScheda($id), $this->navigazione($id));

        return view('giocatore.show', $dati);
    }

    /** Solo il frammento scheda (usato dal popup degli elenchi). */
    public function scheda(string $id)
    {
        $dati = $this->datiScheda($id);

        return view('giocatore.scheda', $dati);
    }

    /* ------------------------------------------------------------------ */

    /**
     * Barra bottoni: giocatore precedente e successivo in ordine
     * alfabetico, lo stesso dell'elenco da cui si arriva. La miniatura
     * e' la bandiera della nazionale con cui ha giocato l'ultima volta.
     */
    protected function navigazione(string $id): array
    {
        [$prev, $next] = $this->wc->vicini(
            'awc_players', 'player_id', ['family_name', 'given_name'], $id
        );

        $voce = function ($v) {
            if (! $v) {
                return null;
            }

            $ultima = \Illuminate\Support\Facades\DB::table('awc_squads')
                ->where('player_id', $v->player_id)
                ->orderByDesc('tournament_id')->first();

            return [
                'url'   => route('giocatore.show', $v->player_id),
                'img'   => $ultima
                    ? $this->wc->bandieraUrl($ultima->team_code, $ultima->tournament_id)
                    : null,
                'forma' => 'tonda',
                'label' => trim($v->given_name.' '.$v->family_name),
            ];
        };

        return ['barraPrev' => $voce($prev), 'barraNext' => $voce($next)];
    }

    protected function datiScheda(string $id): array
    {
        $g = Player::where('player_id', $id)->first();
        abort_if(! $g, 404, 'Giocatore non trovato');

        $apps = PlayerAppearance::where('player_id', $g->player_id)
            ->orderBy('match_date')
            ->get();

        $matchIds = $apps->pluck('match_id')->filter()->unique()->values();

        $sostituzioni = Substitution::where('player_id', $g->player_id)
            ->whereIn('match_id', $matchIds)->get()->groupBy('match_id');

        $espulsioni = Booking::where('player_id', $g->player_id)
            ->whereIn('match_id', $matchIds)->where('red_card', 1)
            ->get()->groupBy('match_id');

        $gol = Goal::where('player_id', $g->player_id)
            ->whereIn('match_id', $matchIds)->get()->groupBy('match_id');

        /* ---- Righe della tabella "Gare giocate" ---- */
        $gare = $apps->map(function ($a) use ($sostituzioni, $espulsioni, $gol) {
            $celle = $this->wc->celleMatch($a->match_name, $a->tournament_id, $a->home_team, $a->away_team);

            /* Minutaggio: sostituzioni / espulsione / partita intera */
            $eventi = [];
            foreach ($sostituzioni->get($a->match_id, collect()) as $s) {
                if ((int) $s->coming_on === 1) {
                    $eventi[] = 'subentrato al '.$s->minute_label;
                }
                if ((int) $s->going_off === 1) {
                    $eventi[] = 'sostituito al '.$s->minute_label;
                }
            }
            foreach ($espulsioni->get($a->match_id, collect()) as $e) {
                $eventi[] = 'espulso al '.$e->minute_label;
            }
            $minutaggio = $eventi ? ucfirst(implode(', ', $eventi)) : 'Partita intera';

            /* Gol (esclusi autogol), con minuti e "(Rig.)" per i rigori */
            $golPartita = $gol->get($a->match_id, collect())
                ->filter(fn ($x) => (int) $x->own_goal === 0);

            $testoGol = '';
            if ($golPartita->isNotEmpty()) {
                $minuti = $golPartita
                    ->map(fn ($x) => $x->minute_label.((int) $x->penalty === 1 ? ' (Rig.)' : ''))
                    ->implode(', ');
                $testoGol = $golPartita->count().' gol: '.$minuti;
            }

            return [
                'anno'      => $this->wc->anno($a->tournament_id),
                'data'      => $a->match_date ? $a->match_date->format('d/m/Y') : '',
                'stage'     => $a->stage_name,
                'match'     => $celle,
                'match_id'  => $a->match_id,
                'maglia'    => trim(($a->shirt_number ?? '').' - '.($a->position_name ?? ''), ' -'),
                'minutaggio' => $minutaggio,
                'gol'       => $testoGol,
            ];
        });

        /* ---- C1 (15/08): le partite passano all'impaginazione a due lati
                della scheda squadra. Cio' che la vecchia tabella teneva in
                colonne (maglia, minutaggio, gol) diventa la riga 'extra'
                sotto il punteggio: nella card non c'e' spazio per sei
                colonne, ma il dato non va perso. ---- */
        $extra = $gare->mapWithKeys(fn ($g) => [$g['match_id'] => collect([
            $g['maglia'] ? 'Maglia '.$g['maglia'] : null,
            $g['minutaggio'] ?: null,
            $g['gol'] ?: null,
        ])->filter()->implode(' · ')])->all();

        $partite = $this->wc->gruppiPartite($matchIds->all(), $extra);

        /* ---- Ruolo (piu' ruoli separati da virgola) ---- */
        $ruoli = [];
        foreach (['portiere' => 'Portiere', 'difensore' => 'Difensore',
                  'centrocampista' => 'Centrocampista', 'attaccante' => 'Attaccante'] as $col => $label) {
            if ($this->wc->haColonna('awc_players', $col) && (int) $g->{$col} === 1) {
                $ruoli[] = $label;
            }
        }

        /* ---- Bandiere in alto (una per nazionale rappresentata) ---- */
        $bandiere = $apps
            ->map(fn ($a) => $this->wc->bandieraUrl($a->team_code, $a->tournament_id))
            ->filter()->unique()->values()->all();

        // Fallback: se non ci sono appearances (dati non ancora caricati),
        // ricaviamo nazionale e torneo dalle rose (awc_squads).
        if (empty($bandiere)) {
            $bandiere = Squad::where('player_id', $g->player_id)->get()
                ->map(fn ($s) => $this->wc->bandieraUrl($s->team_code, $s->tournament_id))
                ->filter()->unique()->values()->all();
        }

        /* ---- Tornei giocati ---- */
        if ($this->wc->haColonna('awc_players', 'count_tournaments')
            && $this->wc->haColonna('awc_players', 'list_tournaments')) {
            $anni  = $this->wc->anniDaLista($g->list_tournaments);
            $count = (int) $g->count_tournaments;
        } else {
            $anni = $apps->pluck('tournament_id')->filter()->unique()
                ->map(fn ($t) => (string) $this->wc->anno($t))->filter()->sort()->values()->all();
            $count = count($anni);
        }
        $tornei = [
            'count' => $count,
            'anni'  => collect($anni)->map(fn ($a) => ['anno' => $a, 'tid' => 'WC-'.$a])->all(),
        ];

        /* ---- Club di provenienza, uno per Mondiale disputato ---- */
        $righeRosa = Squad::where('player_id', $g->player_id)
            ->orderBy('tournament_id')->get();

        $idClub = $righeRosa->pluck('team_past_id')->filter()->unique()->values();
        $anagraficaClub = $idClub->isEmpty()
            ? collect()
            : \Illuminate\Support\Facades\DB::table('awc_clubs')
                ->whereIn('id', $idClub)->get()->keyBy('id');

        $club = $righeRosa->map(function ($r) use ($anagraficaClub) {
            $c = $r->team_past_id ? ($anagraficaClub[$r->team_past_id] ?? null) : null;

            return [
                'anno' => (string) $this->wc->anno($r->tournament_id),
                'nome' => $r->team_past ?: ($c->club_name ?? null),
                'logo' => $this->wc->logoClubUrl($c->logo ?? null),
                // D1 (15/08): l'id serve al link verso la scheda del club.
                // Resta null per le righe che hanno solo il nome scritto a
                // mano in team_past, senza corrispondenza in awc_clubs.
                'id'   => $c->id ?? null,
            ];
        })->filter(fn ($c) => ! empty($c['nome']))->values()->all();

        return [
            'club'     => $club,
            'gruppi'   => $partite['gruppi'],
            'gol'      => $partite['gol'],
            'g'        => $g,
            'nome'     => trim(($g->given_name ?? '').' '.($g->family_name ?? '')),
            'nascita'  => $g->birth_date ? $g->birth_date->format('d/m/Y') : '',
            'anni'     => $g->birth_date ? $g->birth_date->age : null,
            'ruolo'    => implode(', ', $ruoli),
            'bandiere' => $bandiere,
            'tornei'   => $tornei,
            'gare'     => $gare,
            'wikipedia' => $this->wc->haColonna('awc_players', 'player_wikipedia_link')
                ? $g->player_wikipedia_link : null,
        ];
    }
}
