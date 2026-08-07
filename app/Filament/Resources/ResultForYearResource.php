<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResultForYearResource\Pages;
use App\Models\ResultForYear;
use App\Models\Team;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * CRUD di awc_results_for_year (riepilogo per torneo + progressivi "perpetua").
 * - Mostra TUTTI i campi della tabella.
 * - class_mond modificabile INLINE nell'elenco (TextInputColumn).
 * - Filtri per squadra (team_id) e torneo (tournament_id) via dropdown.
 * - Paginazione iniziale 50; colonne statistiche ordinabili asc/desc.
 * La tabella non ha team_name: il nome si mostra risolvendo il team_id.
 */
class ResultForYearResource extends Resource
{
    protected static ?string $model = ResultForYear::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Data entry';
    protected static ?string $navigationLabel = 'Risultati per anno';
    protected static ?string $modelLabel = 'risultato per anno';
    protected static ?string $pluralModelLabel = 'risultati per anno';

    /** Le 18 colonne statistiche ordinabili (etichetta breve => campo). */
    protected const STAT = [
        'PG'    => 'partite_giocate',
        'V'     => 'vittorie',
        'N'     => 'pareggi',
        'P'     => 'sconfitte',
        'Pt2'   => 'pt2',
        'Pt3'   => 'pt3',
        'GF'    => 'gol_fatti',
        'GS'    => 'gol_subiti',
        'DR'    => 'diff_reti',
        'pGioc' => 'perp_giocate',
        'pV'    => 'perp_vittorie',
        'pN'    => 'perp_pareggi',
        'pP'    => 'perp_sconfitte',
        'pGF'   => 'perp_gf',
        'pGS'   => 'perp_gs',
        'pDR'   => 'perp_diff_reti',
        'pPt2'  => 'perp_pt2',
        'pPt3'  => 'perp_pt3',
    ];

    public static function form(Form $form): Form
    {
        $num = fn (string $campo, string $label) => Forms\Components\TextInput::make($campo)
            ->label($label)->numeric();

        return $form->schema([
            Forms\Components\Select::make('tournament_id')
                ->label('Torneo')
                ->options(fn () => Tournament::orderBy('year')->pluck('tournament_id', 'tournament_id'))
                ->searchable()->required(),
            Forms\Components\Select::make('team_id')
                ->label('Squadra')
                ->options(fn () => Team::orderBy('team_name')->pluck('team_name', 'team_id'))
                ->searchable()->required(),

            Forms\Components\Section::make('Torneo')->columns(3)->schema([
                $num('partite_giocate', 'Partite giocate')->required()->default(0),
                $num('vittorie', 'Vittorie')->required()->default(0),
                $num('pareggi', 'Pareggi')->required()->default(0),
                $num('sconfitte', 'Sconfitte')->required()->default(0),
                $num('pt2', 'Punti (2 pt)'),
                $num('pt3', 'Punti (3 pt)'),
                $num('gol_fatti', 'Gol fatti')->required()->default(0),
                $num('gol_subiti', 'Gol subiti')->required()->default(0),
                $num('diff_reti', 'Diff. reti'),
                Forms\Components\TextInput::make('result_mond')->label('Risultato')->required(),
                $num('class_mond', 'Classifica mondiale')->integer(),
            ]),

            Forms\Components\Section::make('Perpetua (progressivi)')->columns(3)->schema([
                $num('perp_giocate', 'Giocate'),
                $num('perp_vittorie', 'Vittorie'),
                $num('perp_pareggi', 'Pareggi'),
                $num('perp_sconfitte', 'Sconfitte'),
                $num('perp_gf', 'GF'),
                $num('perp_gs', 'GS'),
                $num('perp_diff_reti', 'Diff. reti'),
                $num('perp_pt2', 'Punti (2 pt)'),
                $num('perp_pt3', 'Punti (3 pt)'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $colonne = [
            Tables\Columns\TextColumn::make('key_id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('tournament_id')->label('Torneo')->sortable(),
            Tables\Columns\TextColumn::make('team_id')->label('Squadra')
                ->formatStateUsing(fn ($state) => (Team::where('team_id', $state)->value('team_name') ?? $state).' ('.$state.')')
                ->sortable(),
            Tables\Columns\TextColumn::make('result_mond')->label('Risultato'),
            Tables\Columns\TextInputColumn::make('class_mond')
                ->label('Class. mond')
                ->rules(['nullable', 'integer'])
                ->sortable(),
        ];

        foreach (self::STAT as $label => $campo) {
            $colonne[] = Tables\Columns\TextColumn::make($campo)->label($label)->sortable();
        }

        return $table
            ->columns($colonne)
            ->defaultSort('key_id')
            ->paginated([10, 25, 50, 100, 200])
            ->defaultPaginationPageOption(50)
            ->filters([
                Tables\Filters\SelectFilter::make('team_id')->label('Squadra')
                    ->options(fn () => Team::orderBy('team_name')->pluck('team_name', 'team_id'))
                    ->searchable(),
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
            'index'  => Pages\ListResultForYears::route('/'),
            'create' => Pages\CreateResultForYear::route('/create'),
            'edit'   => Pages\EditResultForYear::route('/{record}/edit'),
        ];
    }
}
