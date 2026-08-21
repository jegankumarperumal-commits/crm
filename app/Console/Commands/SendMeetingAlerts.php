<?php

namespace App\Console\Commands;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Services\MeetingAlertNotifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('meetings:send-alerts')]
#[Description('Email upcoming-meeting reminders and missed-meeting alerts.')]
class SendMeetingAlerts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MeetingAlertNotifier $notifier): int
    {
        $now = now();

        $upcoming = Meeting::query()
            ->where('status', MeetingStatus::Scheduled)
            ->whereNull('upcoming_alert_sent_at')
            ->whereBetween('scheduled_at', [$now, $now->clone()->addMinutes(30)])
            ->with('lead')
            ->get();

        foreach ($upcoming as $meeting) {
            $notifier->send($meeting, 'upcoming');
            $meeting->upcoming_alert_sent_at = $now;
            $meeting->save();
        }

        $missed = Meeting::query()
            ->where('status', MeetingStatus::Scheduled)
            ->whereNull('missed_alert_sent_at')
            ->where('scheduled_at', '<', $now)
            ->with('lead')
            ->get();

        foreach ($missed as $meeting) {
            $notifier->send($meeting, 'missed');
            $meeting->missed_alert_sent_at = $now;
            $meeting->save();
        }

        $this->info("Sent {$upcoming->count()} upcoming and {$missed->count()} missed meeting alerts.");

        return self::SUCCESS;
    }
}
