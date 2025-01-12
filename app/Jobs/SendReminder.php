<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Task;
use App\Notifications\ProjectReminder;
use App\Notifications\TaskReminder;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendReminder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('SendReminder running...');
        $this->sendProjectReminder();
        $this->sendTaskReminder();
    }

    private function sendProjectReminder()
    {
        $projects = Project::whereNull('actual_completed_date')
            ->where('expected_completed_date', '<=', Carbon::now())->get();
        $projects->each(function ($project) {
            $project->users->each(function ($user) use ($project) {
                Log::info('Send project reminder', ['data' => $user]);
                $user->notify(new ProjectReminder($project));
            });
        });
    }

    private function sendTaskReminder()
    {
        $tasks = Task::whereNull('actual_completed_date')
            ->where('expected_completed_date', '<=', Carbon::now())->get();
        $tasks->each(function ($task) {
            $task->users->each(function ($user) use ($task) {
                Log::info('Send task reminder', ['data' => $user]);
                $user->notify(new TaskReminder($task));
            });
        });
    }
}
