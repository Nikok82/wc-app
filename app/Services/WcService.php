<?php

namespace App\Services;

use App\Models\Flag;
use App\Models\Squad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Funzioni condivise per le sezioni giocatore / allenatore / arbitro:
 * risoluzione delle bandiere (awc_flags) e composizione delle righe partita.
 */
class WcService
{
    /** Cache in memoria (per richiesta) di awc_flags. */
    protected ?Collection $flags = null;

    /** Cache mappa nome squadra -> team_code per torneo (da awc_squads). */
    protected array $squadMaps = [];

    /** Cache colonne esistenti per tabella. */
    protected array $columns = [];

    /* ----------------------------------------------------------------- */
    /*  Bandiere                                                          */
    /* ----------------------------------------------------------------- */

    protected function flags(): Collection
    {
        return $this->flags ??= Flag::all();
    }

    /**
     * Estrae l'anno da un tournament_id tipo "WC-1938" (o da un intero).
     */
    public function anno(int|string|null $tournamentId): ?int
    {
        if ($tournamentId === null) {
            return null;
        }
        if (preg_match('/(\d{4})/', (string) $tournamentId, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Trova il file bandiera (es. "ESP-1") per un riferimento che puo'
     * essere un team_code (ESP) o un nome paese/squadra (Spagna),
     * valido per l'anno del torneo: start <= anno <= end.
     *
     * Ritorna il nome file SENZA estensione, o null se non trovato.
     */
    public function bandiera(?string $rif, int|string|null $tournamentId): ?string
    {
        $rif = trim((string) $rif);
        if ($rif === '') {
            return null;
        }

        $anno = $this->anno($tournamentId);

        $candidati = $this->flags()->filter(function ($f) use ($rif) {
            return strcasecmp((string) $f->team_code, $rif) === 0
                || strcasecmp((string) $f->team_name, $rif) === 0;
        });

        if ($candidati->isEmpty()) {
            return null;
        }

        // Se conosciamo l'anno, scegliamo la bandiera in corso in quell'anno.
        if ($anno !== null) {
            $match = $candidati->first(function ($f) use ($anno) {
                $start = is_numeric($f->start ?? null) ? (int) $f->start : 0;
                $end   = is_numeric($f->end ?? null) ? (int) $f->end : 9999;

                return $anno >= $start && $anno <= $end;
            });

            if ($match) {
                return $match->flag ?: null;
            }
        }

        // Fallback (o anno ignoto): la bandiera piu' recente disponibile.
        $recente = $candidati->sortByDesc(fn ($f) => is_numeric($f->end ?? null) ? (int) $f->end : 9999)->first();

        return $recente->flag ?: null;
    }

    /** URL pubblico dell'immagine bandiera (o null). */
    public function bandieraUrl(?string $rif, int|string|null $tournamentId): ?string
    {
        $file = $this->bandiera($rif, $tournamentId);

        return $file ? route('img', ['tipo' => 'flags', 'file' => $file.'.png']) : null;
    }

    /* ----------------------------------------------------------------- */
    /*  Squadre di un torneo (awc_squads)                                 */
    /* ----------------------------------------------------------------- */

    /**
     * Mappa "nome squadra" -> team_code per un torneo, da awc_squads.
     */
    public function mappaSquadre(?string $tournamentId): array
    {
        $key = (string) $tournamentId;

        if (! array_key_exists($key, $this->squadMaps)) {
            $this->squadMaps[$key] = Squad::where('tournament_id', $tournamentId)
                ->get()
                ->filter(fn ($s) => ! empty($s->team_name) && ! empty($s->team_code))
                ->pluck('team_code', 'team_name')
                ->all();
        }

        return $this->squadMaps[$key];
    }

    /* ----------------------------------------------------------------- */
    /*  Riga partita: "(bandiera) Italia - Olanda (bandiera)" + link      */
    /* ----------------------------------------------------------------- */

    /**
     * Scompone $match_name nelle due squadre, risolve le bandiere e
     * indica quale delle due va in grassetto (home_team / away_team = 1
     * sulla riga di appearance).
     */
    public function celleMatch(?string $matchName, ?string $tournamentId, $homeTeam = null, $awayTeam = null): array
    {
        $matchName = trim((string) $matchName);
        $mappa = $this->mappaSquadre($tournamentId);

        // Separatore: nel DB e' il trattino senza spazi (es. "Messico-URSS"),
        // ma alcuni nomi squadra contengono il trattino (es. "Bosnia-Herzegovina").
        // Percio' proviamo prima a riconoscere i nomi reali delle squadre del torneo.
        $a = $b = null;

        if (str_contains($matchName, ' - ')) {
            [$a, $b] = array_pad(explode(' - ', $matchName, 2), 2, '');
        } else {
            $candidato = null;
            foreach (array_keys($mappa) as $nome) {
                if (str_starts_with($matchName, $nome.'-')) {
                    $resto = substr($matchName, strlen($nome) + 1);
                    if (isset($mappa[$resto])) {   // entrambe squadre note: match sicuro
                        [$a, $b] = [$nome, $resto];
                        break;
                    }
                    $candidato ??= [$nome, $resto];
                }
            }
            if ($a === null && $candidato) {
                [$a, $b] = $candidato;
            }
            if ($a === null) {                     // fallback: primo trattino
                [$a, $b] = array_pad(explode('-', $matchName, 2), 2, '');
            }
        }

        $a = trim((string) $a);
        $b = trim((string) $b);

        $flagA = $this->bandieraUrl($mappa[$a] ?? $a, $tournamentId);
        $flagB = $this->bandieraUrl($mappa[$b] ?? $b, $tournamentId);

        return [
            'casa'      => ['nome' => $a, 'flag' => $flagA, 'grassetto' => (int) $homeTeam === 1],
            'trasferta' => ['nome' => $b, 'flag' => $flagB, 'grassetto' => (int) $awayTeam === 1],
        ];
    }

    /* ----------------------------------------------------------------- */
    /*  Utilita'                                                          */
    /* ----------------------------------------------------------------- */

    /** Controlla (con cache) se una colonna esiste davvero nella tabella. */
    public function haColonna(string $tabella, string $colonna): bool
    {
        $this->columns[$tabella] ??= Schema::getColumnListing($tabella);

        return in_array($colonna, $this->columns[$tabella], true);
    }

    /**
     * Estrae gli anni (o i tournament id) da una stringa tipo
     * "WC-1934, WC-1938" oppure "1934, 1938".
     */
    public function anniDaLista(?string $lista): array
    {
        if (! $lista) {
            return [];
        }
        preg_match_all('/(?:WC-)?(\d{4})/', $lista, $m);

        return array_values(array_unique($m[1]));
    }
}
