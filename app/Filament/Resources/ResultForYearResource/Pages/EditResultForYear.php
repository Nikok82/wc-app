<?php

namespace App\Filament\Resources\ResultForYearResource\Pages;

use App\Filament\Resources\ResultForYearResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResultForYear extends EditRecord
{
    protected static string $resource = ResultForYearResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
