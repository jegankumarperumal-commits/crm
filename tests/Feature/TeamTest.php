<?php

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('team.index'));
    $response->assertRedirect(route('login'));
});

test('founders can visit the team page', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $response = $this->get(route('team.index'));
    $response->assertOk();
    $response->assertSee('Team');
});

test('users without the manage-team permission cannot visit the team page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('team.index'));
    $response->assertForbidden();
});

test('a founder always has every permission regardless of stored permissions', function () {
    $founder = User::factory()->founder()->create(['permissions' => []]);

    foreach (Permission::cases() as $permission) {
        expect($founder->hasPermission($permission))->toBeTrue();
    }
});

test('a new user can be created with a role and custom permissions', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $webDeveloper = Role::where('slug', 'web_developer')->firstOrFail();

    Livewire::test('pages::team.user-form-modal')
        ->call('open')
        ->set('name', 'Nina Developer')
        ->set('email', 'nina@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role_id', (string) $webDeveloper->id)
        ->set('permissions', [Permission::ViewReports->value])
        ->call('save')
        ->assertHasNoErrors();

    $user = User::where('email', 'nina@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role_id)->toBe($webDeveloper->id)
        ->and($user->hasPermission(Permission::ViewReports))->toBeTrue()
        ->and($user->hasPermission(Permission::ManageLeads))->toBeFalse();
});

test('selecting a role seeds its default permissions', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $businessAnalyst = Role::where('slug', 'business_analyst')->firstOrFail();

    Livewire::test('pages::team.user-form-modal')
        ->call('open')
        ->set('role_id', (string) $businessAnalyst->id)
        ->assertSet('permissions', $businessAnalyst->permissions);
});

test('an existing user\'s role and permissions can be edited', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $intern = Role::where('slug', 'intern')->firstOrFail();
    $user = User::factory()->withRole($intern)->create();

    Livewire::test('pages::team.user-form-modal')
        ->call('open', $user->id)
        ->set('permissions', [Permission::ManageLeads->value, Permission::ViewReports->value])
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->hasPermission(Permission::ManageLeads))->toBeTrue()
        ->and($user->hasPermission(Permission::ViewReports))->toBeTrue()
        ->and($user->hasPermission(Permission::ManageMeetings))->toBeFalse();
});

test('a team member can be deleted', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $intern = Role::where('slug', 'intern')->firstOrFail();
    $user = User::factory()->withRole($intern)->create();

    Livewire::test('pages::team.index')
        ->call('deleteUser', $user->id);

    expect(User::find($user->id))->toBeNull();
});

test('a user cannot delete their own account from the team page', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    Livewire::test('pages::team.index')
        ->call('deleteUser', $founder->id);

    expect(User::find($founder->id))->not->toBeNull();
});

test('the last founder cannot be deleted', function () {
    $founder = User::factory()->founder()->create();
    $manager = User::factory()->create(['permissions' => [Permission::ManageTeam->value]]);
    $this->actingAs($manager);

    Livewire::test('pages::team.index')
        ->call('deleteUser', $founder->id);

    expect(User::find($founder->id))->not->toBeNull();
});

test('the last founder cannot be demoted to another role', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $businessAnalyst = Role::where('slug', 'business_analyst')->firstOrFail();

    Livewire::test('pages::team.user-form-modal')
        ->call('open', $founder->id)
        ->set('role_id', (string) $businessAnalyst->id)
        ->call('save')
        ->assertHasErrors('role_id');

    expect($founder->fresh()->role->is_founder)->toBeTrue();
});
