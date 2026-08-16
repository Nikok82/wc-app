<?php

namespace App\Http\Controllers;

use App\Services\WcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Sezione Club (C2 del todo 15/08).
 *
 * Elenco: tutti i club di awc_clubs in ordine alfabetico, dieci per pagina,
 * con un menu a tendina delle nazioni rappresentate che filtra l'elenco.
 * Accanto a ogni club la bandiera della nazione e lo stemma a 16x16.
 *
 * Scheda: stemma a 60x60, bandiera dello stato e sotto tutti i giocatori
 * convocati ai Mondiali mentre militavano in quel club, divisi per edizione.
 *
 * awc_clubs: id, club_id, club_name, club_code, stato (nome in italiano),
 * logo, wikipedia_page. Il legame con le rose e' awc_squads.team_past_id.
 * Gli stemmi mancanti (538 su 1883) mostrano un segnaposto.
 */
class ClubController extends Controller
{
    /** Quanti club per pagina nell'elenco (deciso da Niko il 15/08). */
    protected const PER_PAGINA = 10;

    /** Valore della tendina che seleziona i club senza nazione assegnata. */
    public const SENZA_NAZIONE = '@senza';

    public function __construct(protected WcService $wc)
    {
    }

    /** Elenco alfabetico con filtro per nazione. */
    public function index(Request $request)
    {
        $stato = trim((string) $request->query('stato', ''));
        $q     = trim((string) $request->query('q', ''));

        // ids=1 mostra l'id accanto al nome. Serve alla caccia ai club
        // doppioni: senza, per compilare l'elenco delle unioni bisogna
        // aprire ogni scheda e leggere l'indirizzo. Fuori da quel lavoro
        // basta non passare il parametro e non si vede nulla.
        $mostraId = $request->query('ids') === '1';

        // Valore speciale della tendina: i club senza nazione assegnata.
        // Sono invisibili a qualsiasi filtro normale, quindi senza una voce
        // apposta non si scoprirebbero se non interrogando il database.
        $senzaNazione = $stato === self::SENZA_NAZIONE;

        $base = DB::table('awc_clubs')
            ->when($senzaNazione, fn ($query) => $query->where(
                fn ($x) => $x->whereNull('stato')->orWhereRaw("TRIM(stato) = ''")
            ))
            ->when($stato !== '' && ! $senzaNazione, fn ($query) => $query->where('stato', $stato))
            ->when($q !== '', fn ($query) => $query->where('club_name', 'like', '%'.$q.'%'))
            ->orderBy('club_name')
            ->orderBy('id');

        // Filtrando per nazione l'elenco esce tutto in una schermata: per
        // confrontare fra loro i club di uno stesso paese e scovare i
        // doppioni, sfogliare dieci per volta e' inutilizzabile. Si resta
        // sul paginatore (la view non cambia) alzando le righe per pagina
        // al totale: lastPage() diventa 1 e le frecce spariscono da sole.
        $perPagina = $stato !== ''
            ? max(1, (clone $base)->count())
            : self::PER_PAGINA;

        $items = $base
            ->paginate($perPagina)
            ->withQueryString()
            ->through(fn ($c) => [
                'id'    => $c->id,
                'nome'  => $c->club_name,
                'stato' => $c->stato,
                'flag'  => $this->wc->bandieraUrl($c->stato, null),
                'logo'  => $this->wc->logoClubUrl($c->logo),
            ]);

        // Nazioni rappresentate: la tendina le elenca tutte, anche quando il
        // filtro attivo ne mostra una sola.
        $nazioni = DB::table('awc_clubs')
            ->select('stato')
            ->whereNotNull('stato')
            ->where('stato', '<>', '')
            ->distinct()
            ->orderBy('stato')
            ->pluck('stato');

        // Quanti club senza nazione esistono in tutto: il numero sta accanto
        // alla voce della tendina, cosi' si sa se vale la pena aprirla.
        $nSenzaNazione = DB::table('awc_clubs')
            ->where(fn ($x) => $x->whereNull('stato')->orWhereRaw("TRIM(stato) = ''"))
            ->count();

        // Quanti, fra quelli elencati adesso, sono senza stemma. Serve alla
        // caccia agli stemmi mancanti, che si fa dalla stessa pagina.
        $senzaStemma = (clone $base)
            ->where(fn ($x) => $x->whereNull('logo')->orWhereRaw("TRIM(logo) = ''"))
            ->count();

        return view('club.index', compact(
            'items', 'nazioni', 'stato', 'q', 'mostraId',
            'senzaNazione', 'nSenzaNazione', 'senzaStemma'
        ));
    }

    /** Scheda del singolo club. */
    public function show(string $id)
    {
        $c = DB::table('awc_clubs')->where('id', $id)->first();
        abort_if(! $c, 404, 'Club non trovato');

        /* ---- Convocati ai Mondiali mentre erano in questo club ---- */
        $righe = DB::table('awc_squads')
            ->where('team_past_id', $c->id)
            ->orderBy('tournament_id')
            ->orderBy('family_name')
            ->orderBy('given_name')
            ->get();

        // Etichetta dell'edizione: "Paese Anno", la stessa forma del menu.
        $tornei = DB::table('awc_tournaments')->get()->keyBy('tournament_id');

        $mondiali = $righe->groupBy('tournament_id')
            ->map(function ($rose, $tid) use ($tornei) {
                $anno = $this->wc->anno($tid);
                $t    = $tornei[$tid] ?? null;

                return [
                    'tid'   => $tid,
                    'anno'  => $anno,
                    // Il numero di maglia esiste solo dal 1954 in poi: prima
                    // di allora la colonna e' vuota e non va inventata.
                    'nome'  => $t ? trim(($t->host_country ?? '').' '.($t->year ?? $anno)) : ('Mondiale '.$anno),
                    'righe' => $rose->map(fn ($r) => [
                        'player_id' => $r->player_id,
                        'nome'      => trim(($r->given_name ?? '').' '.($r->family_name ?? '')),
                        'squadra'   => $r->team_name,
                        'code'      => $r->team_code,
                        'flag'      => $this->wc->bandieraUrl($r->team_code, $tid),
                        'maglia'    => ($anno && $anno >= 1954) ? ($r->shirt_number ?: null) : null,
                    ])->values(),
                ];
            })
            ->sortBy('anno')
            ->values();

        return view('club.show', [
            'club'     => $c,
            'nome'     => $c->club_name,
            'flag'     => $this->wc->bandieraUrl($c->stato, null),
            'logo'     => $this->wc->logoClubUrl($c->logo),
            'mondiali' => $mondiali,
            'totale'   => $righe->pluck('player_id')->filter()->unique()->count(),
        ]);
    }
}
