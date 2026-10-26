# WC App — regole fisse per Claude

## Git e deploy
- MAI push su `main`: ogni push su main va online in automatico (webhook GitHub → Plesk su wch.nikok.it).
- MAI merge di Pull Request: il merge lo fa solo Niko.
- Lavorare sempre su rami `claude/...` e aprire una PR verso main, spiegata in italiano semplice
  (cosa era rotto, cosa cambia, se dopo il merge serve un comando sul server).
- Non riscrivere la storia dei rami (niente rebase/force-push su rami non creati da Claude).

## Codice
- App in sottocartella: usare sempre `url()`, `route()`, `asset()`; niente `route:cache`/`config:cache`
  (l'app legge `.env` a runtime).
- Frammenti caricati via fetch: niente `<script>` dentro; l'inizializzazione va in `public/js/wc.js`.
  I frammenti possono contenere `<style>`.
- Grafici: SVG statico generato lato server (`app/Support/Charts.php`).
- Identificatori PHP (variabili, metodi, classi) solo in ASCII; gli accenti vanno bene in stringhe e commenti.
- Mai caricare file nella cartella di deploy fuori da Git.

## Lavoro con Niko
- Un passo alla volta con conferma, salvo "vai avanti".
- Le cancellazioni definitive (file, dati, DB) le esegue Niko.
- Il database non è disponibile nell'ambiente di Claude: se una correzione richiede il DB
  o una scelta di Niko, descriverla senza toccarla.
