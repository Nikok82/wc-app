{{-- A1 (15/08): destinazioni dello scorrimento laterale fra schede.

     Nodo vuoto e invisibile: serve solo a dire a wc.js dove portare il
     visitatore quando trascina il dito. Lo includono le tre pagine dove
     esistono gia' le frecce avanti/indietro — squadra, squadra-anno,
     torneo — e nessun'altra: altrove il gesto non deve fare niente.

     Direzione (scelta dichiarata): si segue l'esempio di Niko, cioe'
     trascinando verso SINISTRA si va alla scheda PRECEDENTE (Italia ->
     Israele) e verso destra alla successiva (Italia -> Jugoslavia). Il
     gesto punta insomma alla freccia corrispondente della barra bottoni.
     E' l'inverso della convenzione del carosello: per ribaltarlo basta
     scambiare 'prev' e 'next' qui sotto.

     Variabile: $swipe = ['prev' => ['url','label'], 'next' => [...]] --}}
@if (!empty($swipe['prev']) || !empty($swipe['next']))
    <div id="wc-swipe" hidden
         @if (!empty($swipe['prev']))
             data-prev="{{ $swipe['prev']['url'] }}"
             data-prev-label="{{ $swipe['prev']['label'] ?? '' }}"
         @endif
         @if (!empty($swipe['next']))
             data-next="{{ $swipe['next']['url'] }}"
             data-next-label="{{ $swipe['next']['label'] ?? '' }}"
         @endif></div>
@endif
