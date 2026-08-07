<?php

namespace App\Filament\Resources\ResultForYearResource\Pages;

use App\Filament\Resources\ResultForYearResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResultForYears extends ListRecords
{
    protected static string $resource = ResultForYearResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
