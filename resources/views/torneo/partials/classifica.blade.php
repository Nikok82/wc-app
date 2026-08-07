{{-- Tab Classifica del torneo: due sub-tab.
     - "Torneo": squadre del Mondiale ordinate per class_mond, Note = result_mond,
       nomi -> squadra-anno.
     - "Perpetua": classifica di tutte le partecipanti cristallizzata a questo
       Mondiale (somma dei tornei <= anno), ordine pt3/diff. reti, Note =
       medaglie cumulate, nomi -> scheda squadra.
     In alto a destra lo switch pt3/pt2 ri-ordina la classifica per punti
     (pari punti -> differenza reti); tutte le colonne numeriche e la squadra
     sono ordinabili (delega in wc.js). Nessuna paginazione. --}}

<div id="torneo-classifica" class="cls-wrap" data-punti="pt3">

    <div class="cls-bar">
        <div class="cls-tabs">
            <span class="cls-tab active" data-view="torneo">Torneo</span>
            <span class="cls-tab" data-view="perpetua">Perpetua</span>
        </div>
        <div class="cls-punti" title="Ordina la classifica per punti: vittoria da 3 (Pt3) o da 2 (Pt2). A pari punti conta la differenza reti.">
            <button type="button" class="cls-pt active" data-pt="pt3">3 pt a vittoria</button>
            <button type="button" class="cls-pt" data-pt="pt2">2 pt a vittoria</button>
        </div>
    </div>

    <div class="cls-view" data-view="torneo">
        <p class="cls-caption">Classifica del Mondiale {{ $anno }} (ordine ufficiale).</p>
        @include('partials.classifica-tabella', ['righe' => $torneoRighe, 'mode' => 'torneo'])
    </div>

    @php
        /* Nota "nazioni unite": cita solo le righe * presenti in QUESTA
           perpetua (nessuna nota fino al 1950; le catene elencano i soli
           componenti che hanno gia' giocato, es. Jugoslavia* nel 2006 =
           Jugoslavia + Serbia e Montenegro). */
        $ordineGruppi = array_flip(array_column(
            array_column(\App\Services\ClassificaService::GRUPPI_UNITI, 'parziale'), 'nome'));
        $catene = collect($perpetuaRighe)
            ->filter(fn ($r) => $r['unita'] ?? false)
            ->sortBy(fn ($r) => $ordineGruppi[$r['team_name']] ?? 99)
            ->map(fn ($r) => implode(' + ', $r['catena'] ?? []))
            ->filter()
            ->values()
            ->all();
    @endphp

    <div class="cls-view" data-view="perpetua" style="display:none">
        <p class="cls-caption">Classifica perpetua cristallizzata al Mondiale {{ $anno }}:
            somma di tutte le partecipazioni dal 1930 al {{ $anno }}. Le medaglie
            contano i primi, secondi e terzi posti fino a questo torneo.
            @if ($catene)
                <span class="cls-nota">* Alcune nazioni, nel corso del tempo,
                    hanno cambiato status e i record con l'asterisco uniscono i
                    risultati ottenuti negli anni dalle stesse:
                    {{ implode(', ', $catene) }}</span>
            @endif
        </p>
        @include('partials.classifica-tabella', ['righe' => $perpetuaRighe, 'mode' => 'perpetua'])
    </div>
</div>

@include('partials.classifica-css')
