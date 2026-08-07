<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QualifiedTeamResource\Pages;
use App\Models\QualifiedTeam;
use App\Models\Team;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** CRUD di awc_qualified_teams: squadra scelta da dropdown su awc_teams. */
class QualifiedTeamResource extends Resource
{
    protected static ?string $model = QualifiedTeam::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Data entry';
    protected static ?string $navigationLabel = 'Qualificate';
    protected static ?string $modelLabel = 'qualificata';
    protected static ?string $pluralModelLabel = 'qualificate';

    /** Select squadra (da awc_teams) che riempie anche team_name/team_code. */
    public static function selectSquadra(): Forms\Components\Select
    {
        return Forms\Components\Select::make('team_id')
            ->label('Squadra')
            ->options(fn () => Team::orderBy('team_name')->pluck('team_name', 'team_id'))
            ->searchable()
            ->required()
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set) {
                if ($t = Team::where('team_id', $state)->first()) {
                    $set('team_name', $t->team_name);
                    $set('team_code', $t->team_code);
                }
            });
    }

    /** Select torneo che riempie anche tournament_name. */
    public static function selectTorneo(): Forms\Components\Select
    {
        return Forms\Components\Select::make('tournament_id')
            ->label('Torneo')
            ->options(fn () => Tournament::orderBy('year')->pluck('tournament_id', 'tournament_id'))
            ->searchable()
            ->required()
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set) {
                if ($t = Tournament::where('tournament_id', $state)->first()) {
                    $set('tournament_name', $t->tournament_name);
                }
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            self::selectTorneo(),
            Forms\Components\Hidden::make('tournament_name'),
            self::selectSquadra(),
            Forms\Components\Hidden::make('team_name'),
            Forms\Components\Hidden::make('team_code'),
            Forms\Components\TextInput::make('count_matches')->label('Partite')->numeric(),
            Forms\Components\TextInput::make('performance')->label('Performance (HTML ammesso)')
                ->maxLength(50),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tournament_id')->label('Torneo')->sortable(),
                Tables\Columns\TextColumn::make('team_name')->label('Squadra')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('team_code')->label('Codice'),
                Tables\Columns\TextColumn::make('count_matches')->label('Partite'),
                Tables\Columns\TextColumn::make('performance')->label('Performance')->limit(30),
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
            'index'  => Pages\ListQualifiedTeams::route('/'),
            'create' => Pages\CreateQualifiedTeam::route('/create'),
            'edit'   => Pages\EditQualifiedTeam::route('/{record}/edit'),
        ];
    }
}
