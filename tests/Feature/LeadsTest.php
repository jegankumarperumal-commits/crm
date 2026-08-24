<?php

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('leads.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with the manage-leads permission can visit the leads page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('leads.index'));
    $response->assertOk();
    $response->assertSee('Leads');
});

test('users without the manage-leads permission cannot visit the leads page', function () {
    $user = User::factory()->withRole(Role::where('slug', 'web_developer')->firstOrFail())->create();
    $this->actingAs($user);

    $response = $this->get(route('leads.index'));
    $response->assertForbidden();
});

test('the statusFilter query string pre-filters the leads list', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Lead::factory()->withStatus(LeadStatus::New)->create(['name' => 'Fresh Lead']);
    Lead::factory()->withStatus(LeadStatus::Won)->create(['name' => 'Closed Deal']);

    Livewire::withQueryParams(['statusFilter' => 'new'])
        ->test('pages::leads.index')
        ->assertSee('Fresh Lead')
        ->assertDontSee('Closed Deal');
});

test('the "active" pseudo-status excludes Won and Lost leads', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Lead::factory()->withStatus(LeadStatus::New)->create(['name' => 'Open Lead']);
    Lead::factory()->withStatus(LeadStatus::Won)->create(['name' => 'Won Lead']);
    Lead::factory()->withStatus(LeadStatus::Lost)->create(['name' => 'Lost Lead']);

    Livewire::withQueryParams(['statusFilter' => 'active'])
        ->test('pages::leads.index')
        ->assertSee('Open Lead')
        ->assertDontSee('Won Lead')
        ->assertDontSee('Lost Lead');
});

test('openView loads the lead and dispatches the view modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create(['name' => 'Viewable Lead']);

    Livewire::test('pages::leads.index')
        ->call('openView', $lead->id)
        ->assertDispatched('show-view-lead-modal')
        ->assertSee('Viewable Lead');
});

test('a lead can be deleted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();

    Livewire::test('pages::leads.index')
        ->call('deleteLead', $lead->id);

    expect(Lead::find($lead->id))->toBeNull();
});
