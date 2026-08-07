@extends('layouts.app')

@section('title', 'Classifica perpetua — FIFA WC History')

@section('content')
    <div class="team-head">
        <h1>Classifica perpetua</h1>
    </div>

    <div class="cls-wrap" data-punti="pt3">
        <div class="cls-bar">
            <p class="cls-caption" style="margin:0">Tutte le squadre che hanno
                partecipato ai Mondiali (1930–2026). Ordine: punti (vittoria da 3),
                a pari punti differenza reti. Le medaglie contano i primi, secondi
                e terzi posti conquistati.
                <span class="cls-nota">* Alcune nazioni, nel corso del tempo,
                    hanno cambiato status e i record con l'asterisco uniscono i
                    risultati ottenuti negli anni dalle stesse: Germania Ovest +
                    Germania, URSS + Russia, Jugoslavia + Serbia e Montenegro +
                    Serbia, Cecoslovacchia + Cechia, Zaire + Repubblica
                    Democratica del Congo</span></p>
            <div class="cls-punti" title="Ordina la classifica per punti: vittoria da 3 (Pt3) o da 2 (Pt2). A pari punti conta la differenza reti.">
                <button type="button" class="cls-pt active" data-pt="pt3">3 pt a vittoria</button>
                <button type="button" class="cls-pt" data-pt="pt2">2 pt a vittoria</button>
            </div>
        </div>

        <div class="cls-view" data-view="perpetua">
            @include('partials.classifica-tabella', ['righe' => $righe, 'mode' => 'perpetua'])
        </div>
    </div>

    @include('partials.classifica-css')
@endsection
