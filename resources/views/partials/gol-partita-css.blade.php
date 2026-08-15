{{-- Stili dell'elenco marcatori, condivisi da squadra, squadra-anno e
     torneo. Estratti in un partial perche' le tre tab li includono tutte
     e tre e duplicarli porterebbe a farli divergere. --}}
<style>
    /* min-width:0 su tutta la catena: senza, un nome lungo allarga la
       riga e fa sbordare il contenitore in responsive. */
    .gol-riga{display:flex;flex-wrap:wrap;gap:3px 12px;margin-top:5px;
        font-size:12px;color:var(--muted);min-width:0;}
    .gol-voce{display:inline-flex;align-items:center;gap:4px;white-space:nowrap;
        max-width:100%;}
    .gol-min{font-variant-numeric:tabular-nums;min-width:26px;text-align:right;
        color:#8b968f;}
    .gol-fl{width:15px;height:10px;object-fit:cover;border-radius:1px;flex:none;
        box-shadow:0 1px 1px rgba(0,0,0,.2);}
    /* Il nome e' un link alla scheda giocatore, ma nel tabellino deve
       restare discreto: colore del testo, sottolineatura solo al passaggio. */
    .gol-nome{overflow:hidden;text-overflow:ellipsis;color:var(--ink);
        text-decoration:none;}
    a.gol-nome:hover{text-decoration:underline;color:var(--accent);}
    .gol-nota{color:#8b968f;font-style:italic;}
</style>
