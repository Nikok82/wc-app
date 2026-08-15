/* FIFA WC History — JS condiviso di tutte le pagine pubbliche.
 * Config attesa (settata dai layout PRIMA di questo script):
 *   window.WCCONF = { logoSplash: url, leafletJs: url, leafletCss: url }
 * Contiene: menu hamburger, splash di caricamento, loader dei tab,
 * popup partita, toggle delle sezioni torneo, mappe Leaflet, bracket FotMob.
 */
(function () {
    'use strict';
    var CONF = window.WCCONF || {};
    var WC = window.WC = {};

    /* ================= MENU HAMBURGER (drawer 260px) ================= */

    function menuApri() {
        var d = document.getElementById('wc-drawer');
        var o = document.getElementById('wc-drawer-overlay');
        var h = document.querySelector('.wcnav-burger');
        if (!d) return;
        o.classList.add('aperto');
        d.classList.add('aperto');
        if (h) h.classList.add('aperto');
        document.body.style.overflow = 'hidden';
    }

    function menuChiudi() {
        var d = document.getElementById('wc-drawer');
        var o = document.getElementById('wc-drawer-overlay');
        var h = document.querySelector('.wcnav-burger');
        if (!d) return;
        d.classList.remove('aperto');
        o.classList.remove('aperto');
        if (h) h.classList.remove('aperto');
        document.body.style.overflow = '';
    }

    document.addEventListener('click', function (e) {
        var burger = e.target.closest('.wcnav-burger');
        if (burger) {
            document.getElementById('wc-drawer').classList.contains('aperto') ? menuChiudi() : menuApri();
            return;
        }
        if (e.target.id === 'wc-drawer-overlay') { menuChiudi(); return; }

        // Voci a tendina del drawer (Squadre / Tornei)
        var acc = e.target.closest('.drawer-acc-head');
        if (acc) {
            var box = acc.parentElement;
            var eraAperto = box.classList.contains('aperto');
            // una tendina alla volta
            document.querySelectorAll('#wc-drawer .drawer-acc.aperto').forEach(function (b) {
                b.classList.remove('aperto');
            });
            if (!eraAperto) box.classList.add('aperto');
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            menuChiudi();
            if (WC.chiudiPopupScheda) WC.chiudiPopupScheda();
        }
    });

    /* ================= SPLASH DI CARICAMENTO ================= */

    /* C3 (15/08): unico corpo animato, riusato dal velo a tutto schermo e
       dai riquadri dei tab. Icona che ruota + bagliore verde che pulsa. */
    function corpoCarica() {
        var logo = CONF.logoSplash
            ? '<img src="' + CONF.logoSplash + '" alt="" onerror="this.style.display=\'none\'">'
            : '';
        return '<div class="wc-carica-corpo"><span class="bagliore"></span>' + logo + '</div>';
    }

    WC.splashHTML = function () {
        return '<div class="wc-splash">' + corpoCarica() + '</div>';
    };

    /* Velo a tutto schermo, per il caricamento di una PAGINA (click su un
       link, ricarica). Creato alla prima chiamata e poi riusato: cosi' la
       dissolvenza in entrata parte sempre dallo stesso nodo. */
    var veloCarica = null;

    WC.mostraCaricamento = function () {
        if (!veloCarica) {
            veloCarica = document.createElement('div');
            veloCarica.className = 'wc-carica';
            veloCarica.innerHTML = corpoCarica();
            document.body.appendChild(veloCarica);
        }
        // forza un reflow prima di aggiungere la classe, altrimenti al primo
        // inserimento la transizione non parte e il velo appare di scatto
        void veloCarica.offsetWidth;
        veloCarica.classList.add('visibile');
    };

    WC.nascondiCaricamento = function () {
        if (veloCarica) veloCarica.classList.remove('visibile');
    };

    /* ================= LOADER DEI TAB (pagine guscio) ================= */

    /* cfg: { base, id, buttons (selector), content (selector), sezioneDefault } */
    WC.initTabs = function (cfg) {
        var content = document.querySelector(cfg.content || '#tab-content');
        var buttons = document.querySelectorAll(cfg.buttons);
        if (!content || !buttons.length) return;

        function load(section) {
            // rimuove i popup spostati su <body> dal tab precedente
            document.querySelectorAll('body > .popup-partita, body > .overlay-partita, body > .mgr-popup-overlay')
                .forEach(function (el) { el.remove(); });
            content.innerHTML = WC.splashHTML();
            // La query della pagina viene propagata al frammento (es. ?opt32=N
            // per provare le varianti del bracket a 32).
            var qs = window.location.search || '';
            fetch(cfg.base + '/' + encodeURIComponent(cfg.id) + '/' + section + qs)
                .then(function (r) { return r.ok ? r.text() : Promise.reject(r.status); })
                .then(function (html) {
                    content.innerHTML = html;
                    WC.initMappeNazione(content);   // mappe singola-nazione nei frammenti
                    WC.initMappeStadio(content);    // mappe stadi (scheda/tab Stadi)
                    WC.initBracketFotmob(content);  // pillole/scroll del bracket
                    WC.initGiocatori(content);      // paginazione tab Giocatori squadra
                })
                .catch(function () {
                    content.innerHTML = '<p class="err">Errore nel caricamento del contenuto.</p>';
                });
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                load(btn.dataset.section);
            });
        });

        load(cfg.sezioneDefault || buttons[0].dataset.section);
    };

    /* ================= DELEGA: sezioni torneo, popup, toggle ================= */

    document.addEventListener('click', function (e) {

        // Sub-bottoni fase del tab Partite (1step/2step/competition/all)
        var sub = e.target.closest('.sub-btn[data-show]');
        if (sub) {
            var wrap = sub.closest('#torneo-partite');
            wrap.querySelectorAll('.sub-btn[data-show]').forEach(function (b) { b.classList.remove('active'); });
            sub.classList.add('active');
            var scelta = sub.dataset.show;
            wrap.querySelectorAll('.sezione-fase').forEach(function (s) {
                s.style.display = (scelta === 'all' || s.dataset.sez === scelta) ? '' : 'none';
            });
            return;
        }

        // Toggle elenco / tabellone della fase a eliminazione
        var tog = e.target.closest('.ko-toggle[data-view]');
        if (tog) {
            var sez = tog.closest('.sezione-fase');
            sez.querySelectorAll('.ko-toggle').forEach(function (b) { b.classList.remove('active'); });
            tog.classList.add('active');
            sez.querySelectorAll('.ko-view').forEach(function (v) {
                v.style.display = v.dataset.view === tog.dataset.view ? '' : 'none';
            });
            if (tog.dataset.view === 'playoff') WC.initBracketFotmob(sez);
            return;
        }

        // Toggle elenco / mappa del tab Squadre
        var sq = e.target.closest('.sq-toggle[data-view]');
        if (sq) {
            var wrapSq = sq.closest('#torneo-squadre');
            wrapSq.querySelectorAll('.sq-toggle').forEach(function (b) { b.classList.remove('active'); });
            sq.classList.add('active');
            wrapSq.querySelectorAll('.sq-view').forEach(function (v) {
                v.style.display = v.dataset.view === sq.dataset.view ? '' : 'none';
            });
            if (sq.dataset.view === 'mappa') WC.initMappaTorneo();
            return;
        }

        // Home: bottone "Mostra un'altra squadra / un altro torneo" dei box
        // "Scopri…" -> ricarica il solo frammento (rotte home.box.*)
        var altro = e.target.closest('.scopri-altro[data-url]');
        if (altro) {
            var corpoBox = document.getElementById(altro.dataset.target);
            if (corpoBox) {
                corpoBox.classList.add('scopri-caric');
                fetch(altro.dataset.url)
                    .then(function (r) { return r.ok ? r.text() : Promise.reject(r.status); })
                    .then(function (html) { corpoBox.innerHTML = html; })
                    .catch(function () {})
                    .finally(function () { corpoBox.classList.remove('scopri-caric'); });
            }
            return;
        }

        // Classifiche (tab Classifica torneo + /classifica): sub-tab Torneo/Perpetua
        var clsTab = e.target.closest('.cls-tab[data-view]');
        if (clsTab) {
            var cwTab = clsTab.closest('.cls-wrap');
            cwTab.querySelectorAll('.cls-tab').forEach(function (b) { b.classList.remove('active'); });
            clsTab.classList.add('active');
            cwTab.querySelectorAll('.cls-view').forEach(function (v) {
                v.style.display = v.dataset.view === clsTab.dataset.view ? '' : 'none';
            });
            return;
        }

        // Classifiche: switch pt3/pt2 (in alto a destra) -> ri-ordina per punti;
        // a pari punti conta la differenza reti (poi gol fatti, poi nome)
        var clsPt = e.target.closest('.cls-pt[data-pt]');
        if (clsPt) {
            var cwPt = clsPt.closest('.cls-wrap');
            cwPt.dataset.punti = clsPt.dataset.pt;
            cwPt.querySelectorAll('.cls-pt').forEach(function (b) {
                b.classList.toggle('active', b === clsPt);
            });
            cwPt.querySelectorAll('.cls-table').forEach(function (t) {
                WC.clsOrdinaPunti(t, clsPt.dataset.pt);
            });
            return;
        }

        // Classifiche: ordinamento colonne (asc <-> desc; 'squadra' testuale,
        // le altre numeriche; i valori vuoti restano in fondo)
        var clsTh = e.target.closest('.cls-table th[data-sort]');
        if (clsTh) {
            WC.clsOrdina(clsTh.closest('.cls-table'), clsTh.dataset.sort,
                clsTh.classList.contains('asc'));
            return;
        }

        // Bracket FotMob: toggle turni <-> albero (bottone flottante)
        var fmTog = e.target.closest('.fm-switch');
        if (fmTog) {
            var fmWrap = fmTog.closest('.fm-bracket');
            var attuale = fmWrap.dataset.vista === 'albero' ? 'albero' : 'turni';
            var prossima = attuale === 'turni' ? 'albero' : 'turni';
            fmWrap.dataset.vista = prossima;
            fmWrap.querySelectorAll('.fm-vista').forEach(function (v) {
                v.style.display = v.dataset.vista === prossima ? '' : 'none';
            });
            fmTog.classList.toggle('vista-albero', prossima === 'albero');
            return;
        }

        // Bracket FotMob: pillole dei turni -> scorri alla colonna
        var pill = e.target.closest('.fm-pill[data-round]');
        if (pill) {
            var fmw = pill.closest('.fm-bracket');
            var col = fmw.querySelector('.fm-col[data-round="' + pill.dataset.round + '"]');
            if (col) col.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            fmw.querySelectorAll('.fm-pill').forEach(function (p) { p.classList.remove('active'); });
            pill.classList.add('active');
            return;
        }

        // Ordinamento tabella convocati (squadra-anno). Colonne normali:
        // asc <-> desc. Colonna ruolo: P,D,C,A <-> A,C,D,P, sempre con i
        // giocatori in ordine alfabetico dentro il ruolo.
        var thSort = e.target.closest('.conv-table th[data-sort]');
        if (thSort) {
            var table = thSort.closest('.conv-table');
            var key = thSort.dataset.sort;
            var desc = thSort.classList.contains('asc');   // secondo click: inverte
            table.querySelectorAll('th[data-sort]').forEach(function (t) {
                t.classList.remove('asc', 'desc');
            });
            thSort.classList.add(desc ? 'desc' : 'asc');

            var numerica = (key === 'num' || key === 'pg' || key === 'gol' || key === 'ruolo' || key === 'mondiali');
            var righe = Array.prototype.slice.call(table.tBodies[0].rows);

            righe.sort(function (a, b) {
                var va = a.dataset[key], vb = b.dataset[key];
                // vuoti sempre in fondo, in entrambe le direzioni
                if (va === '' && vb === '') return 0;
                if (va === '') return 1;
                if (vb === '') return -1;
                var cmp;
                if (numerica) cmp = parseFloat(va) - parseFloat(vb);
                else cmp = va < vb ? -1 : (va > vb ? 1 : 0);
                if (desc) cmp = -cmp;
                // dentro lo stesso ruolo: sempre alfabetico (cognome, nome)
                if (cmp === 0 && key === 'ruolo') {
                    var ka = a.dataset.cognome + '|' + a.dataset.nome;
                    var kb = b.dataset.cognome + '|' + b.dataset.nome;
                    cmp = ka < kb ? -1 : (ka > kb ? 1 : 0);
                }
                return cmp;
            });

            righe.forEach(function (r) { table.tBodies[0].appendChild(r); });
            // Tab Giocatori della squadra: riapplica filtro+paginazione
            // sul nuovo ordine delle righe
            var gw = table.closest('.gioc-wrap');
            if (gw) WC.giocAggiorna(gw);
            return;
        }

        // Paginazione del tab Giocatori (squadra): frecce ‹ ›
        var gpg = e.target.closest('.gioc-pg[data-dir]');
        if (gpg) {
            var gwrap = gpg.closest('.gioc-wrap');
            gwrap.dataset.page = String((parseInt(gwrap.dataset.page, 10) || 1) + parseInt(gpg.dataset.dir, 10));
            WC.giocAggiorna(gwrap);
            return;
        }

        // Tab Managers della squadra: click sulla riga -> popup scheda.
        // I link interni (anni dei Mondiali) restano normali link.
        var voce = e.target.closest('.mgr-elenco .voce[data-scheda]');
        if (voce && !e.target.closest('a')) {
            WC.apriPopupScheda(voce.dataset.scheda);
            return;
        }

        // Chiusura del popup scheda (bottone x oppure click sullo sfondo)
        if (e.target.closest('.mgr-popup-overlay #popup-chiudi')
            || (e.target.classList && e.target.classList.contains('mgr-popup-overlay'))) {
            WC.chiudiPopupScheda();
            return;
        }

        // Apertura popup partita (spostato su <body> per lo stacking context).
        // I link interni alla card (nomi dei marcatori, B1 del 15/08) devono
        // restare link: se il click parte da un <a>, il popup non si apre.
        var card = e.target.closest('[data-popup]');
        if (card && !e.target.closest('a')) {
            var id = card.dataset.popup;
            var pop = document.getElementById(id);
            var ov = document.getElementById('ov-' + id);
            if (ov && ov.parentElement !== document.body) document.body.appendChild(ov);
            if (pop && pop.parentElement !== document.body) document.body.appendChild(pop);
            if (ov) ov.classList.add('aperto');
            if (pop) pop.classList.add('aperto');
            return;
        }

        // Chiusura popup ("Torna indietro" oppure click sull'overlay)
        if (e.target.closest('.chiudi-popup') || e.target.classList.contains('overlay-partita')) {
            document.querySelectorAll('.popup-partita.aperto, .overlay-partita.aperto')
                .forEach(function (el) { el.classList.remove('aperto'); });
        }
    });

    /* ============ TAB GIOCATORI (squadra): filtro + paginazione ============ */
    /* Il frammento porta .gioc-wrap con data-page/data-perpage; le righe
       della .conv-table hanno data-search (nome, cognome, anni, club, ruolo).
       L'ordinamento riusa la delega .conv-table th[data-sort] qui sopra. */

    WC.giocAggiorna = function (wrap) {
        var table = wrap.querySelector('.conv-table');
        if (!table || !table.tBodies.length) return;

        var q = '';
        var filtro = wrap.querySelector('.gioc-filtro');
        if (filtro) q = filtro.value.trim().toLowerCase();

        var righe = Array.prototype.slice.call(table.tBodies[0].rows);
        var visibili = righe.filter(function (r) {
            return !q || (r.dataset.search || '').indexOf(q) !== -1;
        });

        var per = parseInt(wrap.dataset.perpage, 10) || 10;
        var pagine = Math.max(1, Math.ceil(visibili.length / per));
        var page = Math.min(Math.max(parseInt(wrap.dataset.page, 10) || 1, 1), pagine);
        wrap.dataset.page = String(page);

        var da = (page - 1) * per;
        var a = Math.min(da + per, visibili.length);

        righe.forEach(function (r) { r.style.display = 'none'; });
        visibili.slice(da, a).forEach(function (r) { r.style.display = ''; });

        var stato = wrap.querySelector('.gioc-stato');
        if (stato) {
            stato.innerHTML = visibili.length
                ? (da + 1) + '–' + a + ' di ' + visibili.length + ' giocatori <small>(pagina ' + page + ' di ' + pagine + ')</small>'
                : 'Nessun giocatore corrisponde al filtro';
        }
        wrap.querySelectorAll('.gioc-pg').forEach(function (b) {
            var dir = parseInt(b.dataset.dir, 10);
            b.disabled = (dir < 0 && page <= 1) || (dir > 0 && page >= pagine);
        });
    };

    WC.initGiocatori = function (root) {
        (root || document).querySelectorAll('.gioc-wrap:not([data-init])').forEach(function (w) {
            w.dataset.init = '1';
            WC.giocAggiorna(w);
        });
    };

    // Filtro (mentre si digita) e "per pagina": tornano alla pagina 1
    document.addEventListener('input', function (e) {
        if (e.target.matches && e.target.matches('.gioc-wrap .gioc-filtro')) {
            var w = e.target.closest('.gioc-wrap');
            w.dataset.page = '1';
            WC.giocAggiorna(w);
        }
    });
    document.addEventListener('change', function (e) {
        if (e.target.matches && e.target.matches('.gioc-wrap .gioc-perpage')) {
            var w = e.target.closest('.gioc-wrap');
            w.dataset.perpage = e.target.value;
            w.dataset.page = '1';
            WC.giocAggiorna(w);
        }
    });

    /* ============ CLASSIFICHE (tab Classifica + /classifica) ============ */
    /* Le righe della .cls-table portano data-pos/pg/v/n/p/gf/gs/dr/pt3/pt2/
       squadra ('squadra' e' l'unica chiave testuale). clsOrdina: sort per
       singola colonna con toggle asc/desc; clsOrdinaPunti: sort dello switch
       pt3/pt2 con pari merito su differenza reti, gol fatti e nome. */

    WC.clsOrdina = function (table, key, desc) {
        table.querySelectorAll('th[data-sort]').forEach(function (t) {
            t.classList.remove('asc', 'desc');
        });
        var th = table.querySelector('th[data-sort="' + key + '"]');
        if (th) th.classList.add(desc ? 'desc' : 'asc');

        var testuale = key === 'squadra';
        var righe = Array.prototype.slice.call(table.tBodies[0].rows);
        righe.sort(function (a, b) {
            var va = a.dataset[key], vb = b.dataset[key];
            if (va === '' && vb === '') return 0;   // vuoti sempre in fondo
            if (va === '') return 1;
            if (vb === '') return -1;
            var cmp = testuale
                ? (va < vb ? -1 : (va > vb ? 1 : 0))
                : parseFloat(va) - parseFloat(vb);
            return desc ? -cmp : cmp;
        });
        righe.forEach(function (r) { table.tBodies[0].appendChild(r); });
    };

    WC.clsOrdinaPunti = function (table, pt) {
        table.querySelectorAll('th[data-sort]').forEach(function (t) {
            t.classList.remove('asc', 'desc');
        });
        var th = table.querySelector('th[data-sort="' + pt + '"]');
        if (th) th.classList.add('desc');

        var righe = Array.prototype.slice.call(table.tBodies[0].rows);
        righe.sort(function (a, b) {
            var cmp = parseFloat(b.dataset[pt]) - parseFloat(a.dataset[pt]);
            if (!cmp) cmp = parseFloat(b.dataset.dr) - parseFloat(a.dataset.dr);
            if (!cmp) cmp = parseFloat(b.dataset.gf) - parseFloat(a.dataset.gf);
            if (!cmp) cmp = a.dataset.squadra < b.dataset.squadra ? -1
                : (a.dataset.squadra > b.dataset.squadra ? 1 : 0);
            return cmp;
        });
        righe.forEach(function (r) { table.tBodies[0].appendChild(r); });
    };

    /* ============ POPUP SCHEDA nei frammenti (tab Managers) ============ */
    /* L'overlay arriva col frammento (id popup-overlay, classe
       .mgr-popup-overlay) e viene spostato su <body> alla prima apertura,
       come i popup partita (il contenitore dei tab ha z-index basso). */

    WC.apriPopupScheda = function (url) {
        var ov = document.querySelector('.mgr-popup-overlay');
        if (!ov) return;
        if (ov.parentElement !== document.body) document.body.appendChild(ov);
        var corpo = ov.querySelector('#popup-corpo');
        ov.hidden = false;
        document.body.style.overflow = 'hidden';
        corpo.innerHTML = WC.splashHTML();
        fetch(url)
            .then(function (r) { return r.ok ? r.text() : Promise.reject(r.status); })
            .then(function (html) {
                corpo.innerHTML = html;
                WC.initMappeStadio(corpo);   // mappa nella scheda stadio
            })
            .catch(function () {
                corpo.innerHTML = '<p class="err">Errore nel caricamento della scheda.</p>';
            });
    };

    WC.chiudiPopupScheda = function () {
        var ov = document.querySelector('.mgr-popup-overlay');
        if (!ov || ov.hidden) return;
        ov.hidden = true;
        document.body.style.overflow = '';
        var corpo = ov.querySelector('#popup-corpo');
        if (corpo) corpo.innerHTML = '';
    };

    /* ================= BRACKET FOTMOB: sync pillole su scroll ================= */

    WC.initBracketFotmob = function (root) {
        (root || document).querySelectorAll('.fm-bracket:not([data-init])').forEach(function (fm) {
            fm.dataset.init = '1';
            var scroller = fm.querySelector('.fm-cols');
            if (!scroller) return;
            var pills = fm.querySelectorAll('.fm-pill');
            scroller.addEventListener('scroll', function () {
                var x = scroller.scrollLeft + 40;
                var attiva = null;
                fm.querySelectorAll('.fm-col').forEach(function (c) {
                    if (c.offsetLeft <= x) attiva = c.dataset.round;
                });
                if (!attiva) return;
                pills.forEach(function (p) {
                    p.classList.toggle('active', p.dataset.round === attiva);
                });
            }, { passive: true });
        });
    };

    /* ================= A3: RICERCA GLOBALE (15/08) ================= */
    /* La lente accanto all'hamburger fa entrare da sinistra il campo di
       ricerca; i risultati arrivano come frammento HTML dalla rotta /cerca
       e vengono infilati nel riquadro sovrapposto, come gia' si fa per i
       tab e per i popup delle schede.

       Si interroga mentre si digita, ma con una pausa: senza, ogni tasto
       farebbe partire una query su nove tabelle. Le risposte arretrate
       vengono scartate confrontando il numero di richiesta, altrimenti una
       risposta lenta puo' sovrascrivere una piu' recente. */

    var RIC_PAUSA = 260;   // ms di quiete prima di interrogare

    var ricTimer = null;
    var ricNumero = 0;
    var ricUltimaQ = '';

    function ricElementi() {
        return {
            barra:  document.getElementById('wc-ricerca'),
            campo:  document.getElementById('wc-ricerca-campo'),
            velo:   document.getElementById('ricerca-overlay'),
            box:    document.getElementById('ricerca-risultati')
        };
    }

    WC.apriRicerca = function () {
        var el = ricElementi();
        if (!el.barra) return;
        menuChiudi();
        el.barra.classList.add('aperta');
        el.velo.hidden = false;
        el.box.hidden = false;
        if (!el.box.innerHTML.trim()) WC.cerca('');
        // il fuoco dopo l'animazione: su iOS anticiparlo fa saltare la barra
        setTimeout(function () { el.campo.focus(); }, 180);
    };

    WC.chiudiRicerca = function () {
        var el = ricElementi();
        if (!el.barra) return;
        el.barra.classList.remove('aperta');
        el.velo.hidden = true;
        el.box.hidden = true;
        if (el.campo) el.campo.blur();
    };

    /* tipo/pagina servono a sfogliare i risultati di un solo gruppo */
    WC.cerca = function (q, tipo, pagina) {
        var el = ricElementi();
        if (!el.box) return;

        ricUltimaQ = q;
        var mio = ++ricNumero;

        var url = el.box.dataset.url + '?q=' + encodeURIComponent(q);
        if (tipo) url += '&tipo=' + encodeURIComponent(tipo) + '&pagina=' + (pagina || 1);

        fetch(url)
            .then(function (r) { return r.ok ? r.text() : Promise.reject(r.status); })
            .then(function (html) {
                if (mio !== ricNumero) return;   // risposta arretrata: si scarta
                el.box.innerHTML = html;
            })
            .catch(function () {
                if (mio !== ricNumero) return;
                el.box.innerHTML = '<p class="err" style="padding:14px">Errore nella ricerca.</p>';
            });
    };

    document.addEventListener('click', function (e) {
        if (e.target.closest('.wcnav-lente')) {
            var el = ricElementi();
            if (el.barra && el.barra.classList.contains('aperta')) WC.chiudiRicerca();
            else WC.apriRicerca();
            return;
        }
        if (e.target.closest('.wc-ricerca .ric-chiudi') || e.target.id === 'ricerca-overlay') {
            WC.chiudiRicerca();
            return;
        }
        // frecce di impaginazione di un singolo gruppo
        var pg = e.target.closest('#ricerca-risultati .ric-pg[data-tipo]');
        if (pg && !pg.disabled) {
            WC.cerca(ricUltimaQ, pg.dataset.tipo, parseInt(pg.dataset.pagina, 10) || 1);
            return;
        }
        // click su un risultato: si va via, il riquadro si chiude da solo
        if (e.target.closest('#ricerca-risultati .ric-voce')) {
            WC.chiudiRicerca();
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.id !== 'wc-ricerca-campo') return;
        var q = e.target.value;
        clearTimeout(ricTimer);
        ricTimer = setTimeout(function () { WC.cerca(q); }, RIC_PAUSA);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') WC.chiudiRicerca();
        if (e.key === 'Enter' && e.target.id === 'wc-ricerca-campo') {
            e.preventDefault();
            clearTimeout(ricTimer);
            WC.cerca(e.target.value);
        }
    });

    /* ============ A1: SCORRIMENTO LATERALE FRA SCHEDE (15/08) ============ */
    /* Trascinando il dito si salta alla scheda precedente / successiva, le
       stesse mete delle frecce della barra bottoni. Attivo solo dove il
       layout ha stampato #wc-swipe, cioe' squadra, squadra-anno e torneo.

       Direzione: verso SINISTRA -> precedente, verso destra -> successiva
       (esempio di Niko: dall'Italia a sinistra si arriva a Israele). Il
       gesto punta alla freccia corrispondente, non alla pagina "che entra"
       come nei caroselli.

       Priorita' (decisa il 15/08): dentro un contenitore che scorre in
       orizzontale — il bracket, le pillole dei turni, l'albero — comanda
       quello e il salto di pagina non scatta. Il controllo e' sul punto di
       PARTENZA del trascinamento, non su dove finisce. */

    var SWIPE_MIN = 70;     // px minimi di spostamento orizzontale
    var SWIPE_RAPP = 1.6;   // quanto l'orizzontale deve battere il verticale
    var SWIPE_MAXT = 900;   // ms oltre i quali non e' piu' un gesto ma un trascinamento

    /* true se il punto di partenza cade dentro qualcosa che scorre di suo
       in orizzontale (o dentro un campo di testo, dove il dito seleziona). */
    function dentroScorrimentoOrizzontale(el) {
        for (var n = el; n && n !== document.body; n = n.parentElement) {
            if (n.nodeType !== 1) continue;
            if (n.matches('input, textarea, select, [contenteditable="true"]')) return true;
            var st = getComputedStyle(n);
            var ox = st.overflowX;
            if ((ox === 'auto' || ox === 'scroll') && n.scrollWidth > n.clientWidth + 4) {
                return true;
            }
        }
        return false;
    }

    WC.initSwipeSchede = function () {
        var cfg = document.getElementById('wc-swipe');
        if (!cfg) return;

        var x0 = 0, y0 = 0, t0 = 0, valido = false;

        document.addEventListener('touchstart', function (e) {
            // Un dito solo: con due il visitatore sta ingrandendo, non scorrendo.
            valido = e.touches.length === 1
                && !dentroScorrimentoOrizzontale(e.target);
            if (!valido) return;
            x0 = e.touches[0].clientX;
            y0 = e.touches[0].clientY;
            t0 = Date.now();
        }, { passive: true });

        document.addEventListener('touchend', function (e) {
            if (!valido) return;
            valido = false;
            if (!e.changedTouches || !e.changedTouches.length) return;

            var dx = e.changedTouches[0].clientX - x0;
            var dy = e.changedTouches[0].clientY - y0;

            if (Date.now() - t0 > SWIPE_MAXT) return;
            if (Math.abs(dx) < SWIPE_MIN) return;
            // direzione nettamente orizzontale: senza questo controllo uno
            // scorrimento verticale storto cambierebbe pagina
            if (Math.abs(dx) < Math.abs(dy) * SWIPE_RAPP) return;

            var meta = dx < 0 ? cfg.dataset.prev : cfg.dataset.next;
            if (!meta) return;

            if (WC.mostraCaricamento) WC.mostraCaricamento();
            window.location.href = meta;
        }, { passive: true });
    };

    /* ================= MAPPE LEAFLET ================= */

    var leafletPromise = null;
    function caricaLeaflet() {
        if (window.L) return Promise.resolve();
        if (leafletPromise) return leafletPromise;
        leafletPromise = new Promise(function (resolve, reject) {
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = CONF.leafletCss;
            document.head.appendChild(css);
            var js = document.createElement('script');
            js.src = CONF.leafletJs;
            js.onload = resolve;
            js.onerror = reject;
            document.head.appendChild(js);
        });
        return leafletPromise;
    }

    function erroreMappa(div) {
        div.innerHTML = '<p class="err" style="padding:14px">Impossibile caricare la libreria della mappa.</p>';
    }

    /* Mappa multi-nazione del tab Squadre (data-paesi = JSON). */
    WC.initMappaTorneo = function () {
        var div = document.querySelector('.mappa-torneo:not([data-init])');
        if (!div) return;
        div.dataset.init = '1';

        var paesi = {};
        try { paesi = JSON.parse(div.dataset.paesi || '{}'); } catch (e) { }

        caricaLeaflet().then(function () {
            var map = L.map(div, { minZoom: 1, maxZoom: 7, worldCopyJump: true }).setView([25, 5], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var bounds = null;
            var codes = Object.keys(paesi);
            var rimasti = codes.length;

            codes.forEach(function (code) {
                var v = paesi[code]; // [url, riempi, bordo, nome, performance]
                fetch(v[0])
                    .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
                    .then(function (gj) {
                        var layer = L.geoJSON(gj, {
                            style: { fillColor: v[1], color: v[2], weight: 1.5, fillOpacity: 0.65 }
                        }).addTo(map);
                        layer.bindTooltip(v[3] + (v[4] ? ' — ' + v[4] : ''), { sticky: true });
                        var b = layer.getBounds();
                        if (b && b.isValid()) bounds = bounds ? bounds.extend(b) : b;
                    })
                    .catch(function () { /* confine mancante: si ignora */ })
                    .finally(function () {
                        if (--rimasti === 0 && bounds && bounds.isValid()) {
                            map.fitBounds(bounds, { padding: [12, 12] });
                        }
                    });
            });
        }).catch(function () { erroreMappa(div); });
    };

    /* Mappa singola nazione (scheda squadra, tab Info):
       <div class="mappa-nazione" data-geojson="url" data-nome="Italia"></div> */
    WC.initMappeNazione = function (root) {
        (root || document).querySelectorAll('.mappa-nazione:not([data-init])').forEach(function (div) {
            div.dataset.init = '1';
            caricaLeaflet().then(function () {
                var map = L.map(div, { minZoom: 1, maxZoom: 8 }).setView([25, 5], 3);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                fetch(div.dataset.geojson)
                    .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
                    .then(function (gj) {
                        var layer = L.geoJSON(gj, {
                            style: { fillColor: '#1b9e57', color: '#045e03', weight: 2, fillOpacity: 0.55 }
                        }).addTo(map);
                        if (div.dataset.nome) layer.bindTooltip(div.dataset.nome, { sticky: true });
                        var b = layer.getBounds();
                        // maxZoom: senza tetto i paesi piccoli arrivavano al
                        // livello 8 (massimo della mappa) e risultavano
                        // ingranditi oltre il leggibile. 5 e' il compromesso
                        // che tiene leggibili sia Curacao sia la Russia.
                        if (b && b.isValid()) map.fitBounds(b, { padding: [14, 14], maxZoom: 5 });
                    })
                    .catch(function () { div.style.display = 'none'; });
            }).catch(function () { erroreMappa(div); });
        });
    };

    /* Mappe stadi (04/08):
       - singolo marker (scheda stadio): <div class="mappa-stadio"
         data-lat=".." data-lng=".." data-nome=".."></div>
       - marker multipli (tab Stadi del torneo): <div class="mappa-stadi"
         data-stadi='[[lat,lng,"nome","url"],...]'></div>
       Chiamata dopo load di pagina, frammenti dei tab e popup schede
       (i <script> nei frammenti innerHTML non vengono eseguiti). */
    WC.initMappeStadio = function (root) {
        (root || document).querySelectorAll('.mappa-stadio:not([data-init])').forEach(function (div) {
            div.dataset.init = '1';
            caricaLeaflet().then(function () {
                var lat = parseFloat(div.dataset.lat), lng = parseFloat(div.dataset.lng);
                var map = L.map(div, { minZoom: 2, maxZoom: 18 }).setView([lat, lng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                var m = L.marker([lat, lng]).addTo(map);
                if (div.dataset.nome) m.bindTooltip(div.dataset.nome);
            }).catch(function () { erroreMappa(div); });
        });
        (root || document).querySelectorAll('.mappa-stadi:not([data-init])').forEach(function (div) {
            div.dataset.init = '1';
            var stadi = [];
            try { stadi = JSON.parse(div.dataset.stadi || '[]'); } catch (e) { }
            if (!stadi.length) { div.style.display = 'none'; return; }
            caricaLeaflet().then(function () {
                var map = L.map(div, { minZoom: 1, maxZoom: 18 }).setView([25, 5], 2);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                var bounds = null;
                stadi.forEach(function (s) { // [lat, lng, nome, url]
                    var m = L.marker([s[0], s[1]]).addTo(map);
                    if (s[2]) m.bindTooltip(s[2]);
                    if (s[3]) m.on('click', function () { window.location.href = s[3]; });
                    var ll = L.latLng(s[0], s[1]);
                    bounds = bounds ? bounds.extend(ll) : L.latLngBounds(ll, ll);
                });
                if (bounds) map.fitBounds(bounds.pad(0.2), { maxZoom: 11 });
            }).catch(function () { erroreMappa(div); });
        });
    };

    /* ================= EFFETTO LUCE SULLE BANDIERE ================= */
    /* Per ogni img.flag un fascio di luce animato che attraversa la
       bandiera in diagonale, stessa estetica del riflesso delle medaglie
       della classifica (keyframes analoghi a med-shine). Durata e ritardo
       sono casuali per ogni bandiera, cosi' i lampi non sono mai
       sincronizzati; prefers-reduced-motion disattiva l'animazione.
       (I vecchi fasci statici casuali sono stati rimossi il 02/08 su
       richiesta di Niko.) Un MutationObserver copre anche i contenuti
       inseriti via fetch. */

    function applicaLuce(img) {
        // niente wrap finche' l'immagine non ha un box reale
        if (!img.parentNode) return;
        var cs = getComputedStyle(img);
        var wrap = document.createElement('span');
        wrap.className = 'luce-wrap';
        if (cs.display === 'block') wrap.style.display = 'block';
        // eventuali margini passano al wrapper, cosi' l'overlay combacia
        if (cs.margin !== '0px') {
            wrap.style.margin = cs.margin;
            img.style.margin = '0';
        }
        img.parentNode.insertBefore(wrap, img);
        wrap.appendChild(img);
        var ov = document.createElement('span');
        ov.className = 'luce-overlay';
        ov.style.borderRadius = cs.borderRadius;
        // fascio animato: ciclo 3-7s, partenza sfalsata 0-5s
        var fascio = document.createElement('span');
        fascio.className = 'luce-fascio';
        fascio.style.animationDuration = (3 + Math.random() * 4).toFixed(2) + 's';
        fascio.style.animationDelay = (Math.random() * 5).toFixed(2) + 's';
        ov.appendChild(fascio);
        wrap.appendChild(ov);
    }

    WC.effettoLuce = function (root) {
        var r = root || document;
        var lista = r.querySelectorAll ? r.querySelectorAll('img.flag:not([data-luce])') : [];
        Array.prototype.forEach.call(lista, function (img) {
            img.dataset.luce = '1';
            if (img.complete && img.naturalWidth) applicaLuce(img);
            else img.addEventListener('load', function () { applicaLuce(img); }, { once: true });
        });
    };

    // Stile minimo dell'overlay, iniettato una volta sola
    (function () {
        var st = document.createElement('style');
        st.textContent =
            '.luce-wrap{position:relative;display:inline-block;line-height:0;max-width:100%;}' +
            '.luce-wrap>img{vertical-align:top;}' +
            '.luce-overlay{position:absolute;inset:0;pointer-events:none;overflow:hidden;}' +
            '.luce-fascio{position:absolute;top:-40%;left:-80%;width:45%;height:180%;' +
                'background:linear-gradient(105deg,rgba(255,255,255,0) 0%,' +
                'rgba(255,255,255,.8) 50%,rgba(255,255,255,0) 100%);' +
                'transform:rotate(16deg);' +
                'animation:wc-luce-shine 4s ease-in-out infinite;}' +
            '@keyframes wc-luce-shine{0%{left:-80%}55%{left:135%}100%{left:135%}}' +
            '@media (prefers-reduced-motion:reduce){.luce-fascio{animation:none;display:none;}}';
        document.head.appendChild(st);
    })();

    // Copre i frammenti caricati via fetch/innerHTML (tab, popup, elenchi)
    function osservaLuce() {
        new MutationObserver(function (muts) {
            for (var i = 0; i < muts.length; i++) {
                var ad = muts[i].addedNodes;
                for (var j = 0; j < ad.length; j++) {
                    var nd = ad[j];
                    if (nd.nodeType !== 1) continue;
                    if (nd.matches && nd.matches('img.flag:not([data-luce])')) WC.effettoLuce(nd.parentNode);
                    else WC.effettoLuce(nd);
                }
            }
        }).observe(document.body, { childList: true, subtree: true });
    }

    /* ================= BARRA BOTTONI GLOBALE (05/08) ================= */
    /* Barra fissa a piè di pagina, solo responsive (partial barra-bottoni):
       [ctx prev] indietro | aggiorna | stop | avanti [ctx next].
       Le azioni non permesse prendono .wcb-off (opacità 50%):
       - frecce: Navigation API (canGoBack/canGoForward) dove esiste, cioè
         Chrome/Edge/Android. Su Safari e Firefox l'informazione non è
         esposta dal browser: le frecce restano sempre attive.
       - aggiorna/stop si alternano con lo stato di caricamento: durante il
         caricamento è attivo solo stop, a pagina caricata solo aggiorna.
         Riguarda il caricamento della PAGINA (anche in uscita, click su
         link), non i fetch dei frammenti dei tab. */

    function wcbarSet(azione, off) {
        var btn = document.querySelector('.wc-barra [data-wcbar="' + azione + '"]');
        if (!btn) return;
        btn.classList.toggle('wcb-off', !!off);
        btn.setAttribute('aria-disabled', off ? 'true' : 'false');
    }

    function wcbarFrecce() {
        if (!('navigation' in window) || typeof window.navigation.canGoBack !== 'boolean') return;
        wcbarSet('back', !window.navigation.canGoBack);
        wcbarSet('forward', !window.navigation.canGoForward);
    }

    function wcbarCaricamento(inCorso) {
        wcbarSet('reload', inCorso);
        wcbarSet('stop', !inCorso);
        // C3: lo stesso segnale accende e spegne il velo di caricamento.
        // La barra bottoni e' solo responsive, il velo no: sta qui perche'
        // e' qui che il sito sa gia' quando una navigazione comincia e finisce.
        if (inCorso) WC.mostraCaricamento(); else WC.nascondiCaricamento();
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.wc-barra [data-wcbar]');
        if (btn) {
            if (btn.classList.contains('wcb-off')) return;
            switch (btn.dataset.wcbar) {
                case 'back':    history.back(); break;
                case 'forward': history.forward(); break;
                case 'reload':  wcbarCaricamento(true); window.location.reload(); break;
                case 'stop':    window.stop(); wcbarCaricamento(false); break;
            }
            return;
        }
        // Click su un link normale: parte una navigazione -> stop attivo,
        // aggiorna spento. (Niente beforeunload: bloccherebbe la bfcache.)
        var a = e.target.closest('a[href]');
        if (a && !a.target && !a.hasAttribute('download')
            && (a.getAttribute('href') || '').charAt(0) !== '#'
            && !e.defaultPrevented && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            wcbarCaricamento(true);
        }
    });

    function wcbarInit() {
        // Al primo caricamento la pagina e' gia' a video quando questo
        // script parte: alzare il velo qui produrrebbe solo un lampo. Si
        // sistemano i bottoni e basta; il velo entra in scena dalla
        // navigazione successiva in poi.
        wcbarSet('reload', false);
        wcbarSet('stop', true);
        wcbarFrecce();
        window.addEventListener('load', function () { wcbarCaricamento(false); });
        // Ritorno alla pagina (anche da bfcache): ripristina gli stati
        window.addEventListener('pageshow', function () {
            wcbarCaricamento(false);
            wcbarFrecce();
        });
        window.addEventListener('popstate', wcbarFrecce);
        if ('navigation' in window && window.navigation.addEventListener) {
            window.navigation.addEventListener('currententrychange', wcbarFrecce);
        }
    }

    /* Auto-init per gli elementi gia' presenti al primo caricamento pagina */
    document.addEventListener('DOMContentLoaded', function () {
        wcbarInit();
        WC.initMappeNazione(document);
        WC.initMappeStadio(document);
        WC.initBracketFotmob(document);
        WC.initGiocatori(document);
        WC.initSwipeSchede();
        WC.effettoLuce(document);
        osservaLuce();
    });
})();
