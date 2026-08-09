<?php

namespace App\Services;

use App\Models\Award;
use App\Models\AwardWinner;
use App\Models\Tournament;
use App\Models\TournamentStanding;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logica dati della sezione Torneo (pagina + tab), tradotta dal vecchio
 * tema WP (functioncampionato.php / ajaxcampionato.php / function-varie.php)
 * all'impianto Laravel/Eloquent della app nuova.
 *
 * Le bandiere e i link immagine passano da WcService (route 'img'), i link
 * a squadre/giocatori usano i named route della app (niente URL hardcodati).
 */
class TorneoService
{
    public function __construct(protected WcService $wc)
    {
    }

    /** Normalizza un id torneo in "WC-1990" a partire da "WC-1990" o "1990". */
    public function normalizzaId(string $tournamentId): string
    {
        if (preg_match('/^\d{4}$/', $tournamentId)) {
            return 'WC-'.$tournamentId;
        }

        return strtoupper($tournamentId);
    }

    /** Riga del torneo (awc_tournaments) o null. */
    public function torneo(string $tournamentId): ?Tournament
    {
        return Tournament::where('tournament_id', $tournamentId)->first();
    }

    /** Anno (int) da "WC-1990". */
    public function anno(string $tournamentId): ?int
    {
        return preg_match('/(\d{4})/', $tournamentId, $m) ? (int) $m[1] : null;
    }

    /** Titolo pagina: "Italia '90". */
    public function titolo(Tournament $t): string
    {
        $yy = substr((string) $t->year, -2);

        return trim(($t->host_country ?? '').' \''.$yy);
    }

    /** URL immagine manifesto/sfondo del torneo (resources/images/tornei/WC-1990.jpg). */
    public function manifestoUrl(int|string|null $year): ?string
    {
        if (! $year) {
            return null;
        }

        return route('img', ['tipo' => 'tornei', 'file' => 'WC-'.$year.'.jpg']);
    }

    /* ----------------------------------------------------------------- */
    /*  INFO: podio                                                       */
    /* ----------------------------------------------------------------- */

    /**
     * Podio 1°/2°/3° da awc_tournament_standings, con bandiera e link squadra.
     * Ritorna array indicizzato per posizione (1,2,3) coi soli presenti.
     */
    public function podio(string $tournamentId): array
    {
        $anno = $this->anno($tournamentId);

        $righe = TournamentStanding::where('tournament_id', $tournamentId)
            ->whereIn('position', [1, 2, 3])
            ->orderBy('position')
            ->get();

        $out = [];
        foreach ($righe as $r) {
            $out[(int) $r->position] = [
                'posizione'   => (int) $r->position,
                'team_code'   => $r->team_code,
                'team_name'   => $r->team_name,
                'flag'        => $this->wc->bandieraUrl($r->team_code, $tournamentId),
                'squadra_url' => $r->team_code ? route('squadra.show', $r->team_code) : null,
            ];
        }

        return $out;
    }

    /* ----------------------------------------------------------------- */
    /*  INFO: righe informative                                           */
    /* ----------------------------------------------------------------- */

    /**
     * Righe della scheda Info (paese ospitante, nome coppa, date in italiano,
     * iscritti, partecipanti). Le date seguono il formato "venerdì, 8 giugno 1990".
     */
    public function infoRighe(Tournament $t): array
    {
        $righe = [
            'Paese/i ospitante/i'        => $t->host_country,
            'Nome competizione'          => $t->nome_coppa,
            'Data Inizio'                => $this->dataEstesa($t->start_date),
            'Data Fine'                  => $this->dataEstesa($t->end_date),
            'Iscritti alle qualificazioni' => $t->total_qualifications,
            'Partecipanti alla fase finale' => $t->count_teams,
        ];

        // Rimuove le righe vuote mantenendo l'ordine.
        return array_filter($righe, fn ($v) => $v !== null && $v !== '');
    }

    /** "venerdì, 8 giugno 1990" (locale it) da una data Carbon o stringa. */
    public function dataEstesa($data): ?string
    {
        if (! $data) {
            return null;
        }
        $c = $data instanceof Carbon ? $data : Carbon::parse($data);

        return $c->locale('it')->isoFormat('dddd, D MMMM YYYY');
    }

    /* ----------------------------------------------------------------- */
    /*  INFO: premi                                                       */
    /* ----------------------------------------------------------------- */

    /**
     * Premi del torneo (Pallone/Scarpa d'Oro-Argento-Bronzo, Guanto d'Oro,
     * Miglior Giovane) nell'ordine di awc_awards. I premi non assegnati
     * (assenti in awc_award_winners) vengono omessi. Gestisce piu' vincitori
     * per lo stesso premio (es. Scarpa di Bronzo condivisa).
     */
    public function premi(string $tournamentId): array
    {
        $anno = $this->anno($tournamentId);

        $awards = Award::orderBy('award_id')->get();

        $winners = AwardWinner::where('tournament_id', $tournamentId)
            ->get()
            ->groupBy('award_id');

        $out = [];
        foreach ($awards as $a) {
            $vincitori = $winners->get($a->award_id);
            if (! $vincitori || $vincitori->isEmpty()) {
                continue; // Non Assegnato -> riga omessa
            }

            $lista = $vincitori->map(fn ($v) => [
                'nome'       => trim(($v->given_name ?? '').' '.($v->family_name ?? '')),
                'player_url' => $v->player_id ? route('giocatore.show', $v->player_id) : null,
                'team_code'  => $v->team_code,
                'flag'       => $this->wc->bandieraUrl($v->team_code, $tournamentId),
            ])->all();

            $out[] = [
                'premio'    => $a->award_name,
                'vincitori' => $lista,
            ];
        }

        return $out;
    }

    /* ----------------------------------------------------------------- */
    /*  SQUADRE: elenco card + dati mappa (Fase 3)                        */
    /* ----------------------------------------------------------------- */

    /**
     * Colori per traguardo raggiunto (performance -> [riempimento, bordo]).
     * Legenda di Niko (13/07); "seconda fase a gruppi" = colore degli
     * ottavi (decisione Niko 15/07). Le fasi a gruppi iniziali condividono
     * il granata.
     */
    public const COLORI_PERFORMANCE = [
        'campione'              => ['#FFCC00', '#FFCC00'],
        'secondo posto'         => ['#999999', '#999999'],
        'terzo posto'           => ['#CD7F32', '#CD7F32'],
        'quarto posto'          => ['#000099', '#000099'],
        'quarti di finale'      => ['#009900', '#009900'],
        'ottavi di finale'      => ['#FF6600', '#FF6600'],
        'sedicesimi di finale'  => ['#CC0000', '#CC0000'],
        'seconda fase a gruppi' => ['#FF6600', '#FF6600'],
        'fase a gruppi'         => ['#990000', '#990000'],
    ];

    /**
     * Alias team_code (DB) -> nome file geojson, per i codici che
     * differiscono (verificati sul contenuto dei geojson il 15/07).
     */
    public const ALIAS_GEOJSON = [
        'CHN' => 'CHI', // Cina
        'CON' => 'CDR', // Rep. Dem. del Congo
        'CPV' => 'CPO', // Capo Verde
        'EAU' => 'UAE', // Emirati Arabi Uniti
        'KWT' => 'KUW', // Kuwait
        'UCR' => 'UKR', // Ucraina
    ];

    /**
     * Squadre qualificate del torneo (ordinate per nome) con bandiera,
     * performance, colori mappa e disponibilita' del geojson.
     */
    public function squadre(string $tournamentId): array
    {
        $righe = DB::table('awc_qualified_teams')
            ->where('tournament_id', $tournamentId)
            ->orderBy('team_name')
            ->get();

        $annoTorneo = $this->wc->anno($tournamentId);

        return $righe->map(function ($r) use ($tournamentId, $annoTorneo) {
            $colori = self::COLORI_PERFORMANCE[$r->performance] ?? null;
            $geoCode = self::ALIAS_GEOJSON[$r->team_code] ?? $r->team_code;
            $haGeo = is_file(resource_path('geojson/'.$geoCode.'.geojson'));

            return [
                'team_code'   => $r->team_code,
                'team_name'   => $r->team_name,
                'performance' => $r->performance,
                'flag'        => $this->wc->bandieraUrl($r->team_code, $tournamentId),
                // Le card puntano alla scheda squadra-anno (es. /squadra/ITA-1990)
                'squadra_url' => ($r->team_code && $annoTorneo)
                    ? route('squadra_anno.show', ['code' => $r->team_code, 'year' => $annoTorneo])
                    : ($r->team_code ? route('squadra.show', $r->team_code) : null),
                'colori'      => $colori,
                'geojson_url' => $haGeo ? route('geojson', $geoCode) : null,
            ];
        })->all();
    }

    /**
     * Dati per la mappa: {team_code: [geojson_url, colore, bordo, nome, performance]}
     * per le sole squadre con geojson disponibile e colore noto.
     */
    public function mappaPaesi(array $squadre): array
    {
        $out = [];
        foreach ($squadre as $s) {
            if ($s['geojson_url'] && $s['colori']) {
                $out[$s['team_code']] = [
                    $s['geojson_url'], $s['colori'][0], $s['colori'][1],
                    $s['team_name'], $s['performance'],
                ];
            }
        }

        return $out;
    }

    /* ----------------------------------------------------------------- */
    /*  Navigazione torneo precedente / successivo                        */
    /* ----------------------------------------------------------------- */

    /**
     * Torneo adiacente. Solo anni pari. Ritorna [year, tournament_id, url,
     * manifest] o null. (Esclusione del 2026 rimossa: dal 2022 ora compare
     * il Mondiale successivo 2026.)
     */
    public function adiacente(int $anno, string $direzione): ?array
    {
        $q = Tournament::query()->whereRaw('MOD(year, 2) = 0');

        if ($direzione === 'prev') {
            $t = $q->where('year', '<', $anno)->orderByDesc('year')->first();
        } else {
            $t = $q->where('year', '>', $anno)
                ->orderBy('year')
                ->first();
        }

        if (! $t) {
            return null;
        }

        $tid = $t->tournament_id ?: ('WC-'.$t->year);

        return [
            'year'          => $t->year,
            'tournament_id' => $tid,
            'url'           => route('torneo.show', $tid),
            'manifest'      => $this->manifestoUrl($t->year),
        ];
    }
}
