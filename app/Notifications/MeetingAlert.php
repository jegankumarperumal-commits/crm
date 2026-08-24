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

        [$subject, $greeting, $actionText, $purposeFallback] = match ($this->reason) {
            'created' => ["New meeting scheduled — {$lead->name}", "A new meeting was scheduled with {$lead->name}.", 'View Meeting', 'New Meeting'],
            'updated' => ["Meeting updated — {$lead->name}", "The meeting with {$lead->name} was updated.", 'View Meeting', 'Meeting Updated'],
            'missed' => ["⚠ Action needed — missed meeting with {$lead->name}", "The meeting with {$lead->name} has passed without being marked complete. Please follow up or update its status.", 'Update Meeting Status', 'Missed Meeting'],
            'upcoming' => ["⏰ Starting in 30 minutes — {$lead->name}", "Heads up — your meeting with {$lead->name} starts in about 30 minutes. Time to get ready.", 'View Meeting', 'Meeting Starting Soon'],
        };

        $purpose = filled($this->meeting->agenda) ? $this->meeting->agenda : $purposeFallback;

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line("Lead: {$leadLabel}")
            ->line('Type: '.$this->meeting->type->label())
            ->line('Scheduled: '.$this->meeting->scheduled_at->format('M j, Y g:i A'));

        if ($this->reason === 'missed') {
            $mail->error();
        }

        if (filled($this->meeting->agenda)) {
            $mail->line("Purpose: {$this->meeting->agenda}");
        }

        if (filled($lead->phone)) {
            $mail->line("Phone: {$lead->phone}");
        }

        if (filled($lead->email)) {
            $mail->line("Email: {$lead->email}");
        }

        if (filled($this->meeting->location)) {
            $mail->line("Location / Link: {$this->meeting->location}");
        }

        if ($this->meeting->assignedTo) {
            $mail->line("Assigned to: {$this->meeting->assignedTo->name}");
        }

        if (filled($this->meeting->notes)) {
            $mail->line("Notes: {$this->meeting->notes}");
        }

        return $mail
            ->action($actionText, route('meetings.index'))
            ->salutation('— '.config('app.name').' CRM')
            ->markdown('mail.meeting-alert', ['purpose' => $purpose]);
    }
}
