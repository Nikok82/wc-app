{{-- Stili della sezione Club (C2, 15/08). Elenco e scheda riprendono la
     resa della sezione Giocatori: stessa riga, stessa paginazione, stesso
     popup. Qui c'e' solo cio' che quella non ha: stemma e segnaposto. --}}
<style>
    .barra-ricerca { display:flex; align-items:center; gap:10px; flex-wrap:wrap;
                     margin-bottom:18px; }
    .barra-ricerca input[type=search] { flex:1; min-width:200px; font:inherit;
                     padding:9px 14px; border:1px solid var(--line);
                     border-radius:999px; background:#fff; outline-color:var(--accent); }
    .barra-ricerca button { font:inherit; cursor:pointer; display:flex; gap:7px;
                     align-items:center; padding:9px 18px; border-radius:999px;
                     border:1px solid var(--accent); background:var(--accent); color:#fff; }
    .barra-ricerca button img { width:15px; height:15px; filter:brightness(0) invert(1); }
    .barra-ricerca .per-page { color:var(--muted); font-size:13px; }
    .barra-ricerca select { font:inherit; padding:7px 10px; border-radius:8px;
                     border:1px solid var(--line); background:#fff; max-width:46vw; }

    .elenco .voce { display:flex; justify-content:space-between; gap:14px;
                    padding:10px 4px; border-bottom:1px solid var(--line);
                    color:var(--ink); }
    .elenco .voce:last-child { border-bottom:0; }
    .elenco .voce:hover { background:#f2f7f4; text-decoration:none; }
    .elenco .voce .nome { font-weight:600; color:var(--accent);
                    display:flex; align-items:center; gap:9px; min-width:0; }
    .elenco .voce .flag-riga { width:22px; height:22px; border-radius:50%; flex:none;
                    object-fit:cover; box-shadow:0 1px 2px rgba(0,0,0,.25); }
    .elenco .voce .flag-riga.vuota { box-shadow:none; background:#eef2ef; }
    .elenco .voce .extra { color:var(--muted); font-size:13px; white-space:nowrap; }

    .paginazione { display:flex; align-items:center; justify-content:center;
                   gap:14px; margin-top:20px; }
    .paginazione .pg { display:flex; align-items:center; justify-content:center;
                   width:38px; height:38px; border-radius:50%; background:#fff;
                   border:1px solid var(--line); font-size:18px; font-weight:700; }
    .paginazione .pg.disab { opacity:.35; }
    .paginazione .pg-stato { color:var(--muted); font-size:14px; }

    /* ---- stemma e segnaposto ---- */
    .stemma { flex:none; object-fit:contain; border-radius:3px; background:#fff; }
    /* Segnaposto dei 538 stemmi ancora da reperire: uno scudetto grigio
       della stessa misura, cosi' le righe restano allineate. */
    .stemma-vuoto { display:inline-block; background:#e6ebe8;
                    border:1px solid #d3dbd7;
                    clip-path:polygon(50% 0,100% 18%,100% 62%,50% 100%,0 62%,0 18%); }

    /* ---- modalita' id (?ids=1), per la caccia ai doppioni ---- */
    .elenco .voce .club-id { flex:none; font-size:11px; font-weight:700;
                    font-variant-numeric:tabular-nums; color:#6b7a72;
                    background:#eef2ef; border:1px solid #dfe6e2;
                    border-radius:5px; padding:1px 5px; min-width:34px;
                    text-align:center; }
    .club-modoid, .club-tutti { color:var(--muted); font-size:13px; margin:0 0 10px; }
    .club-tutti { margin:12px 0 0; text-align:center; }
    .club-mancano { color:#a8541b; }
    .club-tutti-ok { color:var(--accent); }

    /* ---- scheda club ---- */
    .club-head { display:flex; align-items:center; gap:16px; margin-bottom:6px; }
    .club-head .stemma { width:60px; height:60px; }
    .club-head h1 { font-size:26px; margin:0; letter-spacing:-.3px; }
    .club-head .club-stato { display:flex; align-items:center; gap:8px;
                    color:var(--muted); font-size:14px; margin-top:4px; }
    .club-head .club-stato img { width:26px; height:auto; border-radius:3px;
                    box-shadow:0 1px 3px rgba(0,0,0,.25); }

    .club-mond { margin-bottom:20px; }
    .club-mond > .titolo { display:flex; align-items:baseline; gap:9px;
                    padding:8px 2px; border-bottom:2px solid var(--accent);
                    margin-bottom:6px; font-weight:800; }
    .club-mond > .titolo .n { font-size:12px; font-weight:500; color:var(--muted); }
    .club-mond .maglia { color:var(--muted); font-size:12px;
                    font-variant-numeric:tabular-nums; }
</style>
