# Model Eloquent — database WC (Mondiali)

34 model generati per le tabelle `awc_*`.

## Dove metterli
Copia tutti i file `.php` dentro la cartella del progetto Laravel:

    mia-app/app/Models/

(sovrascrivono nulla: Laravel di default ha solo `User.php`, che NON e' qui.)

## Convenzioni applicate a ogni model
- `$table`      → nome reale con prefisso, es. `awc_players` (niente prefisso globale,
                  per non collidere con le tabelle di sistema di Laravel/Filament).
- `$primaryKey` → `key_id` (non `id`).
- `$timestamps` → `false` (le tabelle non hanno created_at / updated_at).
- `$guarded`    → `['key_id']` (tutto il resto e' compilabile dal backend).
- `$casts`      → colonne `date` mappate a date dove presenti.

## Casi particolari
- `awc_matches` → model `GameMatch` ('Match' e' parola riservata in PHP).
- `awc_tournament_stages` → `start_date` / `end_date` sono VARCHAR(10) nello schema,
  quindi NON sono castate a date (lasciate stringa, com'e' nel DB).

## Cosa NON e' incluso (di proposito)
- Relazioni (hasMany / belongsTo): il DB non ha chiavi esterne e i dati sono
  denormalizzati. Le aggiungeremo solo dove servono davvero, non a tappeto.
- Indici sulle colonne *_id: utili ma opzionali a questa dimensione (16 MB);
  si aggiungono con un ALTER non distruttivo quando serve.

## Verifica rapida (dopo aver copiato i file)
Nella cartella del progetto:

    php artisan tinker

poi:

    App\Models\Player::count();
    App\Models\Team::first();

Devono restituire numeri/righe coerenti col database.
