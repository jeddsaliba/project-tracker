<?php

namespace App\Filament\Widgets;

use App\Enums\CacheKeys;
use App\Enums\PaymentStatus;
use App\Filament\Widgets\Traits\HasWidgetFilters;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\CustomTrend;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class StatsOverview extends BaseWidget
{
    use HasWidgetFilters;
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $userData = $this->getNewUsers();
        $projectData = $this->getNewProjects();
        $taskData = $this->getNewTasks();

        return [
            Stat::make($userData['title'], $userData['value'])
                ->description((string) $userData['description'])
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($userData['chart'])
                ->color($userData['value'] == 0 ? 'warning' : 'success'),

            Stat::make($projectData['title'], $projectData['value'])
                ->description((string) $projectData['description'])
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($projectData['chart'])
                ->color($projectData['value'] == 0 ? 'warning' : 'success'),

            Stat::make($taskData['title'], $taskData['value'])
                ->description((string) $taskData['description'])
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($taskData['chart'])
                ->color($taskData['value'] == 0 ? 'warning' : 'success'),
        ];
    }

    private function getCacheKey()
    {
        // invalidate this on observers
        return match ($this->filters['dateFilter'] ?? 'daily') {
            'daily' => CacheKeys::DASHBOARD_DAILY,
            'weekly' => CacheKeys::DASHBOARD_WEEKLY,
            'monthly' => CacheKeys::DASHBOARD_MONTHLY,
        };
    }

    private function getNewUsers(): array
    {

        $filter = $this->filters['dateFilter'] ?? 'daily';

        $userData = CustomTrend::model(User::class)
            ->between(
                start: $this->getStartDate(),
                end: $this->getEndDate(),
            )
            ->when($filter == 'daily', fn ($trend) => $trend->perHour())
            ->when($filter == 'weekly', fn ($trend) => $trend->perDay())
            ->when($filter == 'monthly', fn ($trend) => $trend->perMonth())
            ->count();

        $usersFiltered = User::whereBetween('created_at', [$this->getStartDate(), $this->getEndDate()])->count();

        $totalUsers = User::count();

        $userIncrease = $this->getPercentageIncrease($usersFiltered, $totalUsers);

        return [
            'title' => 'New users '.$this->getWidgetTitle($filter),
            'value' => $usersFiltered,
            'description' => "$userIncrease% increase in users",
            'chart' => $userData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
        ];
    }

    private function getNewProjects(): array
    {
        $filter = $this->filters['dateFilter'] ?? 'daily';

        $projectData = CustomTrend::query(Project::query()->whereNotNull('actual_completed_date'))
            ->between(
                start: $this->getStartDate(),
                end: $this->getEndDate(),
            )
            ->when($filter == 'daily', fn ($trend) => $trend->perHour())
            ->when($filter == 'weekly', fn ($trend) => $trend->perDay())
            ->when($filter == 'monthly', fn ($trend) => $trend->perMonth())
            ->count();

        $projectsFiltered = Project::whereNotNull('actual_completed_date')
            ->whereBetween('created_at', [$this->getStartDate(), $this->getEndDate()])
            ->count();

        $totalNewProjects = Project::count();

        $projectIncrease = $this->getPercentageIncrease($projectsFiltered, $totalNewProjects);

        return [
            'title' => 'Completed projects '.$this->getWidgetTitle($filter),
            'value' => $projectsFiltered,
            'description' => "$projectIncrease% increase",
            'chart' => $projectData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
        ];
    }

    private function getNewTasks(): array
    {
        $filter = $this->filters['dateFilter'] ?? 'daily';

        $taskData = CustomTrend::query(Task::query()->whereNotNull('actual_completed_date'))
            ->between(
                start: $this->getStartDate(),
                end: $this->getEndDate(),
            )
            ->when($filter == 'daily', fn ($trend) => $trend->perHour())
            ->when($filter == 'weekly', fn ($trend) => $trend->perDay())
            ->when($filter == 'monthly', fn ($trend) => $trend->perMonth())
            ->count();

        $tasksFiltered = Task::whereNotNull('actual_completed_date')
            ->whereBetween('created_at', [$this->getStartDate(), $this->getEndDate()])
            ->count();

        $totalNewTasks = Task::count();

        $taskIncrease = $this->getPercentageIncrease($tasksFiltered, $totalNewTasks);

        return [
            'title' => 'Completed tasks '.$this->getWidgetTitle($filter),
            'value' => $tasksFiltered,
            'description' => "$taskIncrease% increase",
            'chart' => $taskData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
        ];
    }
}
