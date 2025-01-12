<?php

namespace App\Enums;

enum CacheKeys: string
{
    case DASHBOARD_DAILY = 'dashboard_daily';
    case DASHBOARD_WEEKLY = 'dashboard_weekly';
    case DASHBOARD_MONTHLY = 'dashboard_monthly';
}
