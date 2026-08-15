# WC App — Registro dei sospesi (11/08/2026)

Fotografia completa di **tutto ciò che resta da fare** e di **tutte le domande a cui Niko non ha ancora risposto**, con il contesto di ciascuna. Sostituisce il tracciamento sparso fra `todo-richieste-09-08.md` e la chat.

**Stato ambiente**: DB importato nel container (dump `wc_dump_claude.sql`), MariaDB + PHP 8.3 + `vendor/` + `.env` funzionanti → si può renderizzare e verificare, non si lavora alla cieca. Pagine historicalkits 2010 e 2018 ricevute e valide (contengono gli `src` delle gif).

---

# PARTE 1 — DA FARE

## Fase 1 · Mappe geojson — *diagnosi già completate, pronta a partire*

| # | Problema | Causa accertata | Stato |
|---|---|---|---|
| 1.1 | Bosnia senza mappa | `BOS.geojson` ha un refuso JSON (`""geometry":`) → il file non si parsa | [Certo] |
| 1.2 | **Albania senza mappa** | Stesso identico refuso in `ALB.geojson`. **Non segnalata da Niko**, trovata dal controllo sistematico | [Certo] |
| 1.3 | Irlanda: mappa presente ma il paese non c'è | `IRL.geojson` ha `"geometry": null` | [Certo] |
| 1.4 | **Sudan: stesso difetto** | `SUD.geojson` ha `"geometry": null`. Non segnalato da Niko | [Certo] |
| 1.5 | La Francia mostra gli Stati Uniti · la Francia manca in tutte le mappe torneo | `FRA.geojson` **contiene la geometria degli USA** (bbox identico, byte quasi identici) | [Certo] |
| 1.6 | Spagna mancante nella mappa 2026 | Probabile conseguenza di 1.5: il poligono USA include l'Alaska oltre l'antimeridiano e genera una fascia che copre l'Europa | [Probabile] — da riverificare dopo il fix 1.5 |
| 1.7 | Russia/URSS senza la Čukotka e senza le isole del nord · Nuova Zelanda non visibile nella sua mappa · USA e Russia/URSS non centrati | **Causa unica**: `RUS`, `USR`, `NZL`, `USA` hanno bounding box da −180 a +180 perché il territorio attraversa l'antimeridiano. Una sola normalizzazione delle longitudini risolve tutti e quattro i sintomi | [Certo] |
| 1.8 | Canada non centrato | Diverso da 1.7: non attraversa l'antimeridiano, è l'estremo nord (83° N) che allarga il bbox | [Certo] |
| 1.9 | Marocco include il Sahara Occidentale | Il geojson arriva a 20.8° di latitudine invece di ~27.7° | [Certo] |
| 1.10 | 32 paesi troppo zoomati | Elenco di Niko: Uruguay, Ungheria, Tunisia, Trinidad e Tobago, Togo, Svizzera, Slovenia, Slovacchia, Senegal, Qatar, Paraguay, Panama, Paesi Bassi, Kuwait, Islanda, Irlanda del Nord, Honduras, Haiti, Jamaica, Galles, Emirati Arabi Uniti, El Salvador, Danimarca, Curaçao, Cuba, Costa Rica, Cechia, Capo Verde, Bulgaria, Bolivia, Belgio, Angola | Vedi **domanda D7** |

## Fase 2 · Pagina squadra

- **2.1** Sezione nomi nelle varie lingue: da singoli div a **riga unica** con sequenza `bandiera nome icona-wiki • bandiera nome icona-wiki • …`, a capo libero ma **senza mai spezzare** la terna bandiera-nome-wiki.
- **2.2** Bandiere nella barra dei comandi: **tonde**, diametro = altezza degli altri bottoni.
- **2.3** Rimuovere le bandiere con link in fondo alla pagina squadre.
- **2.4** Tab a icona (come quelle del torneo), tutte su **una riga sola**. → vedi **domanda D1**.

## Fase 3 · Torneo

- **3.1** Fix delle **3 icone** dei sotto-bottoni della tab "Risultati"/bracket (1STEP / albero / ALL). Era già la Fase E del todo precedente.
- **3.2** Icone dei tornei nella barra del footer: **quadrate**, lato = altezza degli altri tasti.
- **3.3** Le 10 tab torneo (Info · Stadi · Partite · Squadre · Managers · Arbitri · Classifica · Record · Marcatori · Maglie) su **una riga sola**.

## Fase 4 · Maglie

- **4.1** Togliere lo sfondo grigio. **Non è CSS**: il grigio `#CCCCCC` è cotto dentro le gif (palette, nessuna trasparenza). Servono ~1.100 file lavorati in batch con flood-fill dai bordi (non sostituzione globale del colore, altrimenti spariscono le righe grigie legittime dentro le maglie). → vedi **domanda D9**.
- **4.2** Maglie 2010 e 2018 assenti: `home_kit`/`away_kit` sono **a NULL su 64 partite su 64** in entrambi i tornei — le gif ci sono (69 e 64 file), manca il matching. Le pagine historicalkits sono ora disponibili → si rigenera l'SQL.

## Fase 5 · Contenuti

- **5.1** Partite 2026 doppie. **Non è un problema di dati**: nel DB ci sono 104 partite con 104 `match_id` distinti, che è il numero corretto (72 gironi + 32 KO). È un bug di rendering.
- **5.2** **Tab Record** (ex Fase D): ~13 record ognuno con link alla partita — giocatore più vecchio/giovane, età media, marcatore più vecchio/giovane, ammoniti, espulsi, rigori segnati/subiti, autogol fatti/subiti, top-3 goleador, miglior portiere. Età = **al momento della partita**. Per squadra-anno + squadra; per il torneo uguale a squadra ma **escluso** il top-goleador. → vedi **domande D5a, D5b, D5c**.
- **5.3** **Tab Partite responsive** (ex Fase E): riprendere la grafica del vecchio tema WP. → vedi **domanda D11**.

## Fase 6 · Coda dai todo precedenti — *aperti, non citati nell'ultima richiesta*

- **6.1** Bracket-32 per il 2026: scelta fra le 4 varianti già pronte su `?opt32=N`. → **domanda D6**.
- **6.2** Barra bottoni history **Fase 3**: schede stadi/arbitri/giocatori/manager con icona, + prev/next per **squadra-anno**.
- **6.3** Loghi club nelle schede giocatore (l'implementazione a video, distinta dal reperimento dei file). → legata a **domanda D2**.
- **6.4** Rifiniture home.

## Fase 7 · Dati e deploy — *lato Niko, non codice*

- **7.1** Import in prod (phpMyAdmin), nell'ordine: `aggiorna_solo_awc.sql` → `d2_maglie_ko_2026.sql` → `UPDATE awc_matches SET match_date='2026-06-16' WHERE match_id='M-2026-15';`
- **7.2** Formazioni 2026 a zero: manca l'import di **fase4 v2** (+ fase3b e fase6, in `import_2026`). Consiglio già dato: importarli in locale e rigenerare **un unico** `aggiorna_solo_awc.sql` completo.
- **7.3** 19 bandiere arbitri da disegnare + SQL da lanciare (vedi `bandiere-mancanti.md`).
- **7.4** 538 stemmi club da reperire (vedi `stemmi-club-mancanti.md`).
- **7.5** Webhook GitHub→Plesk per evitare Estrai/Implementa manuali. Opzionale.

---

# PARTE 2 — DOMANDE APERTE

> Le prime tre sono ferme da tre messaggi e bloccano l'inizio dei lavori.

### D1 · Icone delle tab squadra — *blocca la Fase 2.4*
Proposta, costruita sulle 46 icone già presenti in `Icons.php` e coerente col torneo:

| Tab | Icona proposta |
|---|---|
| Info | `info` |
| Partite | `scoreboard` (come nel torneo) |
| Presenze | `competition` |
| Giocatori | `team` |
| Managers | `strategy` (come nel torneo) |
| Maglie | SVG maglia inline (lo stesso del torneo) |
| Risultati | `chart` |

E per **squadra-anno**: Info → `info`, Partite → `scoreboard`, Convocati → `team`, Maglie → SVG maglia, Record → `trophy`.
**Confermi, o cambi qualcosa?**

### D2 · Stemmi club: file locali o URL esterni? — *blocca la Fase 6.3*
Oggi `awc_clubs.logo` contiene **1342 hotlink a `cdn.soccerwiki.org`**, usati tali e quali come `src`. Se quel sito cambia i percorsi o blocca il referer, spariscono tutti insieme. Proposta: colonna con **solo il nome file**, cartella `resources/images/clubs/`, nome `{club_id}.png` (es. `C-1.png`), rotta `/img/clubs/{file}` gemella di quella delle maglie.
**Passiamo al locale per tutti, solo per i 538 nuovi, o restiamo com'è?**

### D3 · Consegna
**Confermi una patch `git format-patch` per fase**, come sempre?

### D4 · Sigla per il Niger — *serve per completare `bandiere-mancanti.md`*
Ho proposto **`NGE`** e non `NIG` perché **nel tuo DB `NIG` è già la Nigeria** (`NIG.geojson` ha il bbox nigeriano). La FIFA usa l'opposto (NIG = Niger, NGA = Nigeria).
**Tieni `NGE`, o preferisci rinominare la Nigeria in `NGA` e liberare `NIG`?** (la seconda tocca dati esistenti).

### D5 · Tab Record — *tre definizioni mancanti, bloccano la Fase 5.2*
- **D5a** — Cosa intendi per **"età media"**: media dei convocati, o media degli undici schierati partita per partita?
- **D5b** — Cosa definisce il **"miglior portiere"**: meno gol subiti in assoluto, meno gol subiti per partita, o numero di clean sheet?
- **D5c** — I dati di cartellini e sostituzioni **esistono solo dal 1970**. Confermi che per i tornei precedenti quei record restino **vuoti** (invece di essere nascosti o marcati "dato non disponibile")?

### D6 · Bracket a 32 per il 2026 — *aperta da settimane*
Quattro varianti già implementate e provabili con `?opt32=N`: **1** solo turni · **2** albero completo scrollabile · **3** albero con i primi turni compattati · **4** ibrido.
**Quale?**

### D7 · Zoom delle mappe: correzione mirata o globale?
Hai elencato 32 paesi da zoomare "3 misure in meno". Sono tutti paesi piccoli: propongo un **tetto globale al `maxZoom` del `fitBounds`** invece di una tabella con 32 righe — copre la tua lista e anche i casi futuri, senza manutenzione. Se poi qualcuno resta storto si aggiunge l'eccezione singola.
**Ok all'approccio globale?**

### D8 · Sahara Occidentale
Hai scritto "penso sia meglio escluderlo". **Confermi l'esclusione?**

### D9 · Maglie trasparenti: sovrascrivo le gif o affianco dei PNG?
Due strade: **(a)** riscrivere le ~1.100 gif con il colore di sfondo marcato trasparente — zero modifiche al codice, ma i file originali vengono sostituiti (recuperabili solo dalla storia git); **(b)** generare PNG con canale alfa a fianco e cambiare la rotta `/img/kits/…` per preferirli — non distruttivo ma raddoppia i file e tocca il codice.
**Quale preferisci?**

### D10 · Tab Partite responsive: quale riferimento grafico?
La spec dice "riprendere la grafica del vecchio tema". Nei file di progetto ho `claude_riferimenti-tornei_*`.
**Ti basta che mi regoli su quelli, o hai in mente un layout diverso da descrivermi?**

### D11 · Animazione di transizione fra i turni del bracket — *rimandata, non dimenticata*
Non è deducibile dagli screenshot statici di FotMob. Andrà descritta da te (durata / tipo di movimento) **quando si arriverà alla fase di styling**; nel frattempo si approssima con transizioni CSS standard. Nessuna azione richiesta ora.

---

# PARTE 3 — ORDINE PROPOSTO

1. **Fase 1** (mappe) — indipendente da tutte le domande tranne **D7** e **D8**.
2. **Fase 4.2** (maglie 2010/2018) — le pagine ci sono, non serve rispondere a nulla.
3. **Fase 2 + 3** (impaginazione e icone) — servono **D1** e **D3**.
4. **Fase 5.1** (partite 2026 doppie) — indipendente.
5. **Fase 4.1** (maglie trasparenti) — serve **D9**.
6. **Fase 5.2** (Record) — servono **D5a/b/c**.
7. **Fase 5.3** (Partite responsive) — serve **D10**.
8. Coda: **6.1** (serve D6), **6.2**, **6.3** (serve D2), **6.4**.

**Con un solo messaggio che risponde a D1, D3, D7, D8 si sbloccano i primi quattro blocchi.**
