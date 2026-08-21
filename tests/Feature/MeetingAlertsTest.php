<?php

use App\Enums\MeetingType;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingAlert;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('scheduling a meeting sends a "created" alert to the configured recipient', function () {
    config(['alerts.recipient_email' => 'admin@example.test']);
    Notification::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();

    Livewire::test('pages::meetings.form-modal')
        ->set('lead_id', (string) $lead->id)
        ->set('type', MeetingType::PhoneCall->value)
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors();

    Notification::assertSentOnDemand(
        MeetingAlert::class,
        fn (MeetingAlert $notification, array $channels, object $notifiable) => $notification->reason === 'created'
            && $notifiable->routes['mail'] === 'admin@example.test'
    );
});

test('editing a meeting sends an "updated" alert', function () {
    config(['alerts.recipient_email' => 'admin@example.test']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $meeting = Meeting::factory()->for(Lead::factory())->for($user, 'createdBy')->create();

    Notification::fake();

    Livewire::test('pages::meetings.form-modal')
        ->call('open', $meeting->id)
        ->set('location', 'New Location')
        ->call('save');

    Notification::assertSentOnDemand(
        MeetingAlert::class,
        fn (MeetingAlert $notification) => $notification->reason === 'updated'
    );
});

test('no alert is sent when no recipient email is configured', function () {
    config(['alerts.recipient_email' => null]);
    Notification::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();

    Livewire::test('pages::meetings.form-modal')
        ->set('lead_id', (string) $lead->id)
        ->set('type', MeetingType::PhoneCall->value)
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors();

    Notification::assertNothingSent();
});

test('the send-alerts command emails upcoming and missed meetings, once each', function () {
    config(['alerts.recipient_email' => 'admin@example.test']);
    Notification::fake();

    $lead = Lead::factory()->create();
    $creator = User::factory()->create();

    $upcoming = Meeting::factory()->for($lead)->for($creator, 'createdBy')->create([
        'scheduled_at' => now()->addMinutes(15),
    ]);
    $missed = Meeting::factory()->for($lead)->for($creator, 'createdBy')->missed()->create();
    $farFuture = Meeting::factory()->for($lead)->for($creator, 'createdBy')->create([
        'scheduled_at' => now()->addDays(3),
    ]);

    $this->artisan('meetings:send-alerts')->assertSuccessful();

    Notification::assertSentOnDemandTimes(MeetingAlert::class, 2);
    expect($upcoming->fresh()->upcoming_alert_sent_at)->not->toBeNull()
        ->and($missed->fresh()->missed_alert_sent_at)->not->toBeNull()
        ->and($farFuture->fresh()->upcoming_alert_sent_at)->toBeNull();

    // Running it again shouldn't re-send for the same meetings.
    $this->artisan('meetings:send-alerts');
    Notification::assertSentOnDemandTimes(MeetingAlert::class, 2);
});
