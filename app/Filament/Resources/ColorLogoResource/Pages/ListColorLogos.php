<?php

namespace App\Filament\Resources\ColorLogoResource\Pages;

use App\Filament\Resources\ColorLogoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListColorLogos extends ListRecords
{
    protected static string $resource = ColorLogoResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
