<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Enums\ProjectListTab;
use App\Filament\Resources\ProjectResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

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
            ProjectListTab::DUE->value => Tab::make()
                ->query(fn ($query) => $query->whereNull('actual_completed_date')->where('expected_completed_date', '<=', Carbon::now()))
                ->icon(ProjectListTab::DUE->getIcon()),
            ProjectListTab::ONGOING->value => Tab::make()
                ->query(fn ($query) => $query->whereNull('actual_completed_date')->where('expected_completed_date', '>', Carbon::now()))
                ->icon(ProjectListTab::ONGOING->getIcon()),
            ProjectListTab::COMPLETED->value => Tab::make()
                ->query(fn ($query) => $query->whereNotNull('actual_completed_date'))
                ->icon(ProjectListTab::COMPLETED->getIcon()),
        ];
    }
}
