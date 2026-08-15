{{-- C2 (15/08): stemma di un club, col segnaposto per i 538 ancora da
     reperire (elenco in stemmi-club-mancanti.md). Il segnaposto non e' un
     buco: e' uno scudetto grigio della stessa misura, cosi' le righe
     dell'elenco restano allineate anche a stemmi mancanti.

     Variabili: $logo (url o null), $lato (px), $alt --}}
@php
    $lato = $lato ?? 16;
    $alt  = $alt ?? '';
@endphp
@if (!empty($logo))
    <img class="stemma" src="{{ $logo }}" alt="{{ $alt }}" loading="lazy"
         width="{{ $lato }}" height="{{ $lato }}"
         style="width:{{ $lato }}px;height:{{ $lato }}px"
         onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'stemma stemma-vuoto',style:'width:{{ $lato }}px;height:{{ $lato }}px'}))">
@else
    <span class="stemma stemma-vuoto" title="Stemma non ancora disponibile"
          style="width:{{ $lato }}px;height:{{ $lato }}px"></span>
@endif
