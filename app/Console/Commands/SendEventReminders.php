<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send email reminders for events occurring in the next 1-3 days';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $threeDaysLater = now()->addDays(3)->endOfDay();

        $upcomingEvents = Event::where('status', 'upcoming')
            ->whereBetween('date', [$today->format('Y-m-d'), $threeDaysLater->format('Y-m-d')])
            ->orderBy('date')
            ->get()
            ->map(fn (Event $e) => [
                'id' => $e->id,
                'title' => $e->title,
                'date' => $e->date,
                'location' => $e->location,
            ])
            ->toArray();

        if (empty($upcomingEvents)) {
            $this->info('No upcoming events to remind.');
            return self::SUCCESS;
        }

        $users = User::all();
        foreach ($users as $user) {
            if ($user->email) {
                Mail::to($user->email)->send(new EventReminderMail($upcomingEvents));
                $this->info("Sent reminder to {$user->email}");
            }
        }

        $this->info('Reminders sent successfully.');
        return self::SUCCESS;
    }
}
