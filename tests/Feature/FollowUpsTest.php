<?php

use App\Enums\FollowUpAction;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('follow-ups.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with the manage-follow-ups permission can visit the follow-ups page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('follow-ups.index'));
    $response->assertOk();
    $response->assertSee('Follow-ups');
});

test('users without the manage-follow-ups permission cannot visit the follow-ups page', function () {
    $user = User::factory()->withRole(Role::where('slug', 'graphic_designer')->firstOrFail())->create();
    $this->actingAs($user);

    $response = $this->get(route('follow-ups.index'));
    $response->assertForbidden();
});

test('it lists follow-ups with their lead names', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create(['name' => 'Ritu Sharma']);
    FollowUp::factory()->for($lead)->for($user, 'createdBy')->create();

    $response = $this->get(route('follow-ups.index'));
    $response->assertOk();
    $response->assertSee('Ritu Sharma');
});

test('an overdue, incomplete follow-up is flagged as overdue', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();
    $followUp = FollowUp::factory()->for($lead)->for($user, 'createdBy')->overdue()->create();

    expect($followUp->isOverdue())->toBeTrue();
});

test('marking a follow-up done sets its completed_at timestamp', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();
    $followUp = FollowUp::factory()->for($lead)->for($user, 'createdBy')->create();

    Livewire::test('pages::follow-ups.index')
        ->call('markDone', $followUp->id);

    expect($followUp->fresh()->completed_at)->not->toBeNull();
});

test('a follow-up can be created via the form modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();

    Livewire::test('pages::follow-ups.form-modal')
        ->set('lead_id', (string) $lead->id)
        ->set('next_action', FollowUpAction::Call->value)
        ->set('due_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors();

    expect(FollowUp::where('lead_id', $lead->id)->exists())->toBeTrue();
});
