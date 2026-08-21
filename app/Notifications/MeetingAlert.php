<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingAlert extends Notification
{
    /**
     * @param  'created'|'updated'|'missed'|'upcoming'  $reason
     */
    public function __construct(
        public Meeting $meeting,
        public string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->meeting->lead;
        $leadLabel = $lead->name.($lead->company ? " ({$lead->company})" : '');

        [$subject, $greeting] = match ($this->reason) {
            'created' => ["New meeting scheduled — {$lead->name}", 'A new meeting was scheduled.'],
            'updated' => ["Meeting updated — {$lead->name}", 'A meeting was updated.'],
            'missed' => ["Missed meeting — {$lead->name}", 'A scheduled meeting has passed without being marked complete.'],
            'upcoming' => ["Meeting starting soon — {$lead->name}", 'You have a meeting starting in about 30 minutes.'],
        };

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line("Lead: {$leadLabel}")
            ->line('Type: '.$this->meeting->type->label())
            ->line('Scheduled: '.$this->meeting->scheduled_at->format('M j, Y g:i A'));

        if (filled($this->meeting->location)) {
            $mail->line("Location / Link: {$this->meeting->location}");
        }

        return $mail->action('View Meetings', route('meetings.index'));
    }
}
