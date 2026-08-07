{{-- "Bordo con fascio di luce" (nome concordato 03/08): fascio fosforescente
     che percorre il bordo verde dei box contenitori — stesso effetto neon del
     #tab-content della pagina Torneo, estratto in una classe riusabile.
     Uso: aggiungere class="luce-bordo" al box (sfondo di ripiego bianco,
     override con --luce-bg). Nomi propri (--luce-angle / wc-luce-rotate) per
     non collidere con la copia inline del layout torneo. I selettori con id
     servono a vincere la specificita' di #tab-content / #popup. --}}
<style>
    @property --luce-angle { syntax: "<angle>"; initial-value: 0deg; inherits: false; }
    @keyframes wc-luce-rotate { 0% { --luce-angle: 0deg; } 100% { --luce-angle: 360deg; } }

    .luce-bordo,
    #tab-content.luce-bordo,
    #popup.luce-bordo {
        --luce-angle: 0deg;
        border: 4px solid transparent;
        border-radius: 14px;
        background: linear-gradient(var(--luce-bg, #fff), var(--luce-bg, #fff)) padding-box,
            conic-gradient(from var(--luce-angle),
                #045e03 0%, #045e03, #058404, #08ff07, #045e03) border-box;
        animation: wc-luce-rotate 2s infinite linear;
    }

    @media (prefers-reduced-motion: reduce) {
        .luce-bordo, #tab-content.luce-bordo, #popup.luce-bordo { animation: none; }
    }
</style>
