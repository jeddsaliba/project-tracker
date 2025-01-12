<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Traits\HasWidgetFilters;
use App\Models\Project;
use App\Support\CustomTrend;
use Carbon\Carbon;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Color\Rgb;

class ProjectsChart extends ChartWidget
{
    use HasWidgetFilters;
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Projects per Month';

    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Projects '.$this->getWidgetTitle();
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision:0,
                        },
                    },
                },
            }
        JS);
    }

    protected function getData(): array
    {
        $filter = $this->filters['dateFilter'] ?? 'daily';

        $projectData = CustomTrend::query(Project::query()->whereNull('actual_completed_date')->where('expected_completed_date', '>', Carbon::now()))
            ->between(
                start: $this->getStartDate(),
                end: $this->getEndDate(),
            )
            ->when($filter == 'daily', fn ($trend) => $trend->perHour())
            ->when($filter == 'weekly', fn ($trend) => $trend->perDay())
            ->when($filter == 'monthly', fn ($trend) => $trend->perMonth())
            ->count();

        $projectDueData = CustomTrend::query(Project::query()->whereNull('actual_completed_date')->where('expected_completed_date', '<=', Carbon::now()))
            ->between(
                start: $this->getStartDate(),
                end: $this->getEndDate(),
            )
            ->when($filter == 'daily', fn ($trend) => $trend->perHour())
            ->when($filter == 'weekly', fn ($trend) => $trend->perDay())
            ->when($filter == 'monthly', fn ($trend) => $trend->perMonth())
            ->count();

        $projectCompletedData = CustomTrend::query(Project::query()->whereNotNull('actual_completed_date'))
            ->between(
                start: $this->getStartDate(),
                end: $this->getEndDate(),
            )
            ->when($filter == 'daily', fn ($trend) => $trend->perHour())
            ->when($filter == 'weekly', fn ($trend) => $trend->perDay())
            ->when($filter == 'monthly', fn ($trend) => $trend->perMonth())
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Ongoing',
                    'data' => $projectData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => Rgb::fromString("rgb(".FilamentColor::getColors()['warning'][500].")")->toHex()->__toString(),
                    'borderColor' => Rgb::fromString("rgb(".FilamentColor::getColors()['warning'][500].")")->toHex()->__toString(),
                ],
                [
                    'label' => 'Due',
                    'data' => $projectDueData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => Rgb::fromString("rgb(".FilamentColor::getColors()['danger'][500].")")->toHex()->__toString(),
                    'borderColor' => Rgb::fromString("rgb(".FilamentColor::getColors()['danger'][500].")")->toHex()->__toString(),
                ],
                [
                    'label' => 'Completed',
                    'data' => $projectCompletedData->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => Rgb::fromString("rgb(".FilamentColor::getColors()['success'][500].")")->toHex()->__toString(),
                    'borderColor' => Rgb::fromString("rgb(".FilamentColor::getColors()['success'][500].")")->toHex()->__toString(),
                ],
            ],
            'labels' => $projectData->map(function (TrendValue $value) use ($filter) {
                return match ($filter) {
                    'daily' => Carbon::parse($value->date)->format('h:i A'),
                    'weekly' => Carbon::parse($value->date)->format('D'),
                    'monthly' => Carbon::parse($value->date)->format('M'),
                };
            }),
        ];
    }
}
