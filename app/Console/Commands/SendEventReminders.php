<?php

namespace App\Console\Commands;

use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends reminder notifications for events starting within the next 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = \App\Models\Event::with('attendees.user')
        ->whereBetween('start_time', [now(), now()->addDay()])
        ->get();

        $eventCount = $events->count();
        $eventLabel = Str::plural('event', $eventCount);
        $attendeeCount = 0;
        $sentCount = 0;
        $skippedNoEmailCount = 0;

        $this->info("Found {$eventCount} {$eventLabel} in the next 24 hours.");

       $events->each(function ($event) use (&$attendeeCount, &$sentCount, &$skippedNoEmailCount) {
            $event->attendees->each(function ($attendee) use ($event, &$attendeeCount, &$sentCount, &$skippedNoEmailCount) {
                $attendeeCount++;
                $user = $attendee->user;

                if (!$user || empty($user->email)) {
                    $skippedNoEmailCount++;
                    return;
                }

                $user->notify(new EventReminderNotification($event));
                $sentCount++;
            });
        });

        $this->info("Attendees processed: {$attendeeCount}");
        $this->info("Emails sent: {$sentCount}");
        $this->info("Skipped (missing email): {$skippedNoEmailCount}");
        $this->info('Reminder notification run finished.');
    }
}
