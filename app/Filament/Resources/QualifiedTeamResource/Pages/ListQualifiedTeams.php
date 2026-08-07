<?php

namespace App\Filament\Resources\QualifiedTeamResource\Pages;

use App\Filament\Resources\QualifiedTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQualifiedTeams extends ListRecords
{
    protected static string $resource = QualifiedTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
