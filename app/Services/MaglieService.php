<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Maglie indossate da una nazionale in un torneo.
 *
 * Nel DB la maglia sta a livello di PARTITA: awc_matches.home_kit / away_kit
 * sono la maglia di chi giocava in casa / in trasferta in quella gara, NON
 * un'etichetta "prima/seconda". Da qui ricaviamo l'insieme distinto delle
 * maglie effettivamente indossate da una squadra in un torneo.
 *
 * Blocco riusabile dalle tre viste:
 *  - squadra-anno: le maglie di questa coppia (con le partite in cui e' stata
 *    indossata ciascuna, linkate alla scheda partita);
 *  - torneo: le stesse maglie per ogni squadra del torneo (solo immagini);
 *  - squadra: le stesse per ogni torneo della nazionale (solo immagini).
 *
 * Il file .gif puo' mancare (walkover SWE-AUT 1938, 32 KO del 2026 non ancora
 * assegnati): quelle partite non hanno maglia nota e vengono saltate.
 */
class MaglieService
{
    public function __construct(protected WcService $wc)
    {
    }

    /**
     * Maglie distinte indossate da $code nel torneo $tid.
     * Ordine "per uso" (scelta di Niko): la piu' indossata per prima; a
     * parita' di partite, prima quella comparsa come maglia della squadra di
     * casa, poi per nome file (deterministico).
     *
     * @return array<int, array{
     *   file:string, url:?string, count:int, casa:bool,
     *   partite: array<int, array{match_id:string, url:string, data:?string,
     *     avversario:?string, avversario_code:?string, avversario_flag:?string,
     *     gf:?int, gs:?int}>
     * }>
     */
    public function perSquadraTorneo(string $code, string $tid): array
    {
        $code = strtoupper($code);

        $partite = DB::table('awc_matches')
            ->where('tournament_id', $tid)
            ->where(function ($q) use ($code) {
                $q->where('home_team_code', $code)->orWhere('away_team_code', $code);
            })
            ->orderBy('match_date')->orderBy('match_time')->orderBy('key_id')
            ->get();

        return $this->raggruppa($partite, $code, $this->wc->anno($tid));
    }

    /**
     * Raggruppa per file-maglia una collezione di partite gia' filtrate su una
     * squadra. Estratto a parte cosi' le viste torneo/squadra possono fare
     * un'unica query sul torneo e richiamarlo per ciascuna squadra.
     *
     * @param  iterable  $partite  righe di awc_matches che coinvolgono $code
     */
    public function raggruppa(iterable $partite, string $code, ?int $anno): array
    {
        $code = strtoupper($code);
        $kit = [];   // file => dati aggregati

        foreach ($partite as $p) {
            $casa = $p->home_team_code === $code;

            // La maglia segue la squadra: se giocava in casa e' la home_kit,
            // altrimenti la away_kit. Vale anche nei 142 casi in cui la fonte
            // (historicalkits) inverte casa/trasferta: l'assegnazione al DB e'
            // gia' corretta, la maglia resta quella indossata dalla squadra.
            $file = trim((string) ($casa ? $p->home_kit : $p->away_kit));
            if ($file === '') {
                continue;   // partita senza maglia nota
            }

            if (! isset($kit[$file])) {
                $kit[$file] = [
                    'file'    => $file,
                    'url'     => $anno ? route('img.kit', ['anno' => $anno, 'file' => $file]) : null,
                    'count'   => 0,
                    'casa'    => false,   // comparsa almeno una volta come home_kit
                    'partite' => [],
                ];
            }

            $hs = $p->home_team_score === null ? null : (int) $p->home_team_score;
            $as = $p->away_team_score === null ? null : (int) $p->away_team_score;
            $avvCode = $casa ? $p->away_team_code : $p->home_team_code;

            $kit[$file]['count']++;
            $kit[$file]['casa'] = $kit[$file]['casa'] || $casa;
            $kit[$file]['partite'][] = [
                'match_id'        => $p->match_id,
                'url'             => route('partita.show', $p->match_id),
                'data'            => $p->match_date ? date('d/m/Y', strtotime((string) $p->match_date)) : null,
                'avversario'      => $casa ? $p->away_team_name : $p->home_team_name,
                'avversario_code' => $avvCode,
                'avversario_flag' => $this->wc->bandieraUrl($avvCode, $anno),
                'gf'              => $casa ? $hs : $as,
                'gs'              => $casa ? $as : $hs,
            ];
        }

        $kit = array_values($kit);

        usort($kit, function ($x, $y) {
            return [$y['count'], (int) $y['casa'], $x['file']]
               <=> [$x['count'], (int) $x['casa'], $y['file']];
        });

        return $kit;
    }

    /**
     * Tutte le squadre di un torneo con le rispettive maglie distinte: una
     * sola query sul torneo, raggruppata per squadra. Squadre in ordine
     * ALFABETICO; dentro ogni blocco le maglie sono ordinate per uso.
     *
     * @return array<int, array{code:string, name:string, flag:?string,
     *   url:?string, kits:array}>
     */
    public function perTorneo(string $tid): array
    {
        $anno = $this->wc->anno($tid);

        $partite = DB::table('awc_matches')
            ->where('tournament_id', $tid)
            ->orderBy('match_date')->orderBy('match_time')->orderBy('key_id')
            ->get();

        // Squadre presenti nelle partite (code => nome visualizzato)
        $nomi = [];
        foreach ($partite as $p) {
            if (! empty($p->home_team_code)) {
                $nomi[$p->home_team_code] = $p->home_team_name;
            }
            if (! empty($p->away_team_code)) {
                $nomi[$p->away_team_code] = $p->away_team_name;
            }
        }

        $out = [];
        foreach ($nomi as $code => $nome) {
            $delSquadra = $partite->filter(
                fn ($p) => $p->home_team_code === $code || $p->away_team_code === $code
            );
            $kits = $this->raggruppa($delSquadra, $code, $anno);
            if (empty($kits)) {
                continue;   // squadra senza maglie note (es. KO 2026 non assegnati)
            }
            $out[] = [
                'code' => $code,
                'name' => $nome ?: $code,
                'flag' => $this->wc->bandieraUrl($code, $anno),
                'url'  => $anno ? route('squadra_anno.show', ['code' => $code, 'year' => $anno]) : null,
                'kits' => $kits,
            ];
        }

        usort($out, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $out;
    }

    /**
     * Tutti i tornei a cui una nazionale ha partecipato, con le maglie
     * distinte per ciascuno. Tornei dal piu' VECCHIO al piu' recente; dentro
     * ogni blocco le maglie sono ordinate per uso.
     *
     * @return array<int, array{tid:string, anno:?int, url:?string, kits:array}>
     */
    public function perSquadra(string $code): array
    {
        $code = strtoupper($code);

        $tids = DB::table('awc_qualified_teams')
            ->where('team_code', $code)
            ->distinct()->pluck('tournament_id')
            ->sortBy(fn ($t) => (int) substr((string) $t, 3))
            ->values();

        $out = [];
        foreach ($tids as $tid) {
            $kits = $this->perSquadraTorneo($code, $tid);
            if (empty($kits)) {
                continue;
            }
            $anno = $this->wc->anno($tid);
            $out[] = [
                'tid'  => $tid,
                'anno' => $anno,
                'url'  => $anno ? route('squadra_anno.show', ['code' => $code, 'year' => $anno]) : null,
                'kits' => $kits,
            ];
        }

        return $out;
    }
}
