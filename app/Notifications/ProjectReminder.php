<?php

namespace App\Notifications;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ProjectReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Project $project)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expected_completed_date = Carbon::parse($this->project->expected_completed_date)->format(config('filament.date_format'));
        return (new MailMessage)
            ->subject(config('app.name') . ' | Project Reminder')
            ->greeting("Greetings, {$notifiable->name}!")
            ->line(new HtmlString("This is a reminder that your project: <strong>{$this->project->title}</strong> is due on <strong>{$expected_completed_date}</strong>."))
            ->line("Please update your project immediately.")
            ->action('Update Project Here', url(route('filament.app.resources.projects.edit', ['record' => $this->project->id])));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
