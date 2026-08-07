<?php

namespace App\Http\Controllers;

use App\Services\ClassificaService;

/**
 * Classifica perpetua globale (/classifica): tutte le squadre che hanno
 * partecipato ai Mondiali, tutti i tornei cumulati. Stesse regole della
 * sub-tab Perpetua del torneo (switch pt3/pt2, sort colonne, medaglie,
 * nessuna paginazione). Linkata dalla home.
 */
class ClassificaController extends Controller
{
    public function index(ClassificaService $cls)
    {
        return view('classifica.show', [
            // null, null = tutti i tornei, bandiera piu' recente
            'righe' => $cls->perpetua(null, null),
        ]);
    }
}
