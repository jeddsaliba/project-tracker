<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Enums\TaskListTab;
use App\Filament\Resources\TaskResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All')->icon('heroicon-m-list-bullet'),
            TaskListTab::DUE->value => Tab::make()
                ->query(fn ($query) => $query->whereNull('actual_completed_date')->where('expected_completed_date', '<=', Carbon::now()))
                ->icon(TaskListTab::DUE->getIcon()),
            TaskListTab::ONGOING->value => Tab::make()
                ->query(fn ($query) => $query->whereNull('actual_completed_date')->where('expected_completed_date', '>', Carbon::now()))
                ->icon(TaskListTab::ONGOING->getIcon()),
            TaskListTab::COMPLETED->value => Tab::make()
                ->query(fn ($query) => $query->whereNotNull('actual_completed_date'))
                ->icon(TaskListTab::COMPLETED->getIcon()),
        ];
    }
}
