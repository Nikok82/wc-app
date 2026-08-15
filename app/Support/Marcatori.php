<?php

namespace App\Support;

/**
 * Aggregazione dei marcatori di una partita (B2 del todo 15/08).
 *
 * Le due sorgenti di gol del progetto hanno forme leggermente diverse:
 *   - WcService::golPerPartite()          -> 'nota' gia' calcolata ('aut.'/'rig.')
 *   - TorneoPartiteService::golPartita()  -> 'own_goal' / 'penalty' booleani
 * Qui vengono normalizzate entrambe e le reti dello stesso giocatore nella
 * stessa partita finiscono in una voce sola, con i minuti in fila:
 *
 *   15', 41', 44' (rig.), 72', 75'  [bandiera]  O. Salenko
 *
 * L'annotazione del rigore resta attaccata al minuto a cui si riferisce.
 * Unica eccezione all'accorpamento: rete regolare e autogol dello stesso
 * giocatore restano due voci separate, perche' sono accreditate a squadre
 * diverse e sommarle sarebbe scorretto.
 */
class Marcatori
{
    /**
     * @param  iterable  $gol  righe grezze dei marcatori di UNA partita
     * @return array  voci aggregate, nell'ordine di comparsa (cioe' di minuto):
     *                ['player_id','nome','flag','team_code','autogol'(bool),
     *                 'minuti' => [['minuto' => "44'", 'nota' => 'rig.'|null], ...]]
     */
    public static function aggrega($gol): array
    {
        $voci = [];

        foreach ($gol ?? [] as $g) {
            $g = (array) $g;

            $nota = $g['nota'] ?? null;
            $autogol = ! empty($g['own_goal']) || $nota === 'aut.';
            $rigore  = ! empty($g['penalty']) || $nota === 'rig.';

            // Chiave: giocatore + tipo di rete. Senza player_id (dati vecchi
            // o incompleti) si ripiega sul nome, che dentro una singola
            // partita e' comunque discriminante a sufficienza.
            $chiave = ($g['player_id'] ?? ('n:'.($g['nome'] ?? ''))).'|'.($autogol ? 'a' : 'r');

            if (! isset($voci[$chiave])) {
                $voci[$chiave] = [
                    'player_id' => $g['player_id'] ?? null,
                    'nome'      => $g['nome'] ?? '',
                    'flag'      => $g['flag'] ?? null,
                    'team_code' => $g['team_code'] ?? null,
                    'autogol'   => $autogol,
                    'minuti'    => [],
                ];
            }

            $voci[$chiave]['minuti'][] = [
                'minuto' => $g['minuto'] ?? '',
                'nota'   => $rigore ? 'rig.' : null,
            ];
        }

        return array_values($voci);
    }

    /**
     * Minuti di una voce gia' aggregata, pronti da stampare:
     * "15', 41', 44' (rig.), 72', 75'".
     */
    public static function minuti(array $voce): string
    {
        return implode(', ', array_map(
            fn ($m) => trim($m['minuto'].($m['nota'] ? ' ('.$m['nota'].')' : '')),
            $voce['minuti']
        ));
    }
}
