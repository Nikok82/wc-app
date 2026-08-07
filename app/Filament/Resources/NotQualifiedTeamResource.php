<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotQualifiedTeamResource\Pages;
use App\Models\NotQualifiedTeam;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** CRUD di awc_not_qualified_teams: squadra scelta da dropdown su awc_teams. */
class NotQualifiedTeamResource extends Resource
{
    protected static ?string $model = NotQualifiedTeam::class;

    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationGroup = 'Data entry';
    protected static ?string $navigationLabel = 'Non qualificate';
    protected static ?string $modelLabel = 'non qualificata';
    protected static ?string $pluralModelLabel = 'non qualificate';

    public static function form(Form $form): Form
    {
        return $form->schema([
            QualifiedTeamResource::selectTorneo(),
            Forms\Components\Hidden::make('tournament_name'),
            QualifiedTeamResource::selectSquadra(),
            Forms\Components\Hidden::make('team_name'),
            Forms\Components\Hidden::make('team_code'),
            Forms\Components\TextInput::make('result')->label('Risultato (HTML ammesso)')
                ->maxLength(60),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tournament_id')->label('Torneo')->sortable(),
                Tables\Columns\TextColumn::make('team_name')->label('Squadra')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('team_code')->label('Codice'),
                Tables\Columns\TextColumn::make('result')->label('Risultato')->limit(40),
            ])
            ->defaultSort('key_id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tournament_id')->label('Torneo')
                    ->options(fn () => Tournament::orderBy('year')->pluck('tournament_id', 'tournament_id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNotQualifiedTeams::route('/'),
            'create' => Pages\CreateNotQualifiedTeam::route('/create'),
            'edit'   => Pages\EditNotQualifiedTeam::route('/{record}/edit'),
        ];
    }
}
