<?php

use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('meetings.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with the manage-meetings permission can visit the meetings page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('meetings.index'));
    $response->assertOk();
    $response->assertSee('Meetings');
});

test('users without the manage-meetings permission cannot visit the meetings page', function () {
    $user = User::factory()->withRole(Role::where('slug', 'video_editor')->firstOrFail())->create();
    $this->actingAs($user);

    $response = $this->get(route('meetings.index'));
    $response->assertForbidden();
});

test('it lists meetings with their lead names', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create(['name' => 'Ritu Sharma']);
    Meeting::factory()->for($lead)->for($user, 'createdBy')->create();

    $response = $this->get(route('meetings.index'));
    $response->assertOk();
    $response->assertSee('Ritu Sharma');
});

test('a scheduled meeting in the past is flagged as missed', function () {
    $lead = Lead::factory()->create();
    $meeting = Meeting::factory()->for($lead)->for(User::factory()->create(), 'createdBy')->missed()->create();

    expect($meeting->isMissed())->toBeTrue();
});

test('updating a meeting outcome persists the new status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();
    $meeting = Meeting::factory()->for($lead)->for($user, 'createdBy')->create();

    Livewire::test('pages::meetings.index')
        ->call('updateStatus', $meeting->id, MeetingStatus::Completed->value);

    expect($meeting->fresh()->status)->toBe(MeetingStatus::Completed);
});

test('the dateFilter query string pre-filters meetings to today', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $todayLead = Lead::factory()->create(['name' => 'Today Client']);
    $laterLead = Lead::factory()->create(['name' => 'Next Week Client']);

    // The "location" column only ever renders inside a table row, unlike the
    // lead's name which also appears in the "Schedule Meeting" modal's lead
    // dropdown regardless of which meetings are filtered into view.
    Meeting::factory()->for($todayLead)->for($user, 'createdBy')->create(['scheduled_at' => today()->addHours(2), 'location' => 'Today HQ']);
    Meeting::factory()->for($laterLead)->for($user, 'createdBy')->create(['scheduled_at' => today()->addWeek(), 'location' => 'Next Week Office']);

    Livewire::withQueryParams(['dateFilter' => 'today'])
        ->test('pages::meetings.index')
        ->assertSee('Today HQ')
        ->assertDontSee('Next Week Office');
});

test('a meeting can be scheduled via the form modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();

    Livewire::test('pages::meetings.form-modal')
        ->set('lead_id', (string) $lead->id)
        ->set('type', MeetingType::PhoneCall->value)
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors();

    expect(Meeting::where('lead_id', $lead->id)->exists())->toBeTrue();
});
