<style>
    .scheda { font-size:14px; }
    .scheda-head { display:flex; align-items:center; justify-content:space-between;
                   gap:14px; margin-bottom:14px; }
    .scheda-head h2 { font-size:24px; margin:0; letter-spacing:-.3px; }
    .scheda-head .bandiere { display:flex; gap:8px; flex:none;
                             margin-right:50px; /* spazio per la X di chiusura del popup */ }
    .scheda-head .bandiere img { width:44px; height:auto; border-radius:4px;
                                 box-shadow:0 1px 4px rgba(0,0,0,.25); }

    .scheda .riga { display:flex; gap:16px; padding:10px 2px;
                    border-bottom:1px solid var(--line, #e2e8e5); }
    .scheda .riga:last-child { border-bottom:0; }
    .scheda .riga .lbl { color:var(--muted, #6b7a72); text-transform:uppercase;
                         font-size:12px; letter-spacing:.5px; width:140px;
                         flex:none; padding-top:2px; }
    .scheda .riga .val { flex:1; font-weight:500; }

    .scheda table.gare { width:100%; border-collapse:collapse; margin-top:6px; }
    .scheda table.gare th { text-align:left; color:var(--muted, #6b7a72);
                            text-transform:uppercase; font-size:11px;
                            letter-spacing:.5px; padding:6px 8px 6px 0;
                            border-bottom:1px solid var(--line, #e2e8e5); }
    .scheda table.gare td { padding:7px 8px 7px 0; vertical-align:top;
                            border-bottom:1px solid var(--line, #e2e8e5);
                            font-size:13px; }
    .scheda table.gare tr:last-child td { border-bottom:0; }
    .scheda .data-cell { white-space:nowrap; color:var(--muted, #6b7a72); }

    /* ---- Tabella gare: separatore anno torneo + righe alternate ---- */
    .scheda table.gare tr.anno-sep td { font-weight:800; font-size:15px;
                            color:var(--accent, #1b9e57); padding:12px 0 4px;
                            border-bottom:2px solid var(--accent, #1b9e57); }
    .scheda table.gare tr.gara.alt { background:#d2ffd2; }

    /* ---- Mobile: gare su due righe (Data/Fase/Partita + Maglia/Minutaggio/Gol) ---- */
    @media (max-width:640px) {
        .scheda .riga { flex-wrap:wrap; }
        .scheda .riga.riga-gare .val { flex:1 1 100%; }

        .scheda table.gare.gare-g,
        .scheda table.gare.gare-g thead,
        .scheda table.gare.gare-g tbody { display:block; width:100%; }
        .scheda table.gare.gare-g thead tr,
        .scheda table.gare.gare-g tbody tr.gara { display:grid;
                            grid-template-columns:1fr 1.1fr 1.6fr; column-gap:6px; }
        .scheda table.gare.gare-g th,
        .scheda table.gare.gare-g td { border-bottom:0; padding:3px 8px 3px 0; }
        .scheda table.gare.gare-g thead tr { padding-bottom:5px;
                            border-bottom:1px solid var(--line, #e2e8e5); }
        .scheda table.gare.gare-g tbody tr.gara { padding:6px 0;
                            border-bottom:1px solid var(--line, #e2e8e5); }
        .scheda table.gare.gare-g tbody tr.gara:last-child { border-bottom:0; }
        .scheda table.gare.gare-g tr.anno-sep { display:block; }
        .scheda table.gare.gare-g tr.anno-sep td { display:block; width:100%; }
        .scheda .data-cell { white-space:normal; }
    }

    .match-cell { display:inline-flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .match-cell .mflag { width:22px; height:auto; border-radius:2px;
                         box-shadow:0 1px 2px rgba(0,0,0,.25); }
    .match-cell .evidenzia { font-weight:700; }
    .match-cell .msep { color:var(--muted, #6b7a72); }
    .match-cell .match-link img { width:16px; height:16px; vertical-align:middle;
                                  opacity:.65; transition:opacity .15s; }
    .match-cell .match-link:hover img { opacity:1; }

    .tornei-lista a { margin-right:6px; }
</style>
