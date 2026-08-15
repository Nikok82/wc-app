<?php

namespace App\Support;

/**
 * Lettura della stringa digitata nella ricerca globale (A3 del todo 15/08).
 *
 * Regole decise da Niko il 15/08:
 *
 *  - l'ANNO si scrive per intero (1990) o con le ultime due cifre (90), e si
 *    riferisce sempre al torneo. Una cifra sola NON e' un anno: "Italia 9"
 *    resta testo e quindi non trova niente;
 *  - "#" seguito da un numero cerca chi ha indossato quel numero di maglia.
 *    Il numero da solo non basta: serve una nazione o un anno. L'ordine
 *    delle parole e' indifferente, quindi "#9 Italia" e "Italia #9" sono la
 *    stessa domanda;
 *  - la ricerca per numero vale dal 1954 in poi: prima di allora i numeri
 *    non stanno nel database (1930, 1934, 1938 e 1950) e non vanno inventati.
 *
 * La classe si limita a smontare la stringa; a decidere cosa interrogare
 * pensa RicercaService.
 */
class RicercaQuery
{
    /** Prima edizione con i numeri di maglia nel database. */
    public const ANNO_MAGLIE = 1954;

    /** Testo libero rimasto dopo aver tolto anni e numero di maglia. */
    public string $testo = '';

    /** Anni scritti per intero (es. 1990). */
    public array $anniPieni = [];

    /** Anni scritti con due cifre (es. "90" -> "90"), da risolvere dopo. */
    public array $anniBrevi = [];

    /** Numero di maglia richiesto con "#", oppure null. */
    public ?int $maglia = null;

    public static function leggi(string $q): self
    {
        $r = new self();

        $q = trim(preg_replace('/\s+/u', ' ', $q));
        if ($q === '') {
            return $r;
        }

        // "#10" attaccato o staccato: "#10", "# 10", "Italia#10"
        $q = preg_replace('/#\s+(\d)/u', '#$1', $q);

        $parole = [];
        foreach (explode(' ', $q) as $t) {
            if ($t === '') {
                continue;
            }

            if (preg_match('/^#(\d{1,3})$/u', $t, $m)) {
                $r->maglia = (int) $m[1];
                continue;
            }

            // Numero di maglia attaccato a una parola: "Italia#10"
            if (preg_match('/^(.+?)#(\d{1,3})$/u', $t, $m)) {
                $r->maglia = (int) $m[2];
                $parole[] = $m[1];
                continue;
            }

            if (preg_match('/^\d{4}$/u', $t)) {
                $r->anniPieni[] = (int) $t;
                continue;
            }

            if (preg_match('/^\d{2}$/u', $t)) {
                $r->anniBrevi[] = $t;
                continue;
            }

            // Una cifra sola non e' un anno: resta testo e non trovera' nulla.
            $parole[] = $t;
        }

        $r->testo = trim(implode(' ', $parole));

        return $r;
    }

    /** true se non c'e' niente da cercare. */
    public function vuota(): bool
    {
        return $this->testo === ''
            && empty($this->anniPieni)
            && empty($this->anniBrevi)
            && $this->maglia === null;
    }

    /** true se e' una ricerca per numero di maglia. */
    public function perMaglia(): bool
    {
        return $this->maglia !== null;
    }

    /**
     * Una ricerca per maglia e' valida solo se accompagnata da una nazione
     * (testo) o da un anno. Il numero da solo non da' risultati.
     */
    public function magliaValida(): bool
    {
        return $this->perMaglia()
            && ($this->testo !== '' || $this->anniPieni || $this->anniBrevi);
    }

    /** true se c'e' almeno un anno scritto in qualche forma. */
    public function haAnno(): bool
    {
        return ! empty($this->anniPieni) || ! empty($this->anniBrevi);
    }
}
