<?php

/**
 * Nomi d'epoca dei club scritti a mano (16/08).
 *
 * Di norma i nomi d'epoca vengono ricavati da soli: sono i valori di
 * awc_squads.team_past diversi dal nome nel catalogo, cioe' come il club
 * era chiamato nell'anno di quella convocazione. Esempio: il club a
 * catalogo e' "JS Kabylie", ma le rose del 1982 dicono "JE Tizi-Ouzou".
 *
 * Qui dentro finiscono le eccezioni: quando la dicitura ricavata in
 * automatico e' imprecisa o incompleta, si scrive quella giusta e prende
 * il posto dell'altra. Serve soprattutto per le fusioni, che il database
 * non puo' sapere: la Sampdoria nasce nel 1946 da Andrea Doria e
 * Sampierdarenese, e nessuna colonna lo dice.
 *
 * Chiave: id del club in awc_clubs. Valore: il testo, gia' pronto.
 * Una stringa vuota nasconde del tutto la riga per quel club.
 */

return [
    // 1310 => 'MP Alger (1977-1987)',
    // 85   => 'nata nel 1946 dalla fusione di Andrea Doria e Sampierdarenese',
];
