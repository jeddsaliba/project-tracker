<?php

namespace App\Filament\Resources;

use App\Enums\NavGroup;
use App\Filament\Exports\ProjectExporter;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationGroup = NavGroup::PM->value;

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getProjectForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(ProjectExporter::class)
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\RelationManagers\ProjectsRelationManager),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Model $record) => Color::hex(collect($record->status)->first()->color))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_completed_date')
                    ->label('Expected Date of Completion')
                    ->date(config('filament.date_format'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->badge(fn (Model $record) => !$record->actual_completed_date && $record->expected_completed_date < Carbon::now())
                    ->color(fn (Model $record) => !$record->actual_completed_date && $record->expected_completed_date < Carbon::now() ? Color::Red : null),
                Tables\Columns\TextColumn::make('actual_completed_date')
                    ->label('Actual Date of Completion')
                    ->date(config('filament.date_format'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(config('filament.date_time_format'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(config('filament.date_time_format'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(config('filament.date_time_format'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(!Auth::user()->isSuperAdmin(), function ($query) {
                $query->whereHas('users', function ($query) {
                    $query->where('users.id', Auth::id()); // Filter by the current user's ID
                })->orWhere(function ($query) {
                    $query->whereHas('createdBy', function ($query) {
                        $query->where('users.id', Auth::id()); // Filter by the current user's ID
                    });
                });
            })
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getProjectForm(): array
    {
        return [
            Forms\Components\Section::make('Project Information')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            $set('slug', Str::slug($state));
                        })->columnSpan(2),
                    Forms\Components\TextInput::make('slug')
                        ->dehydrated()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(2),
                    Forms\Components\Select::make('status_id')
                        ->label('Status')
                        ->relationship('status', 'name')
                        ->searchable()
                        ->native(false)
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\Grid::make(2)
                                ->schema(StatusResource::getStatusForm()),
                        ]),
                    Forms\Components\RichEditor::make('description')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('user_id')
                        ->label('People Involved')
                        ->relationship('users', 'name')
                        ->searchable()
                        ->multiple()
                        ->native(false)
                        ->preload()
                        ->columnSpan(3),
                    Forms\Components\DatePicker::make('expected_completed_date')
                        ->required(),
                    Forms\Components\DatePicker::make('actual_completed_date'),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('attachments')
                        ->columnSpanFull()
                        ->downloadable()
                        ->multiple()
                        ->reorderable(),
                ])
                ->columns(5)
            ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('actual_completed_date')->where('expected_completed_date', '>', Carbon::now())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $totalPending = static::getModel()::whereNull('actual_completed_date')->where('expected_completed_date', '>', Carbon::now())->count();
        $total = static::getModel()::count();
        return $totalPending / $total > 0.5 ? 'warning' : 'success';
    }
}
