<?php

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('roles.index'));
    $response->assertRedirect(route('login'));
});

test('founders can visit the roles page', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $response = $this->get(route('roles.index'));
    $response->assertOk();
    $response->assertSee('Roles');
});

test('users without the manage-team permission cannot visit the roles page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('roles.index'));
    $response->assertForbidden();
});

test('the default roles are seeded by the migration', function () {
    $slugs = Role::pluck('slug')->all();

    expect($slugs)->toContain('founder', 'business_analyst', 'web_developer', 'graphic_designer', 'video_editor', 'digital_marketing_executive', 'intern');
});

test('a new custom role can be created with chosen permissions', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    Livewire::test('pages::roles.role-form-modal')
        ->call('open')
        ->set('name', 'QA Tester')
        ->set('permissions', [Permission::ManageLeads->value])
        ->call('save')
        ->assertHasNoErrors();

    $role = Role::where('name', 'QA Tester')->first();

    expect($role)->not->toBeNull()
        ->and($role->is_founder)->toBeFalse()
        ->and($role->permissions)->toBe([Permission::ManageLeads->value]);
});

test('a role can be edited', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $role = Role::factory()->create(['name' => 'Old Name', 'permissions' => []]);

    Livewire::test('pages::roles.role-form-modal')
        ->call('open', $role->id)
        ->set('name', 'New Name')
        ->set('permissions', [Permission::ViewReports->value])
        ->call('save')
        ->assertHasNoErrors();

    expect($role->fresh()->name)->toBe('New Name')
        ->and($role->fresh()->permissions)->toBe([Permission::ViewReports->value]);
});

test('the founder role cannot be deleted', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $founderRole = Role::where('slug', 'founder')->firstOrFail();

    Livewire::test('pages::roles.index')
        ->call('deleteRole', $founderRole->id)
        ->assertHasErrors('role');

    expect(Role::find($founderRole->id))->not->toBeNull();
});

test('a role with users assigned cannot be deleted', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $role = Role::factory()->create();
    User::factory()->withRole($role)->create();

    Livewire::test('pages::roles.index')
        ->call('deleteRole', $role->id)
        ->assertHasErrors('role');

    expect(Role::find($role->id))->not->toBeNull();
});

test('an unused custom role can be deleted', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $role = Role::factory()->create();

    Livewire::test('pages::roles.index')
        ->call('deleteRole', $role->id)
        ->assertHasNoErrors();

    expect(Role::find($role->id))->toBeNull();
});
