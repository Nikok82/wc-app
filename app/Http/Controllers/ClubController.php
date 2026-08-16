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

        // Nomi d'epoca dei soli club della schermata: e' la ragione per cui
        // "JE Tizi-Ouzou" non si trovava, e cercarlo qui e' il primo posto
        // dove uno guarda.
        $epoca = $this->nomiEpoca(
            $items->pluck('id')->all(),
            $items->pluck('nome', 'id')->all()
        );

        return view('club.index', compact(
            'items', 'nazioni', 'stato', 'q', 'mostraId',
            'senzaNazione', 'nSenzaNazione', 'senzaStemma', 'epoca'
        ));
    }

    /**
     * Nomi d'epoca dei club indicati, ricavati dalle rose.
     *
     * Il catalogo (awc_clubs.club_name) porta il titolo della pagina wiki,
     * cioe' il nome di oggi; le rose (awc_squads.team_past) portano il nome
     * in uso nell'anno della convocazione. Quando i due non coincidono, il
     * secondo e' un nome d'epoca: "JS Kabylie" a catalogo, "JE Tizi-Ouzou"
     * nelle rose del 1982. Senza mostrarlo, chi legge una rosa non trova
     * quel club da nessuna parte.
     *
     * Le diciture scritte a mano in resources/data/club-nomi-epoca.php
     * hanno la precedenza: servono per i casi che il database non sa, come
     * le fusioni fra societa'.
     *
     * @param  array  $ids  id dei club
     * @param  array  $nomi id => nome a catalogo
     * @return array  id => testo pronto da stampare
     */
    public function nomiEpoca(array $ids, array $nomi): array
    {
        $manuali = $this->diciturePersonali();

        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return [];
        }

        $righe = DB::table('awc_squads')
            ->select('team_past_id', 'team_past', 'tournament_id')
            ->whereIn('team_past_id', $ids)
            ->whereNotNull('team_past')
            ->where('team_past', '<>', '')
            ->distinct()
            ->get();

        // Raccolta: per ogni club, ogni grafia diversa dal nome a catalogo
        // con gli anni in cui compare.
        $per = [];
        foreach ($righe as $r) {
            $catalogo = $nomi[$r->team_past_id] ?? '';
            if ($this->stessoNome($r->team_past, $catalogo)) {
                continue;
            }

            $anno = $this->wc->anno($r->tournament_id);
            $chiave = mb_strtolower(trim($r->team_past));

            if (! isset($per[$r->team_past_id][$chiave])) {
                $per[$r->team_past_id][$chiave] = ['nome' => trim($r->team_past), 'anni' => []];
            }
            if ($anno) {
                $per[$r->team_past_id][$chiave]['anni'][] = (int) $anno;
            }
        }

        $esito = [];
        foreach ($per as $id => $voci) {
            $pezzi = [];
            foreach ($voci as $v) {
                $anni = array_unique($v['anni']);
                sort($anni);
                // Un anno solo resta un anno; piu' anni diventano un
                // intervallo: "1982-1986", non l'elenco di tutte le edizioni.
                $quando = ! $anni ? '' : (count($anni) === 1
                    ? (string) $anni[0]
                    : $anni[0].'-'.end($anni));

                $pezzi[] = $v['nome'].($quando ? ' ('.$quando.')' : '');
            }
            sort($pezzi);
            $esito[$id] = implode(' · ', $pezzi);
        }

        // Le diciture scritte a mano sostituiscono quelle ricavate; una
        // stringa vuota nasconde la riga.
        foreach ($manuali as $id => $testo) {
            if (in_array($id, $ids)) {
                $esito[$id] = $testo;
            }
        }

        return array_filter($esito, fn ($t) => trim((string) $t) !== '');
    }

    /** Confronto tollerante fra due grafie di un nome di club. */
    protected function stessoNome(?string $a, ?string $b): bool
    {
        $norm = fn ($x) => preg_replace('/[^a-z0-9]/u', '',
            mb_strtolower(trim((string) $x)));

        return $norm($a) === $norm($b);
    }

    /** Diciture d'epoca scritte a mano, se il file esiste. */
    protected function diciturePersonali(): array
    {
        $file = resource_path('data/club-nomi-epoca.php');

        return is_file($file) ? (array) require $file : [];
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

        $epoca = $this->nomiEpoca([$c->id], [$c->id => $c->club_name]);

        return view('club.show', [
            'club'     => $c,
            'nome'     => $c->club_name,
            'flag'     => $this->wc->bandieraUrl($c->stato, null),
            'logo'     => $this->wc->logoClubUrl($c->logo),
            'mondiali' => $mondiali,
            'epoca'    => $epoca[$c->id] ?? null,
            'totale'   => $righe->pluck('player_id')->filter()->unique()->count(),
        ]);
    }
}
