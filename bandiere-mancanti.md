# Bandiere mancanti — arbitri e manager

Generato dal dump `wc_dump_claude.sql` (DB `wc`), 11/08/2026.

## Sintesi

- **Manager: nessuna bandiera mancante.** Tutti i 100% dei `country_name` di `awc_managers` trovano una riga in `awc_flags`.
- **Arbitri: 19 nazionalità senza bandiera**, su 89 distinte, per un totale di 26 arbitri su 426.
- **Nessun file `.png` risulta referenziato da `awc_flags` e mancante su disco**: il problema non è di file persi, è di righe assenti in `awc_flags`.

## Causa

Le 19 nazionalità scoperte sono **le uniche scritte in inglese** in `awc_referees.country_name` (`Finland` invece di `Finlandia`, `Libya` invece di `Libia`…). Tutte le altre 70 sono in italiano e agganciano `awc_flags.team_name` senza problemi. Sono paesi che non hanno mai partecipato a un Mondiale, quindi non hanno una riga in `awc_flags`.

Serve quindi fare **due cose insieme**, altrimenti la bandiera continua a non uscire:

1. normalizzare `country_name` in italiano;
2. inserire la riga in `awc_flags` **con il nome italiano**.

## Elenco — file da produrre

Cartella: **`resources/images/flags/`** — formato **PNG**, stessa resa delle esistenti.

| # | Nome file da creare | Nome IT (per `awc_flags.team_name`) | `country_name` attuale | Arbitri |
|---|---|---|---|---|
| 1 | `GTM.png` | Guatemala | Guatemala | 3 |
| 2 | `BHR.png` | Bahrein | Bahrain | 2 |
| 3 | `MLI.png` | Mali | Mali | 2 |
| 4 | `MRI.png` | Mauritius | Mauritius | 2 |
| 5 | `SIN.png` | Singapore | Singapore | 2 |
| 6 | `SYR.png` | Siria | Syria | 2 |
| 7 | `VEN.png` | Venezuela | Venezuela | 2 |
| 8 | `BEN.png` | Benin | Benin | 1 |
| 9 | `ETH.png` | Etiopia | Ethiopia | 1 |
| 10 | `FIN.png` | Finlandia | Finland | 1 |
| 11 | `GAB.png` | Gabon | Gabon | 1 |
| 12 | `GAM.png` | Gambia | Gambia | 1 |
| 13 | `HKG.png` | Hong Kong | Hong Kong | 1 |
| 14 | `LBY.png` | Libia | Libya | 1 |
| 15 | `MTN.png` | Mauritania | Mauritania | 1 |
| 16 | `NGE.png` | Niger | Niger | 1 |
| 17 | `SEY.png` | Seychelles | Seychelles | 1 |
| 18 | `UAR.png` | Repubblica Araba Unita | United Arab Republic | 1 |
| 19 | `ZAM.png` | Zambia | Zambia | 1 |

### ⚠️ Una sigla da decidere tu

**Niger** → ho proposto `NGE` e non `NIG` perché **`NIG` nel tuo DB è già la Nigeria** (`NIG.geojson` ha il bounding box della Nigeria). La FIFA usa NIG per il Niger e NGA per la Nigeria, cioè l'opposto del tuo progetto: se un giorno la Nigeria diventasse `NGA` si libererebbe `NIG`, ma finché non lo fai `NGE` evita la collisione. Dimmi tu.

### Sigle già presenti in altre parti del progetto (riuso, non invenzione)

`GTM`, `VEN`, `GAB`, `FIN` hanno **già il geojson** con quel nome: usando le stesse sigle la mappa e la bandiera restano coerenti.

## SQL pronto

```sql
-- 1) normalizza i nomi paese in italiano
UPDATE awc_referees SET country_name='Bahrein' WHERE country_name='Bahrain';
UPDATE awc_referees SET country_name='Siria' WHERE country_name='Syria';
UPDATE awc_referees SET country_name='Etiopia' WHERE country_name='Ethiopia';
UPDATE awc_referees SET country_name='Finlandia' WHERE country_name='Finland';
UPDATE awc_referees SET country_name='Libia' WHERE country_name='Libya';
UPDATE awc_referees SET country_name='Repubblica Araba Unita' WHERE country_name='United Arab Republic';

-- 2) inserisce le bandiere (visibility=0 come le altre; start/end pieni)
-- ATTENZIONE: team_id qui e' lasciato NULL perche' non sono squadre partecipanti.
-- Se preferisci un team_id, assegna dei T-1xx liberi.
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Guatemala', 'GTM', 'GTM', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Bahrein', 'BHR', 'BHR', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Mali', 'MLI', 'MLI', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Mauritius', 'MRI', 'MRI', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Singapore', 'SIN', 'SIN', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Siria', 'SYR', 'SYR', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Venezuela', 'VEN', 'VEN', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Benin', 'BEN', 'BEN', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Etiopia', 'ETH', 'ETH', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Finlandia', 'FIN', 'FIN', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Gabon', 'GAB', 'GAB', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Gambia', 'GAM', 'GAM', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Hong Kong', 'HKG', 'HKG', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Libia', 'LBY', 'LBY', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Mauritania', 'MTN', 'MTN', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Niger', 'NGE', 'NGE', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Seychelles', 'SEY', 'SEY', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Repubblica Araba Unita', 'UAR', 'UAR', 0, 0, 0, 1930, 2050);
INSERT INTO awc_flags (team_id, team_name, team_code, flag, visibility, mens_team, womens_team, start, end) VALUES (NULL, 'Zambia', 'ZAM', 'ZAM', 0, 0, 0, 1930, 2050);
```

## Dettaglio arbitri coinvolti

- **Guatemala** (`GTM`) — 3: Carlos Alberto Batres; Mario Escobar; Rómulo Méndez
- **Bahrein** (`BHR`) — 2: Ibrahim Youssef Al-Doy; Nawaf Shukralla
- **Mali** (`MLI`) — 2: Koman Coulibaly; Idrissa Traoré
- **Mauritius** (`MRI`) — 2: Kee Chong Lim; Edwin Picon-Ackong
- **Singapore** (`SIN`) — 2: Shamsul Maidin; Govindasamy Suppiah
- **Siria** (`SYR`) — 2: Jamal Al-Sharif; Farouk Bouzo
- **Venezuela** (`VEN`) — 2: Vicente Llobregat; Jesús Valenzuela
- **Benin** (`BEN`) — 1: Coffi Codjia
- **Etiopia** (`ETH`) — 1: Seyoum Tarekegn
- **Finlandia** (`FIN`) — 1: Arne Eriksson
- **Gabon** (`GAB`) — 1: Pierre Atcho
- **Gambia** (`GAM`) — 1: Bakary Gassama
- **Hong Kong** (`HKG`) — 1: Tam Sun Chan
- **Libia** (`LBY`) — 1: Yousef Alghoul
- **Mauritania** (`MTN`) — 1: Dahane Beida
- **Niger** (`NGE`) — 1: Lucien Bouchardeau
- **Seychelles** (`SEY`) — 1: Eddy Maillet
- **Repubblica Araba Unita** (`UAR`) — 1: Ali Kandil
- **Zambia** (`ZAM`) — 1: Janny Sikazwe
