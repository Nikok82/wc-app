<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * Widget della dashboard /admin: una card per ogni pagina di servizio
 * (risorsa CRUD registrata nel pannello). L'elenco è DINAMICO: le CRUD
 * aggiunte in futuro compaiono da sole, senza toccare questo file.
 */
class PagineServizio extends Widget
{
    protected static string $view = 'filament.widgets.pagine-servizio';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $cards = collect(Filament::getCurrentPanel()->getResources())
            ->map(function (string $resource) {
                $model = $resource::getModel();

                return [
                    'label'     => ucfirst($resource::getNavigationLabel()),
                    'icon'      => $resource::getNavigationIcon() ?? 'heroicon-o-rectangle-stack',
                    'gruppo'    => $resource::getNavigationGroup(),
                    'conteggio' => $model::count(),
                    'url'       => $resource::getUrl('index'),
                    'urlNuovo'  => $resource::hasPage('create') ? $resource::getUrl('create') : null,
                ];
            })
            ->sortBy('label')
            ->values();

        return ['cards' => $cards];
    }
}
