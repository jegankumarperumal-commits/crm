<?php

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('reports.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with the view-reports permission can visit the reports page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('reports.index'));
    $response->assertOk();
    $response->assertSee('Reports');
});

test('users without the view-reports permission cannot visit the reports page', function () {
    $user = User::factory()->withRole(Role::where('slug', 'video_editor')->firstOrFail())->create();
    $this->actingAs($user);

    $response = $this->get(route('reports.index'));
    $response->assertForbidden();
});

test('it reflects real lead counts in the pipeline breakdown', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Lead::factory()->withStatus(LeadStatus::Won)->count(2)->create();
    Lead::factory()->withStatus(LeadStatus::Lost)->create();

    $component = Livewire::test('pages::reports.index');

    expect($component->instance()->totalLeads)->toBe(3)
        ->and($component->instance()->wonCount)->toBe(2)
        ->and($component->instance()->lostCount)->toBe(1)
        ->and($component->instance()->conversionRate)->toBe(66.7);
});
