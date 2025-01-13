<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskObserver
{
    public function creating(Task $task): void
    {
        if (!$task->created_by) $task->created_by = Auth::id();
    }
}
