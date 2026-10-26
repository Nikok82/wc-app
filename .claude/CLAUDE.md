# WC App — istruzioni per Claude

Storia completa dei Mondiali di calcio FIFA. Online su https://wch.nikok.it (VPS IONOS con Plesk).
Proprietario e sviluppatore: Niko. Rispondi in italiano.

## Regola d'oro: mai push su `main`

Plesk fa il deploy automatico di `main` tramite webhook GitHub: un push su `main` va online subito.

- Lavora sempre su un ramo `claude/<argomento>`.
- Alla fine apri una Pull Request verso `main` con descrizione in italiano.
- Il merge lo fa solo Niko (anche da telefono). Non fare mai merge da solo.
- Se una modifica richiede comandi sul server (migrazioni, `php artisan optimize:clear`, ecc.),
  scrivilo in evidenza nella PR: il deploy automatico fa solo il pull del codice.
  Sul server: `sudo -u nikok /opt/plesk/php/8.3/bin/php artisan ...` in `/var/www/vhosts/nikok.it/wch.nikok.it`.

## Stack

- Laravel 12, PHP 8.3, MariaDB 10.5, Blade, JavaScript vanilla, Filament 3 per l'admin.
- Database: oltre 34 tabelle con prefisso `awc_`. Frontend in sola lettura con ricerca; dati inseriti da Filament.
- Composer: in produzione si usa solo `install` da `composer.lock`. Non lanciare mai `composer update`.

## Database: regole tassative

- Il database è PREESISTENTE. Non creare né modificare tabelle con le migration senza conferma
  esplicita di Niko.
- Per modifiche allo schema: prepara uno script SQL separato, spiegalo, e lascia che lo esegua Niko.
- NON lanciare comandi distruttivi (`migrate:fresh`, `migrate:refresh`, `db:wipe`, `DROP`) per nessun motivo.
- Usa sempre Eloquent o query preparate, mai SQL costruito concatenando stringhe.

## Verifiche prima di aprire una PR

Nel cloud non c'è il database. Prima di ogni PR esegui:

1. `composer install --no-interaction --no-scripts` (se manca `vendor/`), poi `cp .env.example .env && php artisan key:generate`.
2. `php -l` su ogni file PHP modificato.
3. `php artisan view:cache` (compila tutti i Blade) e `php artisan route:list`.
4. `node --check` sui file JavaScript modificati.
5. Caratteri non ASCII negli identificatori PHP: `LC_ALL=C grep -rnP '[\x80-\xFF]' <file>`
   (una `е` cirillica in un nome di metodo è già successa: PHP la accetta in silenzio).

Non committare `.env`, `vendor/`, dump `.sql`.

## Vincoli permanenti

- Il sito NON deve essere indicizzato (noindex via header Apache su Plesk). Non aggiungere sitemap,
  meta di indicizzazione o altro che lo esponga ai motori di ricerca.
- I frammenti delle tab arrivano via `fetch` e gli script dentro `innerHTML` non vengono eseguiti:
  per le fisarmoniche usare `<details>`, non JavaScript.
- Swipe fra schede (A1): verso sinistra si va alla scheda *precedente*; invertibile scambiando
  `prev`/`next` in `partials/swipe-schede.blade.php`.
- `awc_clubs.club_name` = titolo attuale della pagina Wikipedia; `awc_squads.team_past` = nome usato
  in quell'anno. Che differiscano è corretto: non normalizzarli.
- Club apparentemente duplicati: veri duplicati (stesso club, due ID, stesso logo) → unire;
  nomi d'epoca e fusioni (Kispesti/Honvéd, Sampdoria) → tenere entrambi e collegarli, mai unire;
  omonimi (Rangers scozzesi/cileni) → non toccare.
- Tabellone eliminazione diretta: un solo albero dati condiviso ("vincente di → partita successiva"),
  disegnato da due componenti separati. Mai due fonti dati.

## Regole di visualizzazione

- Rigori: "(4-3 d.c.r.)" sotto il risultato dei supplementari; supplementari: "(2-1 d.t.s.)" sotto il regolamentare.
- 1934/38: risultato originale con sotto il replay, etichetta "dopo replay match".
- Finale 3° posto: ramo laterale separato.

## Ricerca globale

Massimo 10 risultati per pagina; anni solo a 4 cifre o ultime 2 (non cifra singola); `#numero` richiede
nazione o anno; attiva dal 1954 (1930–1950 vuoto con avviso); ordine: Torneo → Squadra → Squadre-anno →
Partite → Giocatori → Allenatori → Arbitri → Stadi → Club.

## Glossario

- Schede operative: squadra, squadra-anno, torneo.
- Schede dimostrative: partita, giocatore, allenatore, arbitro, stadio.

## Stemmi dei club

- File in `resources/images/clubs/C-<club_id>.png`, PNG 64×64 con sfondo trasparente.
- Conversione con `tools/prepara_loghi_club.py` (istruzioni in `guida prepara-loghi-club.md`):
  legge `loghi_da_convertire/` (cartella di lavoro, esclusa da git) e scrive PNG con lo stesso nome del file
  d'origine. Formati accettati: PNG, JPG, WEBP, GIF, BMP, TIFF (non SVG).

## Archivio

`docs/archivio/` contiene patch e appunti del vecchio flusso (patch applicate a mano da PowerShell).
Sono solo storico: non vanno riapplicate.
