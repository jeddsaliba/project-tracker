<?php

namespace App\Filament\Widgets\Traits;

/**
 * Widgets inheriting this class must also inherit InteractsWithPageFilters
 */
trait HasWidgetFilters
{
    /**
     * calculate data increase in percentage
     *
     * @param  $filteredData  int refers to the sum / count of filtered data i.e. daily, weekly, monthly
     * @param  $totalData  int refers to the sum / count of total data without filters
     * @return int
     */
    private function getPercentageIncrease(int $filteredData, int $totalData): float
    {
        // Initial data is the total data minus the filtered data
        $initialData = $totalData - $filteredData;

        // Project just started and no initial data
        if ($initialData == 0 && $filteredData > 0) {
            return 100;  // Assuming 100% increase if there's new data but no initial data
        }

        // Prevent division by zero, return 0% increase if there's no initial data and no filtered data
        if ($initialData == 0 && $filteredData == 0) {
            return 0;
        }

        return round(($filteredData / $initialData) * 100, 2);
    }

    /**
     * calculate data increase in value
     */
    private function getValueIncrease(int $filteredData, int $totalData): float
    {
        $initialData = $totalData - $filteredData;

        return round($totalData - $initialData);
    }

    private function getWidgetTitle(): string
    {
        $filter = $this->filters['dateFilter'] ?? 'daily';

        return match ($filter) {
            'daily' => 'today',
            'weekly' => 'this week',
            'monthly' => 'this year',
        };
    }

    private function getStartDate()
    {
        $filter = $this->filters['dateFilter'] ?? 'daily';
        switch ($filter) {
            case 'daily':
                return now()->startOfDay();
            case 'weekly':
                return now()->startOfWeek();
            case 'monthly':
                return now()->startOfYear();
            default:
                return now()->startOfDay();
        }
    }

    private function getEndDate()
    {
        $filter = $this->filters['dateFilter'] ?? 'daily';
        switch ($filter) {
            case 'daily':
                return now()->endOfDay();
            case 'weekly':
                return now()->endOfWeek();
            case 'monthly':
                return now()->endOfYear();
            default:
                return now()->endOfDay();
        }
    }
}
