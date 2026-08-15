# -*- coding: utf-8 -*-
"""Rende trasparente lo sfondo grigio (#CCCCCC) delle GIF delle maglie.

Perche' non basta una sostituzione globale del colore
--------------------------------------------------------------------
Il grigio #CCCCCC non compare solo nello sfondo: alcune maglie lo usano
anche al loro interno (righe, ombreggiature, numeri). Sostituendo tutti
i pixel di quel colore si bucherebbero le maglie.

Si usa quindi un riempimento a partire dai quattro bordi: diventano
trasparenti solo i pixel grigi collegati al perimetro dell'immagine.
I grigi interni, isolati dalla sagoma, vengono riassegnati al colore
piu' simile disponibile in tavolozza (differenza impercettibile, si
tratta di pochi pixel per immagine) e restano visibili.

Vengono toccati solo i colori esattamente uguali a #CCCCCC: i grigi
vicini (#EFEFEF, #B5B5B5 e simili) sono tinte usate dentro le maglie,
non antialiasing dello sfondo, e vanno lasciate stare.
"""
import os
import sys
from collections import deque

from PIL import Image

RADICE = sys.argv[1] if len(sys.argv) > 1 else 'resources/images/kits'
SFONDO = (204, 204, 204)


def indici_del_colore(tavolozza, colore):
    """Indici della tavolozza che corrispondono esattamente a un colore."""
    out = []
    for i in range(len(tavolozza) // 3):
        if tuple(tavolozza[i * 3:i * 3 + 3]) == colore:
            out.append(i)
    return out


def indice_alternativo(tavolozza, n_colori, escludi):
    """Indice del colore piu' vicino al grigio di sfondo ma diverso da esso.
    Serve per i pixel grigi interni, che non vanno resi trasparenti."""
    migliore, distanza_migliore = None, None
    for i in range(n_colori):
        if i in escludi:
            continue
        c = tuple(tavolozza[i * 3:i * 3 + 3])
        d = sum((c[k] - SFONDO[k]) ** 2 for k in range(3))
        if distanza_migliore is None or d < distanza_migliore:
            migliore, distanza_migliore = i, d
    return migliore


def elabora(percorso):
    im = Image.open(percorso)
    if im.mode != 'P':
        return 'saltato: non e\' a tavolozza'

    tav = im.getpalette() or []
    grigi = set(indici_del_colore(tav, SFONDO))
    if not grigi:
        return 'saltato: nessun pixel di sfondo grigio'

    larghezza, altezza = im.size
    px = im.load()

    # Riempimento dai bordi: si marcano i pixel grigi raggiungibili
    # dal perimetro senza attraversare la sagoma della maglia.
    visto = bytearray(larghezza * altezza)
    coda = deque()

    def accoda(x, y):
        if px[x, y] in grigi and not visto[y * larghezza + x]:
            visto[y * larghezza + x] = 1
            coda.append((x, y))

    for x in range(larghezza):
        accoda(x, 0)
        accoda(x, altezza - 1)
    for y in range(altezza):
        accoda(0, y)
        accoda(larghezza - 1, y)

    while coda:
        x, y = coda.popleft()
        if x > 0:
            accoda(x - 1, y)
        if x < larghezza - 1:
            accoda(x + 1, y)
        if y > 0:
            accoda(x, y - 1)
        if y < altezza - 1:
            accoda(x, y + 1)

    trasparente = min(grigi)
    n_colori = max(1, len(tav) // 3)
    alternativo = indice_alternativo(tav, n_colori, grigi)

    esterni = interni = 0
    for y in range(altezza):
        riga = y * larghezza
        for x in range(larghezza):
            if px[x, y] in grigi:
                if visto[riga + x]:
                    px[x, y] = trasparente
                    esterni += 1
                elif alternativo is not None:
                    px[x, y] = alternativo
                    interni += 1

    if esterni == 0:
        return 'saltato: sfondo non raggiungibile dai bordi'

    # optimize=False per non far ricalcolare gli indici a Pillow:
    # l'indice trasparente deve restare quello appena impostato.
    im.save(percorso, 'GIF', transparency=trasparente, optimize=False)
    return 'ok: %d pixel di sfondo, %d grigi interni preservati' % (esterni, interni)


def main():
    if not os.path.isdir(RADICE):
        print('Cartella non trovata:', RADICE)
        return 1

    fatti = saltati = 0
    for cartella, _, files in sorted(os.walk(RADICE)):
        for nome in sorted(files):
            if not nome.lower().endswith('.gif'):
                continue
            percorso = os.path.join(cartella, nome)
            esito = elabora(percorso)
            if esito.startswith('ok'):
                fatti += 1
            else:
                saltati += 1
                print('%-52s %s' % (os.path.relpath(percorso, RADICE), esito))

    print('---')
    print('Maglie rese trasparenti: %d   saltate: %d' % (fatti, saltati))
    return 0


if __name__ == '__main__':
    sys.exit(main())
