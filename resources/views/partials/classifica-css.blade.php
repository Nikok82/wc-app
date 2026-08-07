{{-- Stili condivisi delle classifiche (tab Classifica del torneo e
     /classifica globale). Le variabili tema hanno un fallback perche' il
     partial vive sia nel layout torneo (verde) sia nel layout app. --}}
<style>
    .cls-wrap{position:relative;}

    /* barra superiore: sub-tab a sinistra, switch punti in alto a destra */
    .cls-bar{display:flex;align-items:center;justify-content:space-between;gap:10px;
        flex-wrap:wrap;margin-bottom:14px;}
    .cls-tabs{display:flex;gap:6px;
        background:linear-gradient(142deg,#045e03 0%,#57c785 58%,#045e03 100%);
        padding:5px 8px;border-radius:0 10px 0 10px;}
    .cls-tab{display:flex;align-items:center;justify-content:center;cursor:pointer;
        padding:7px 16px;border-radius:5px;background:#fff;color:#000;
        font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;
        user-select:none;}
    .cls-tab.active{border:1px solid var(--giallo,#ffff00);
        box-shadow:0 0 .1rem var(--giallo,#ffff00),0 0 .5rem #08ff07aa;}

    /* switch pt3/pt2 (segmented): ordina per punti, pari punti -> diff. reti */
    .cls-punti{display:inline-flex;border:1px solid var(--line,#e2e8e5);
        border-radius:999px;overflow:hidden;background:#fff;
        box-shadow:0 1px 3px rgba(0,0,0,.12);}
    .cls-pt{font:inherit;font-size:12px;font-weight:700;cursor:pointer;border:0;
        padding:7px 14px;background:transparent;color:var(--muted,#6b7a72);
        white-space:nowrap;}
    .cls-pt.active{background:linear-gradient(142deg,#045e03 0%,#57c785 120%);
        color:#fff;}
    .cls-pt:not(.active):hover{color:var(--ink,#16231d);}

    /* tabella */
    .cls-scroll{overflow-x:auto;margin:0 -8px;padding:0 8px;}
    .cls-table{width:100%;border-collapse:collapse;font-size:13px;min-width:680px;}
    .cls-table th{position:sticky;top:0;background:#f4f6f5;color:var(--muted,#6b7a72);
        text-transform:uppercase;font-size:11px;letter-spacing:.4px;text-align:left;
        padding:8px 6px;border-bottom:2px solid var(--line,#e2e8e5);white-space:nowrap;z-index:1;}
    .cls-table th[data-sort]{cursor:pointer;user-select:none;}
    .cls-table th[data-sort]:hover{color:var(--ink,#16231d);}
    .cls-table th[data-sort]::after{content:'\2195';opacity:.35;margin-left:4px;font-size:10px;}
    .cls-table th[data-sort].asc::after{content:'\2191';opacity:1;}
    .cls-table th[data-sort].desc::after{content:'\2193';opacity:1;}
    .cls-table td{padding:7px 6px;border-bottom:1px solid var(--line,#e2e8e5);
        vertical-align:middle;}
    .cls-table tbody tr:last-child td{border-bottom:0;}
    .cls-table tbody tr:hover td{background:#f6faf7;}
    .cls-table .c-num{text-align:center;}
    .cls-table .c-pos{font-weight:800;color:var(--verde-scuro,#0f6c14);}
    .cls-table .c-squadra{white-space:nowrap;}
    .cls-table .c-squadra a{font-weight:700;}
    .cls-table .c-squadra .flag{width:26px;height:auto;border-radius:3px;
        box-shadow:0 1px 2px rgba(0,0,0,.25);vertical-align:middle;margin-right:7px;}
    .cls-table .c-pt3,.cls-table .c-pt2{font-weight:700;}
    .cls-table .c-note{white-space:nowrap;color:var(--muted,#6b7a72);font-size:12px;}

    /* podio evidenziato nelle prime tre righe della vista di default */
    .cls-table tbody tr[data-pos="1"] .c-pos{color:#b08d00;}
    .cls-table tbody tr[data-pos="2"] .c-pos{color:#7d7f7f;}
    .cls-table tbody tr[data-pos="3"] .c-pos{color:#a5652a;}

    /* ---- medaglie (Note della perpetua): oro / argento / bronzo con
           riflesso di luce animato, in tema con l'effetto luce bandiere ---- */
    .medaglia{position:relative;display:inline-flex;align-items:center;justify-content:center;
        width:24px;height:24px;border-radius:50%;margin-right:5px;
        font-size:11px;font-weight:800;line-height:1;color:#3a2c00;
        border:1px solid rgba(0,0,0,.28);overflow:hidden;cursor:default;
        box-shadow:inset 0 1px 2px rgba(255,255,255,.7),
                   inset 0 -2px 3px rgba(0,0,0,.3),
                   0 1px 3px rgba(0,0,0,.35);}
    .medaglia.oro{background:radial-gradient(circle at 30% 28%,#f7ecb8 0%,#D6BD50 48%,#a2842a 100%);}
    .medaglia.argento{background:radial-gradient(circle at 30% 28%,#eff0f0 0%,#999b9b 50%,#6d7171 100%);color:#232626;}
    .medaglia.bronzo{background:radial-gradient(circle at 30% 28%,#eeb98c 0%,#CD7F32 50%,#87511f 100%);color:#31200c;}
    /* fascio di luce che attraversa la medaglia */
    .medaglia::after{content:'';position:absolute;top:-40%;left:-80%;width:45%;height:180%;
        background:linear-gradient(105deg,rgba(255,255,255,0) 0%,
            rgba(255,255,255,.9) 50%,rgba(255,255,255,0) 100%);
        transform:rotate(16deg);animation:med-shine 3.4s ease-in-out infinite;}
    .medaglia.argento::after{animation-delay:.55s;}
    .medaglia.bronzo::after{animation-delay:1.1s;}
    @keyframes med-shine{0%{left:-80%;}55%{left:135%;}100%{left:135%;}}
    @media (prefers-reduced-motion:reduce){.medaglia::after{animation:none;}}

    .cls-caption{color:var(--muted,#6b7a72);font-size:12px;margin:0 0 12px;}
    /* Nota "nazioni unite" (righe con asterisco): a capo sotto la caption */
    .cls-nota{display:block;margin-top:5px;font-style:italic;opacity:.9;}

    @media (max-width:560px){
        .cls-bar{justify-content:flex-end;}
        .cls-tabs{margin-right:auto;}
    }
</style>
