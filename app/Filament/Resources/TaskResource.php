<?php

namespace App\Filament\Resources;

use App\Enums\NavGroup;
use App\Filament\Exports\TaskExporter;
use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\Pages\ViewTask;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationGroup = NavGroup::PM->value;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getTaskForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(TaskExporter::class)
                    ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager),
                Tables\Actions\CreateAction::make()
                    ->hidden(fn ($livewire) => !$livewire instanceof \App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\RelationManagers\TasksRelationManager),
                Tables\Columns\TextColumn::make('project.title')
                    ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Model $record) => Color::hex(collect($record->status)->first()->color))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('checklistCount')
                    ->label('Checklist')
                    ->badge()
                    ->color(function (Model $record) {
                        if (!$record->checklist()->where(['is_done' => true])->count()) return Color::Red;
                        if ($record->checklist()->where(['is_done' => true])->count()) return Color::Orange;
                        if ($record->checklist()->where(['is_done' => true])->count() == $record->checklist->count()) return Color::Green;
                    }),
                \IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar::make('progress')
                    ->getStateUsing(function (Model $record) {
                        $total = $record->checklist()->count();
                        $progress = $record->checklist()->where(['is_done' => true])->count();
                        return [
                            'total' => $total,
                            'progress' => $progress,
                        ];
                    }),
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
                Tables\Actions\Action::make('checklist')
                    ->icon('heroicon-o-list-bullet')
                    ->form([
                        self::getChecklist()
                    ])
                    ->requiresConfirmation()
                    ->after(function (Model $record): void {
                        if ($record->checklist()->where(['is_done' => false])->get()->isEmpty()) {
                            $record->whereNull('actual_completed_date')->update(['actual_completed_date' => Carbon::now()]);
                        } else {
                            $record->update(['actual_completed_date' => null]);
                        }
                    })
                    ->action(function (Model $record, array $data): void {
                        $record->checklist()->whereNotIn('id', $data['checklist'])->update(['is_done' => false]);
                        $record->checklist()->whereIn('id', $data['checklist'])->update(['is_done' => true]);
                        Notification::make()
                            ->success()
                            ->title('Checklist updated')
                            ->send();
                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
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

    public static function getTaskForm(): array
    {
        return [
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('Task Information')
                    ->schema(self::getTaskInformationForm())
                    ->columns(6),
                Forms\Components\Wizard\Step::make('Checklist')
                    ->schema([
                        Forms\Components\Repeater::make('checklist')
                            ->hiddenLabel()
                            ->relationship('checklist')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\RichEditor::make('description')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\Checkbox::make('is_done')
                                    ->label(function (Forms\Get $get) {
                                        return $get('is_done') ? 'Completed' : 'Mark as Complete';
                                    })
                                    ->live(),
                                ])
                    ])
                ])
                ->skippable()
                ->columnSpanFull()
                ->hiddenOn('view'),
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Task Information')
                        ->schema(self::getTaskInformationForm())
                        ->columns(6),
                    Forms\Components\Tabs\Tab::make('Checklist')
                        ->schema([self::getChecklist()])
                ])
                ->columnSpanFull()
                ->hiddenOn(['create', 'edit'])
        ];
    }

    public static function getTaskInformationForm(): array
    {
        return [
            Forms\Components\Select::make('project_id')
                ->required()
                ->relationship('project', 'title')
                ->columnSpanFull()
                ->hidden(fn ($livewire) => $livewire instanceof \App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager),
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
                ])
                ->columnSpan(2),
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
                ->columnSpan(2),
            Forms\Components\DatePicker::make('expected_completed_date')
                ->required()
                ->columnSpan(2),
            Forms\Components\DatePicker::make('actual_completed_date')
                ->columnSpan(2),
            Forms\Components\SpatieMediaLibraryFileUpload::make('attachments')
                ->columnSpanFull()
                ->downloadable()
                ->multiple()
                ->reorderable(),
        ];
    }

    private static function getChecklist()
    {
        return Forms\Components\CheckboxList::make('checklist')
            ->options(fn (Model $record) => $record->checklist()->pluck('title', 'id')->toArray())
            ->descriptions(fn (Model $record) => $record->checklist()->pluck('description', 'id')->map(fn ($description) =>new HtmlString($description))->toArray())
            ->bulkToggleable()
            ->searchable()
            ->default(fn (Model $record) => $record->checklist()->where(['is_done' => true])->pluck('id')->toArray())
            ->afterStateHydrated(function (Model | null $record, Forms\Components\CheckboxList $checklist) {
                $count = $record?->checklist()->count();
                $checklist->columns(max(1, (int) ceil($count / 10)));
            })
            ->hiddenLabel();
    }
}
