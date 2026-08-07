<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PagineServizio;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard di /admin: mostra solo le card delle pagine di servizio
 * (widget PagineServizio) al posto dei widget predefiniti di Filament.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Pagine di servizio';

    public function getWidgets(): array
    {
        return [
            PagineServizio::class,
        ];
    }
}
