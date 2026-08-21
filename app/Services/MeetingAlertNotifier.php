<?php

namespace App\Services;

use App\Models\Meeting;
use App\Notifications\MeetingAlert;
use Illuminate\Support\Facades\Notification;

/**
 * Sends meeting alerts to the configured recipient email. A no-op when no
 * recipient is configured, so alerts simply stay off until one is set.
 */
class MeetingAlertNotifier
{
    /**
     * @param  'created'|'updated'|'missed'|'upcoming'  $reason
     */
    public function send(Meeting $meeting, string $reason): void
    {
        $email = config('alerts.recipient_email');

        if (blank($email)) {
            return;
        }

        Notification::route('mail', $email)->notify(new MeetingAlert($meeting, $reason));
    }
}
