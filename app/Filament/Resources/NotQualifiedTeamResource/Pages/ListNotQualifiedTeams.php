<?php

namespace App\Filament\Resources\NotQualifiedTeamResource\Pages;

use App\Filament\Resources\NotQualifiedTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotQualifiedTeams extends ListRecords
{
    protected static string $resource = NotQualifiedTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
