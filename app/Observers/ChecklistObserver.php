<?php

namespace App\Observers;

use App\Models\Checklist;
use Illuminate\Support\Facades\Auth;

class ChecklistObserver
{
    public function creating(Checklist $checklist): void
    {
        if (!$checklist->created_by) $checklist->created_by = Auth::id();
    }
}
