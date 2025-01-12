<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TaskResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;

class LatestTasks extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;
    
    public function table(Table $table): Table
    {
        return TaskResource::table($table)
            ->query(TaskResource::getEloquentQuery())
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(TaskResource::getTaskForm()),
                Tables\Actions\EditAction::make()
                    ->url(fn (Model $record): string => TaskResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('id', 'desc');
    }
}
