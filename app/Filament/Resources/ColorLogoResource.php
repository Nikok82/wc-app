<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColorLogoResource\Pages;
use App\Models\ColorLogo;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * CRUD dei colori sociali (awc_colors_logos): per ogni squadra quattro
 * input color che salvano l'esadecimale in color1..color4.
 */
class ColorLogoResource extends Resource
{
    protected static ?string $model = ColorLogo::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    protected static ?string $navigationGroup = 'Data entry';
    protected static ?string $navigationLabel = 'Colori squadre';
    protected static ?string $modelLabel = 'colori squadra';
    protected static ?string $pluralModelLabel = 'colori squadre';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('team_id')
                ->label('Squadra')
                ->options(fn () => Team::orderBy('team_name')->pluck('team_name', 'team_id'))
                ->searchable()
                ->required()
                ->live()
                // team_name e team_code vengono riempiti dal team scelto
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    if ($t = Team::where('team_id', $state)->first()) {
                        $set('team_name', $t->team_name);
                        $set('team_code', $t->team_code);
                    }
                }),
            Forms\Components\Hidden::make('team_name'),
            Forms\Components\Hidden::make('team_code'),

            Forms\Components\ColorPicker::make('color1')->label('Colore 1'),
            Forms\Components\ColorPicker::make('color2')->label('Colore 2'),
            Forms\Components\ColorPicker::make('color3')->label('Colore 3'),
            Forms\Components\ColorPicker::make('color4')->label('Colore 4'),

            Forms\Components\TextInput::make('start')->label('Dal (anno)')->numeric(),
            Forms\Components\TextInput::make('end')->label('Al (anno)')->numeric(),
            Forms\Components\Toggle::make('mens_team')->label('Maschile')->default(true),
            Forms\Components\Toggle::make('womens_team')->label('Femminile')->default(false),
            Forms\Components\Toggle::make('visibility')->label('Visibile'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team_name')->label('Squadra')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('team_code')->label('Codice'),
                Tables\Columns\ColorColumn::make('color1')->label('1'),
                Tables\Columns\ColorColumn::make('color2')->label('2'),
                Tables\Columns\ColorColumn::make('color3')->label('3'),
                Tables\Columns\ColorColumn::make('color4')->label('4'),
                Tables\Columns\TextColumn::make('start')->label('Dal'),
                Tables\Columns\TextColumn::make('end')->label('Al'),
            ])
            ->defaultSort('team_name')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListColorLogos::route('/'),
            'create' => Pages\CreateColorLogo::route('/create'),
            'edit'   => Pages\EditColorLogo::route('/{record}/edit'),
        ];
    }
}
