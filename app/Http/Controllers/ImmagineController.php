<?php

namespace App\Http\Controllers;

/**
 * Serve le immagini che vivono in resources/images (bandiere, icone e
 * manifesti dei tornei), non raggiungibili direttamente dal web server.
 * Sola lettura, nomi file validati (niente path traversal).
 */
class ImmagineController extends Controller
{
    public function show(string $tipo, string $file)
    {
        abort_unless(in_array($tipo, ['flags', 'icons', 'tornei', 'site_logos', 'clubs'], true), 404);
        abort_unless((bool) preg_match('/^[A-Za-z0-9._ -]+\.(png|svg|jpg|jpeg|webp)$/', $file), 404);
        abort_if(str_contains($file, '..'), 404);

        $path = resource_path('images/'.$tipo.'/'.$file);
        abort_unless(is_file($path), 404);

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'svg'         => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp'        => 'image/webp',
            default       => 'image/png',
        };

        return response()->file($path, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Serve le maglie per partita: resources/images/kits/{anno}/{file}.gif.
     * La cartella e' l'anno del torneo (nel DB c'e' solo il nome file).
     */
    public function kit(string $anno, string $file)
    {
        abort_unless((bool) preg_match('/^\d{4}$/', $anno), 404);
        abort_unless((bool) preg_match('/^[A-Za-z0-9._-]+\.(gif|png|webp)$/', $file), 404);
        abort_if(str_contains($file, '..'), 404);

        $path = resource_path('images/kits/'.$anno.'/'.$file);
        abort_unless(is_file($path), 404);

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            default => 'image/gif',
        };

        return response()->file($path, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Serve i confini nazionali (resources/geojson/{CODE}.geojson) per la
     * mappa del tab Squadre. Sola lettura, codice validato.
     */
    public function geojson(string $code)
    {
        abort_unless((bool) preg_match('/^[A-Z]{3}$/', $code), 404);

        $path = resource_path('geojson/'.$code.'.geojson');
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type'  => 'application/geo+json',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
