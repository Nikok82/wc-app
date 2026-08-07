<?php

namespace App\Filament\Resources\QualifiedTeamResource\Pages;

use App\Filament\Resources\QualifiedTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQualifiedTeam extends EditRecord
{
    protected static string $resource = QualifiedTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
