<?php

namespace App\Support;

/**
 * Grafici come SVG STATICO calcolato lato server.
 *
 * I frammenti dei tab si caricano via fetch e non possono contenere <script>
 * (solo <style>): niente librerie JS. Qui gli archi delle torte e le
 * coordinate della linea sono calcolati in PHP e restituiti come markup <svg>,
 * inseribile direttamente in Blade con {!! !!}.
 */
class Charts
{
    /** Formatta un float per l'SVG (max 2 decimali, compatto). */
    protected static function f(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }

    /** Scurisce un colore #rrggbb di un fattore (per il lato estruso). */
    protected static function darken(string $hex, float $factor = 0.60): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = (int) round(hexdec(substr($hex, 0, 2)) * $factor);
        $g = (int) round(hexdec(substr($hex, 2, 2)) * $factor);
        $b = (int) round(hexdec(substr($hex, 4, 2)) * $factor);

        return sprintf('#%02x%02x%02x', min(255, $r), min(255, $g), min(255, $b));
    }

    /** Punto sull'ellisse (angolo in radianti, y verso il basso). */
    protected static function pt(float $cx, float $cy, float $rx, float $ry, float $a): array
    {
        return [$cx + $rx * cos($a), $cy + $ry * sin($a)];
    }

    /**
     * Torta pseudo-3D (ellisse prospettica con spessore estruso).
     *
     * @param  array  $slices  [ ['val'=>int, 'color'=>'#rrggbb', 'label'=>'V'], ... ]
     * @param  float  $r       raggio orizzontale
     * @param  string|null  $titolo  didascalia sopra la torta
     * @param  bool   $labels  mostra i numeri sulle fette
     */
    public static function pie3d(array $slices, float $r = 74, ?string $titolo = null, bool $labels = true): string
    {
        $ry    = $r * 0.58;
        $depth = max(12.0, $r * 0.26);
        $pad   = 6.0;
        $cx    = $r + $pad;
        $topPad = $titolo !== null ? 20.0 : $pad;
        $cy    = $topPad + $ry;
        $w     = 2 * ($r + $pad);
        $h     = $cy + $ry + $depth + $pad;

        $total = 0;
        foreach ($slices as $s) {
            $total += max(0, (int) ($s['val'] ?? 0));
        }

        $svg = '<svg viewBox="0 0 '.self::f($w).' '.self::f($h).'" width="'.self::f($w).'" height="'.self::f($h)
             .'" class="wc-pie" role="img" aria-label="'.e($titolo ?? 'torta').'">';
        if ($titolo !== null) {
            $svg .= '<text x="'.self::f($cx).'" y="13" text-anchor="middle" class="wc-pie-tit">'.e($titolo).'</text>';
        }

        // Torta vuota (nessuna partita nella categoria): ellisse grigia estrusa.
        if ($total === 0) {
            $g1 = '#cbd3ce';
            $g2 = self::darken($g1, 0.72);
            [$x0, $y0] = self::pt($cx, $cy, $r, $ry, 0);
            [$x1, $y1] = self::pt($cx, $cy, $r, $ry, M_PI);
            $svg .= '<path d="M '.self::f($x0).' '.self::f($y0).' A '.self::f($r).' '.self::f($ry)
                 .' 0 0 1 '.self::f($x1).' '.self::f($y1).' L '.self::f($x1).' '.self::f($y1 + $depth)
                 .' A '.self::f($r).' '.self::f($ry).' 0 0 0 '.self::f($x0).' '.self::f($y0 + $depth)
                 .' Z" fill="'.$g2.'"/>';
            $svg .= '<ellipse cx="'.self::f($cx).'" cy="'.self::f($cy).'" rx="'.self::f($r).'" ry="'
                 .self::f($ry).'" fill="'.$g1.'"/>';
            $svg .= '<text x="'.self::f($cx).'" y="'.self::f($cy + 4).'" text-anchor="middle" class="wc-pie-empty">n/d</text>';

            return $svg.'</svg>';
        }

        // Angoli delle fette: partenza a ore 3 (0 rad), senso orario.
        $acc = 0.0;
        $seg = [];
        foreach ($slices as $s) {
            $val = max(0, (int) ($s['val'] ?? 0));
            if ($val === 0) {
                continue;
            }
            $a0 = $acc;
            $a1 = $acc + 2 * M_PI * ($val / $total);
            $seg[] = ['a0' => $a0, 'a1' => $a1, 'val' => $val,
                      'color' => $s['color'] ?? '#888', 'label' => $s['label'] ?? ''];
            $acc = $a1;
        }

        // 1) Lati estrusi: solo la parte FRONTALE (bassa) dell'ellisse, cioè
        //    gli angoli in [0, PI] (dove sin>0). Disegnati prima delle facce.
        foreach ($seg as $s) {
            if ($s['a0'] >= M_PI) {
                continue;                 // fetta tutta sul retro: nessun lato
            }
            $b0 = $s['a0'];
            $b1 = min($s['a1'], M_PI);
            if ($b1 <= $b0) {
                continue;
            }
            [$x0, $y0] = self::pt($cx, $cy, $r, $ry, $b0);
            [$x1, $y1] = self::pt($cx, $cy, $r, $ry, $b1);
            $svg .= '<path d="M '.self::f($x0).' '.self::f($y0)
                 .' A '.self::f($r).' '.self::f($ry).' 0 0 1 '.self::f($x1).' '.self::f($y1)
                 .' L '.self::f($x1).' '.self::f($y1 + $depth)
                 .' A '.self::f($r).' '.self::f($ry).' 0 0 0 '.self::f($x0).' '.self::f($y0 + $depth)
                 .' Z" fill="'.self::darken($s['color']).'"/>';
        }

        // 2) Facce superiori.
        foreach ($seg as $s) {
            [$x0, $y0] = self::pt($cx, $cy, $r, $ry, $s['a0']);
            [$x1, $y1] = self::pt($cx, $cy, $r, $ry, $s['a1']);
            $large = ($s['a1'] - $s['a0']) > M_PI ? 1 : 0;
            if (count($seg) === 1) {
                // Fetta unica = ellisse piena (evita path degenere 0..2PI).
                $svg .= '<ellipse cx="'.self::f($cx).'" cy="'.self::f($cy).'" rx="'.self::f($r)
                     .'" ry="'.self::f($ry).'" fill="'.$s['color'].'" stroke="#fff" stroke-width="1"/>';
            } else {
                $svg .= '<path d="M '.self::f($cx).' '.self::f($cy).' L '.self::f($x0).' '.self::f($y0)
                     .' A '.self::f($r).' '.self::f($ry).' 0 '.$large.' 1 '.self::f($x1).' '.self::f($y1)
                     .' Z" fill="'.$s['color'].'" stroke="#fff" stroke-width="1"/>';
            }
        }

        // 3) Numeri sulle fette (baricentro), solo se la fetta è ampia a sufficienza.
        if ($labels) {
            foreach ($seg as $s) {
                if ($s['val'] / $total < 0.07) {
                    continue;
                }
                $am = ($s['a0'] + $s['a1']) / 2;
                [$lx, $ly] = self::pt($cx, $cy, $r * 0.62, $ry * 0.62, $am);
                $svg .= '<text x="'.self::f($lx).'" y="'.self::f($ly + 4)
                     .'" text-anchor="middle" class="wc-pie-num">'.$s['val'].'</text>';
            }
        }

        return $svg.'</svg>';
    }

    /**
     * Grafico a linea del piazzamento (class_mond) nel tempo.
     *
     * @param  array  $punti  [ anno => ['pos'=>int, 'medal'=>?int, 'res'=>string], ... ]
     * @param  array  $anni   tutti gli anni-edizione (ordinati) per l'asse X
     */
    public static function lineChart(array $punti, array $anni): string
    {
        sort($anni);
        $n = count($anni);
        if ($n === 0 || empty($punti)) {
            return '';
        }

        $W = 640; $H = 250;
        $mL = 30; $mR = 14; $mT = 16; $mB = 30;
        $iW = $W - $mL - $mR;
        $iH = $H - $mT - $mB;

        $posMax = max(4, max(array_map(fn ($p) => (int) $p['pos'], $punti)));

        // indice dell'anno sull'asse X
        $idx = array_flip($anni);
        $xFor = fn (int $anno) => $mL + ($n <= 1 ? $iW / 2 : ($idx[$anno] / ($n - 1)) * $iW);
        // pos=1 in alto, pos=posMax in basso (asse invertito)
        $yFor = fn (int $pos) => $mT + (($pos - 1) / max(1, $posMax - 1)) * $iH;

        $medalCol = [1 => '#D6BD50', 2 => '#9a9c9c', 3 => '#CD7F32'];

        $svg = '<svg viewBox="0 0 '.$W.' '.$H.'" width="'.$W.'" height="'.$H.'" preserveAspectRatio="xMidYMid meet" class="wc-line" role="img" aria-label="Piazzamento per Mondiale">';
        $svg .= '<defs><linearGradient id="wcLineFill" x1="0" y1="0" x2="0" y2="1">'
             .'<stop offset="0" stop-color="#1b9e57" stop-opacity=".33"/>'
             .'<stop offset="1" stop-color="#1b9e57" stop-opacity="0"/></linearGradient></defs>';

        // Griglia orizzontale + etichette posizione
        $ticks = [1];
        foreach ([(int) ceil($posMax / 3), (int) ceil(2 * $posMax / 3), $posMax] as $t) {
            if ($t > 1 && ! in_array($t, $ticks, true)) {
                $ticks[] = $t;
            }
        }
        foreach ($ticks as $t) {
            $y = $yFor($t);
            $svg .= '<line x1="'.$mL.'" y1="'.self::f($y).'" x2="'.($W - $mR).'" y2="'.self::f($y)
                 .'" class="wc-line-grid"/>';
            $svg .= '<text x="'.($mL - 5).'" y="'.self::f($y + 3).'" text-anchor="end" class="wc-line-axis">'.$t.'°</text>';
        }

        // Segmenti (linea + area) solo tra edizioni CONSECUTIVE entrambe presenti:
        // così negli anni non qualificati la linea si spezza.
        for ($i = 0; $i < $n - 1; $i++) {
            $y1 = $anni[$i]; $y2 = $anni[$i + 1];
            if (! isset($punti[$y1]) || ! isset($punti[$y2])) {
                continue;
            }
            $x1 = $xFor($y1); $yy1 = $yFor((int) $punti[$y1]['pos']);
            $x2 = $xFor($y2); $yy2 = $yFor((int) $punti[$y2]['pos']);
            $base = $mT + $iH;
            $svg .= '<path d="M '.self::f($x1).' '.self::f($yy1).' L '.self::f($x2).' '.self::f($yy2)
                 .' L '.self::f($x2).' '.self::f($base).' L '.self::f($x1).' '.self::f($base)
                 .' Z" fill="url(#wcLineFill)"/>';
            $svg .= '<line x1="'.self::f($x1).'" y1="'.self::f($yy1).'" x2="'.self::f($x2).'" y2="'
                 .self::f($yy2).'" class="wc-line-seg"/>';
        }

        // Etichette anni + punti
        foreach ($anni as $anno) {
            $x = $xFor($anno);
            $svg .= '<text x="'.self::f($x).'" y="'.($H - 10).'" text-anchor="middle" class="wc-line-year">'
                 ."'".substr((string) $anno, 2).'</text>';
            if (! isset($punti[$anno])) {
                continue;
            }
            $pos = (int) $punti[$anno]['pos'];
            $y = $yFor($pos);
            $col = $medalCol[$punti[$anno]['medal'] ?? 0] ?? '#2f6f4e';
            $svg .= '<circle cx="'.self::f($x).'" cy="'.self::f($y).'" r="4.5" fill="'.$col
                 .'" stroke="#fff" stroke-width="1.5"><title>'.e(($anno).' — '.$pos.'° ('.($punti[$anno]['res'] ?? '').')').'</title></circle>';
            $svg .= '<text x="'.self::f($x).'" y="'.self::f($y - 9).'" text-anchor="middle" class="wc-line-pos">'.$pos.'</text>';
        }

        return $svg.'</svg>';
    }
}
