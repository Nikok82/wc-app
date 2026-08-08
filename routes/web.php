<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SquadraController;
use App\Http\Controllers\GiocatoreController;
use App\Http\Controllers\AllenatoreController;
use App\Http\Controllers\ArbitroController;
use App\Http\Controllers\PartitaController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\ImmagineController;

// Home provvisoria: menu con i link alle sezioni realizzate
Route::get('/', [HomeController::class, 'index'])->name('home');

// Frammenti dei box "Scopri una nazionale" / "Scopri un torneo" della home
// (bottone "Mostra un'altra/o" -> fetch + innerHTML, delega in wc.js)
Route::get('/home/box-squadra', [HomeController::class, 'boxSquadra'])->name('home.box.squadra');
Route::get('/home/box-torneo', [HomeController::class, 'boxTorneo'])->name('home.box.torneo');

/* ---------------- Squadra-anno (es. /squadra/ITA-1990) ---------------- */
// Nazionale in un singolo torneo: guscio + tab (info/partite/convocati/record).
// Definite PRIMA di squadra.show per non farle catturare da {code}.
Route::get('/squadra/{code}-{year}', [\App\Http\Controllers\SquadraAnnoController::class, 'show'])
    ->where(['code' => '[A-Za-z]{3}', 'year' => '\d{4}'])->name('squadra_anno.show');
Route::get('/squadra/{code}-{year}/info', [\App\Http\Controllers\SquadraAnnoController::class, 'info'])
    ->where(['code' => '[A-Za-z]{3}', 'year' => '\d{4}'])->name('squadra_anno.info');
Route::get('/squadra/{code}-{year}/partite', [\App\Http\Controllers\SquadraAnnoController::class, 'partite'])
    ->where(['code' => '[A-Za-z]{3}', 'year' => '\d{4}'])->name('squadra_anno.partite');
Route::get('/squadra/{code}-{year}/convocati', [\App\Http\Controllers\SquadraAnnoController::class, 'convocati'])
    ->where(['code' => '[A-Za-z]{3}', 'year' => '\d{4}'])->name('squadra_anno.convocati');
Route::get('/squadra/{code}-{year}/maglie', [\App\Http\Controllers\SquadraAnnoController::class, 'maglie'])
    ->where(['code' => '[A-Za-z]{3}', 'year' => '\d{4}'])->name('squadra_anno.maglie');
Route::get('/squadra/{code}-{year}/record', [\App\Http\Controllers\SquadraAnnoController::class, 'record'])
    ->where(['code' => '[A-Za-z]{3}', 'year' => '\d{4}'])->name('squadra_anno.record');

// Pagina squadra (URL con il codice: /squadra/ITA)
Route::get('/squadra/{code}', [SquadraController::class, 'show'])
    ->where('code', '[A-Za-z]{3}')->name('squadra.show');

// Contenuto dei tab caricato via AJAX: /squadra/ITA/info
Route::get('/squadra/{code}/info', [SquadraController::class, 'info'])->name('squadra.info');

Route::get('/squadra/{code}/partite', [SquadraController::class, 'partite'])->name('squadra.partite');

Route::get('/squadra/{code}/presenze', [SquadraController::class, 'presenze'])->name('squadra.presenze');

Route::get('/squadra/{code}/record', [SquadraController::class, 'record'])->name('squadra.record');

Route::get('/squadra/{code}/giocatori', [SquadraController::class, 'giocatori'])->name('squadra.giocatori');

Route::get('/squadra/{code}/managers', [SquadraController::class, 'managers'])->name('squadra.managers');

Route::get('/squadra/{code}/maglie', [SquadraController::class, 'maglie'])->name('squadra.maglie');

/* ---------------- Giocatori / Allenatori / Arbitri ---------------- */

// Elenchi con ricerca, paginazione (20/50/100) e popup scheda
Route::get('/giocatori', [GiocatoreController::class, 'index'])->name('giocatori.index');
Route::get('/allenatori', [AllenatoreController::class, 'index'])->name('allenatori.index');
Route::get('/arbitri', [ArbitroController::class, 'index'])->name('arbitri.index');

// Schede: pagina completa + frammento HTML per il popup ("/scheda")
Route::get('/giocatore/{id}', [GiocatoreController::class, 'show'])->name('giocatore.show');
Route::get('/giocatore/{id}/scheda', [GiocatoreController::class, 'scheda'])->name('giocatore.scheda');

Route::get('/allenatore/{id}', [AllenatoreController::class, 'show'])->name('allenatore.show');
Route::get('/allenatore/{id}/scheda', [AllenatoreController::class, 'scheda'])->name('allenatore.scheda');

Route::get('/arbitro/{id}', [ArbitroController::class, 'show'])->name('arbitro.show');
Route::get('/arbitro/{id}/scheda', [ArbitroController::class, 'scheda'])->name('arbitro.scheda');

// Scheda partita (05/08): guscio con testata fissa + tab via fetch
Route::get('/partita/{matchId}', [PartitaController::class, 'show'])->name('partita.show');
Route::get('/partita/{matchId}/info', [PartitaController::class, 'info'])->name('partita.info');
Route::get('/partita/{matchId}/formazioni', [PartitaController::class, 'formazioni'])->name('partita.formazioni');
Route::get('/partita/{matchId}/eventi', [PartitaController::class, 'eventi'])->name('partita.eventi');
Route::get('/partita/{matchId}/situazione', [PartitaController::class, 'situazione'])->name('partita.situazione');

/* ---------------- Stadi (04/08) ---------------- */
// Elenco con ricerca/paginazione/popup + scheda (pagina e frammento)
Route::get('/stadi', [\App\Http\Controllers\StadioController::class, 'index'])->name('stadi.index');
Route::get('/stadio/{id}', [\App\Http\Controllers\StadioController::class, 'show'])->name('stadio.show');
Route::get('/stadio/{id}/scheda', [\App\Http\Controllers\StadioController::class, 'scheda'])->name('stadio.scheda');

// Pagina Torneo: guscio + tab caricati via fetch (come la sezione squadra)
Route::get('/torneo/{tournamentId}', [TorneoController::class, 'show'])->name('torneo.show');
Route::get('/torneo/{tournamentId}/info', [TorneoController::class, 'info'])->name('torneo.info');
Route::get('/torneo/{tournamentId}/partite', [TorneoController::class, 'partite'])->name('torneo.partite');
Route::get('/torneo/{tournamentId}/squadre', [TorneoController::class, 'squadre'])->name('torneo.squadre');
Route::get('/torneo/{tournamentId}/maglie', [TorneoController::class, 'maglie'])->name('torneo.maglie');
Route::get('/torneo/{tournamentId}/record', [TorneoController::class, 'record'])->name('torneo.record');
Route::get('/torneo/{tournamentId}/marcatori', [TorneoController::class, 'marcatori'])->name('torneo.marcatori');
Route::get('/torneo/{tournamentId}/classifica', [TorneoController::class, 'classifica'])->name('torneo.classifica');
// Tab Arbitri / Managers / Stadi del torneo (04/08)
Route::get('/torneo/{tournamentId}/arbitri', [TorneoController::class, 'arbitri'])->name('torneo.arbitri');
Route::get('/torneo/{tournamentId}/managers', [TorneoController::class, 'managers'])->name('torneo.managers');
Route::get('/torneo/{tournamentId}/stadi', [TorneoController::class, 'stadi'])->name('torneo.stadi');

// Classifica perpetua globale (tutti i tornei), linkata dalla home
Route::get('/classifica', [\App\Http\Controllers\ClassificaController::class, 'index'])->name('classifica');

// Immagini in resources/images (bandiere, icone e manifesti tornei) in sola lettura
Route::get('/img/{tipo}/{file}', [ImmagineController::class, 'show'])
    ->where('tipo', 'flags|icons|tornei|site_logos')
    ->name('img');

// Maglie per partita: resources/images/kits/{anno}/{file}.gif (05/08)
Route::get('/img/kits/{anno}/{file}', [ImmagineController::class, 'kit'])
    ->where('anno', '\d{4}')
    ->name('img.kit');

// Confini nazionali per la mappa del tab Squadre (resources/geojson)
Route::get('/geojson/{code}', [ImmagineController::class, 'geojson'])
    ->where('code', '[A-Z]{3}')
    ->name('geojson');