<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\Tournament;
use App\Services\WcService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ---- Livewire in sottocartella (fix 26/07) --------------------------
        // Laravel, generando URL RELATIVI, toglie apposta la sottocartella
        // (/WC/mia-app/public), quindi il data-update-uri emesso da Livewire
        // diventava "/livewire/update" e il browser POSTava sulla radice del
        // server (404 di Apache; il login /admin falliva). La rotta di default
        // /livewire/update resta registrata da Livewire e gestisce davvero le
        // richieste: questa "gemella" col prefisso serve SOLO a far generare
        // al client l'URI completo di sottocartella. Con l'app sulla radice
        // (es. artisan serve) il prefisso è vuoto e non cambia nulla.
        // NB: non usare `php artisan route:cache` (in CLI il prefisso è vuoto).
        Livewire::setUpdateRoute(function ($handle) {
            $base = trim(request()->getBaseUrl(), '/');   // es. "WC/mia-app/public"

            return Route::post('/'.($base !== '' ? $base.'/' : '').'livewire/update', $handle)
                ->middleware('web');
        });

        // Dati del menu di navigazione (squadre + tornei) condivisi con la
        // navbar in cima a ogni pagina. Caricati solo quando la navbar viene
        // effettivamente renderizzata.
        View::composer('partials.navbar', function ($view) {
            $wc = app(WcService::class);

            $view->with([
                // Squadre in ordine alfabetico, con la bandiera piu' recente
                'navSquadre' => Team::where('visibility', 0)
                    ->orderBy('team_name')
                    ->get(['team_code', 'team_name'])
                    ->map(function ($t) use ($wc) {
                        $t->flag = $wc->bandieraUrl($t->team_code, null);

                        return $t;
                    }),
                // Tornei dal 1930 in avanti, con manifesto mini per il menu
                'navTornei' => Tournament::whereNotNull('tournament_id')
                    ->orderBy('year')
                    ->get(['tournament_id', 'year', 'host_country'])
                    ->map(function ($t) {
                        $t->mini = route('img', ['tipo' => 'tornei', 'file' => 'mini-'.$t->year.'.jpg']);

                        return $t;
                    }),
            ]);
        });
    }
}
