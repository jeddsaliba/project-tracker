<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectObserver
{
    public function creating(Project $project): void
    {
        if (!$project->created_by) $project->created_by = Auth::id();
    }
}
