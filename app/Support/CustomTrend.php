<?php

namespace App\Support;

use Flowframe\Trend\Trend;
use Illuminate\Support\Traits\Conditionable;

/**
 * CustomTrend extends the Trend class to add conditional logic.
 *
 * @method static static model(string $model)
 * @method static static query(Builder $builder)
 * @method static static between($start, $end)
 * @method static static perHour()
 * @method static static perDay()
 * @method static static perMonth()
 * @method static static perWeek()
 */
class CustomTrend extends Trend
{
    use Conditionable;
}
