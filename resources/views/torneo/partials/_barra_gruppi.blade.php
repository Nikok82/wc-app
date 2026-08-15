{{-- A2 (15/08): riga di scorciatoie ai gironi, allineata a sinistra e
     posta fra la barra verde delle icone e il primo girone.

         Gruppi: A • B • C • D • E • F

     Variabili: $voci = [['etichetta' => 'A', 'ancora' => 'girone-f1-gruppo-a'], ...]
     Le ancore sono href="#..." e restano fuori dalla gestione "sta caricando"
     di wc.js, che ignora di proposito i link che iniziano con #. --}}
@if (!empty($voci))
    <div class="barra-gruppi">
        <span class="bg-eti">Gruppi:</span>
        @foreach ($voci as $v)
            <a class="bg-voce" href="#{{ $v['ancora'] }}">{{ $v['etichetta'] }}</a>@if(! $loop->last)<span class="bg-sep">•</span>@endif
        @endforeach
    </div>

    <style>
        #torneo-partite .barra-gruppi{display:flex;flex-wrap:wrap;align-items:center;
            gap:4px 8px;padding:2px 2px 6px;font-size:14px;}
        #torneo-partite .barra-gruppi .bg-eti{color:var(--muted);font-weight:600;}
        #torneo-partite .barra-gruppi .bg-voce{display:inline-block;min-width:22px;
            text-align:center;font-weight:800;color:var(--verde);text-decoration:none;
            padding:1px 4px;border-radius:5px;}
        #torneo-partite .barra-gruppi .bg-voce:hover{background:rgba(27,158,87,.12);
            text-decoration:none;}
        #torneo-partite .barra-gruppi .bg-sep{color:#b9c4bd;}
        /* Con la barra dei tab appiccicata in alto, il girone raggiunto via
           ancora finirebbe sotto la testata: lo stacco lo evita. */
        #torneo-partite .groups{scroll-margin-top:78px;}
    </style>
@endif
