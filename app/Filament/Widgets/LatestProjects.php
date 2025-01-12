<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TaskResource;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;

class LatestProjects extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return ProjectResource::table($table)
            ->query(ProjectResource::getEloquentQuery())
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(ProjectResource::getProjectForm()),
                Tables\Actions\EditAction::make()
                    ->url(fn (Model $record): string => ProjectResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('id', 'desc');
    }
}
