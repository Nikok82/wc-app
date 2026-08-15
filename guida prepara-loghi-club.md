# Guida — prepara loghi club

Come aggiungere gli stemmi dei club al sito, dal file scaricato da
internet fino alla verifica finale sul database.

Vale sia per i 538 stemmi oggi mancanti, sia per quelli che serviranno in
futuro quando entreranno nuove rose.

---

## A cosa serve

Gli stemmi dei club compaiono nelle schede giocatore, accanto al nome
della squadra di provenienza: a 16 pixel nella tab Convocati della
pagina squadra-anno, a 18 pixel nella tab Giocatori della pagina
squadra.

Gli stemmi che si trovano in giro hanno formati, misure e sfondi tutti
diversi. Lo strumento li uniforma: **PNG quadrati da 64 pixel di lato,
sfondo trasparente, stemma centrato**. La misura 64 copre gli schermi ad
alta densità senza appesantire il repository.

Il database non contiene l'immagine, ma solo il **nome del file**. La
corrispondenza è data dall'identificativo del club: il club `C-631`
(la SPAL) usa il file `C-631.png`.

---

## Cosa serve avere installato

Solo Pillow, la libreria che manipola le immagini. Una volta sola:

```
py -m pip install Pillow
```

Se risponde che è già installata, si va avanti.

---

## Passo 1 — Prepara le cartelle

Da PowerShell, nella radice del progetto (`D:\PHP\htdocs\WC\mia-app`):

```
mkdir loghi_da_convertire
```

Questa è la cartella di lavoro: ci si mettono le immagini così come si
trovano, senza sistemarle. Non fa parte del sito e non finisce su git.

La cartella di destinazione, `resources\images\clubs`, esiste già.

---

## Passo 2 — Trova gli identificativi dei club

Apri **`stemmi-club-mancanti.md`**. La tabella elenca tutti i club senza
stemma, ordinati per nazione, con:

- `club_id` — l'identificativo, per esempio `C-631`
- `File da creare` — il nome esatto da usare, per esempio `C-631.png`
- Club e Nazione — per sapere quale stemma cercare
- `Conv.` — quanti giocatori mostrerebbero quello stemma

In fondo al file c'è anche una **top 40 per impatto**: se vuoi partire
dai club che si vedono di più, comincia da lì.

---

## Passo 3 — Scarica le immagini

Cerca lo stemma di ogni club e salvalo dentro `loghi_da_convertire`.

Va bene **qualsiasi formato comune**: PNG, JPG, WEBP, GIF, BMP, TIFF.
Va bene sia lo sfondo bianco sia quello già trasparente. Non serve
ritagliare, ridimensionare o rendere quadrata l'immagine: fa tutto lo
strumento.

**L'unica cosa che conta è il nome.** Rinomina ogni file con
l'identificativo del club, mantenendo l'estensione originale:

```
C-631.webp
C-274.jpg
C-1105.png
```

Se sbagli un identificativo, lo stemma finirà sul club sbagliato: è
l'unico errore che lo strumento non può accorgersi di fare al posto tuo.

Suggerimento pratico: conviene lavorare a gruppi di venti o trenta per
volta, così se qualcosa non va lo scopri subito.

---

## Passo 4 — Converti

```
py prepara_loghi_club.py
```

Legge tutto quello che trova in `loghi_da_convertire` e scrive i PNG
finiti in `resources\images\clubs`.

Stampa una riga per ogni file, per esempio:

```
C-631.webp                         ok (330x479 -> 64x64, sfondo gia' trasparente)
C-274.jpg                          ok (200x200 -> 64x64, 18240 pixel di sfondo rimossi)
---
Convertiti: 2   non riusciti: 0
```

Il nome resta lo stesso, cambia solo l'estensione: `C-631.webp` diventa
`C-631.png`.

### Se serve cambiare cartelle o misura

Si possono passare come parametri:

```
py prepara_loghi_club.py cartella_ingresso cartella_uscita
py prepara_loghi_club.py cartella_ingresso cartella_uscita 128
```

Il terzo parametro è il lato in pixel. Serve solo se un giorno gli
stemmi verranno mostrati più grandi di adesso.

### Il warning su `getdata`

Se compare un messaggio che parla di `getdata is deprecated`, ignoralo:
è Pillow che avvisa di un metodo che verrà tolto nel 2027. Non cambia il
risultato.

---

## Passo 5 — Controlla il risultato

Apri qualche PNG in `resources\images\clubs` e verifica che:

- sia **quadrato**, 64 per 64
- lo stemma sia **centrato**, senza deformazioni
- lo sfondo sia **a scacchi**, cioè trasparente, e non bianco pieno

Se uno stemma esce storto, la causa quasi sempre è una di queste:

| Cosa vedi | Perché | Cosa fare |
|---|---|---|
| Sfondo bianco rimasto | L'originale ha una cornice o un bordo colorato che tocca i lati, quindi il bianco non è collegato al perimetro | Ritaglia a mano la cornice e riconverti |
| Pezzi bianchi dello stemma spariti | Le zone bianche toccavano il bordo dell'immagine | Aggiungi qualche pixel di margine attorno all'originale e riconverti |
| Stemma minuscolo al centro | Non è un errore: lo strumento ritaglia i margini vuoti, quindi se è piccolo l'originale era a bassa risoluzione | Cerca una versione più grande |

Quello che **non** succede: lo strumento non buca mai le parti bianche
interne allo stemma, perché parte dai bordi e si propaga solo sui pixel
chiari collegati al perimetro.

---

## Passo 6 — Pubblica le immagini

Prima le immagini, poi il database. **L'ordine conta.**

```
git add resources/images/clubs
git commit -m "Stemmi club in locale"
git push
```

Poi da Plesk: **Estrai** e **Implementa**.

Verifica che siano arrivate davvero: apri nel browser

```
https://nikok.pikta.it/img/clubs/C-631.png
```

Se lo stemma si vede, sei a posto. Se dà 404, il deploy non è andato:
fermati qui e non passare al passo successivo.

---

## Passo 7 — Aggiorna il database

**Solo la prima volta**, cioè quando si passa dagli indirizzi esterni ai
file locali. Nelle aggiunte successive questo passo non serve.

Da phpMyAdmin, sul database di produzione:

```sql
-- Converte awc_clubs.logo da indirizzo esterno a solo nome file,
-- coerente con la logica gia' usata per le maglie.
-- DA LANCIARE SOLO DOPO che le immagini sono online.

UPDATE awc_clubs SET logo = CONCAT(club_id, '.png') WHERE logo LIKE 'http%';
```

Poi la verifica:

```sql
SELECT COUNT(*) AS url_residui FROM awc_clubs WHERE logo LIKE 'http%';
```

Deve rispondere **`url_residui = 0`**. Se esce un numero diverso da
zero, qualche riga ha ancora un indirizzo esterno: incolla il risultato
di questa query per capire quali sono.

```sql
SELECT club_id, club_name, logo FROM awc_clubs WHERE logo LIKE 'http%';
```

---

## Passo 8 — Registra gli stemmi appena aggiunti

Per i club che **prima non avevano nulla** in colonna, la conversione del
passo 7 non basta: quella riga non conteneva un indirizzo, quindi la
`UPDATE` non l'ha toccata. Va scritto il nome del file.

Per un club singolo:

```sql
UPDATE awc_clubs SET logo = 'C-631.png' WHERE club_id = 'C-631';
```

Per un gruppo di club, elencandoli:

```sql
UPDATE awc_clubs
SET    logo = CONCAT(club_id, '.png')
WHERE  club_id IN ('C-274', 'C-1105', 'C-812');
```

Verifica di quanti club restano ancora senza stemma:

```sql
SELECT COUNT(DISTINCT c.id) AS club_senza_stemma
FROM   awc_clubs c
JOIN   awc_squads s ON s.team_past_id = c.id
WHERE  c.logo IS NULL OR c.logo = '';
```

Il numero scende man mano che aggiungi stemmi. Si parte da **538**.

---

## Riepilogo dei comandi

```
mkdir loghi_da_convertire
   ... metti dentro le immagini, rinominate C-###.qualcosa ...
py prepara_loghi_club.py
git add resources/images/clubs
git commit -m "Stemmi club"
git push
   ... Estrai e Implementa da Plesk ...
   ... in phpMyAdmin: UPDATE awc_clubs ... e verifica url_residui = 0 ...
```

---

## Note

**Il database non va toccato a ogni aggiunta.** Se il club aveva già un
indirizzo esterno, la conversione del passo 7 ha già scritto il nome
giusto: basta che il file esista con quel nome. Il passo 8 serve solo per
i club che erano completamente senza stemma.

**Un file mancante non rompe la pagina.** Nel markup c'è un
`onerror="this.style.display='none'"`, quindi se l'immagine non esiste
sparisce e resta il nome del club. Non compare l'icona di immagine rotta.

**Lo strumento si può rilanciare quante volte si vuole.** Riscrive i file
esistenti senza degradarli, perché lavora sempre sull'originale che si
trova in `loghi_da_convertire`, non sul PNG già convertito.

**Svuota `loghi_da_convertire` quando hai finito un gruppo**, altrimenti
al giro dopo riconverte anche i vecchi. Non è dannoso, solo tempo perso.
