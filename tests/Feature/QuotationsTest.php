<?php

use App\Enums\QuotationStatus;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('quotations.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with the manage-quotations permission can visit the quotations page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('quotations.index'));
    $response->assertOk();
    $response->assertSee('Quotations');
});

test('users without the manage-quotations permission cannot visit the quotations page', function () {
    $user = User::factory()->withRole(Role::where('slug', 'intern')->firstOrFail())->create();
    $this->actingAs($user);

    $response = $this->get(route('quotations.index'));
    $response->assertForbidden();
});

test('it lists quotations with their lead names', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create(['name' => 'Vikram Patel']);
    Quotation::factory()->for($lead)->for($user, 'createdBy')->create();

    $response = $this->get(route('quotations.index'));
    $response->assertOk();
    $response->assertSee('Vikram Patel');
});

test('the "pending" pseudo-status excludes Accepted and Rejected quotations', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Assert on the quotation number rather than the lead's name — the
    // create-form modal's lead dropdown always lists every lead by name
    // regardless of which quotations are filtered into the table below it.
    $sent = Quotation::factory()->for(Lead::factory())->for($user, 'createdBy')->withStatus(QuotationStatus::Sent)->create();
    $accepted = Quotation::factory()->for(Lead::factory())->for($user, 'createdBy')->withStatus(QuotationStatus::Accepted)->create();

    Livewire::withQueryParams(['statusFilter' => 'pending'])
        ->test('pages::quotations.index')
        ->assertSee($sent->quotation_number)
        ->assertDontSee($accepted->quotation_number);
});

test('a quotation can be created via the form modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();

    Livewire::test('pages::quotations.form-modal')
        ->call('open')
        ->set('lead_id', (string) $lead->id)
        ->set('amount', '85000')
        ->call('save')
        ->assertHasNoErrors();

    expect(Quotation::where('lead_id', $lead->id)->exists())->toBeTrue();
});

test('a quotation gets an auto-generated number once created', function () {
    $lead = Lead::factory()->create();
    $quotation = Quotation::factory()->for($lead)->for(User::factory()->create(), 'createdBy')->create();

    expect($quotation->quotation_number)->toBe('QT-'.(1000 + $quotation->id));
});

test('a quotation can be deleted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();
    $quotation = Quotation::factory()->for($lead)->for($user, 'createdBy')->create();

    Livewire::test('pages::quotations.index')
        ->call('deleteQuotation', $quotation->id);

    expect(Quotation::find($quotation->id))->toBeNull();
});

test('moving a quotation past Prepared records a sent date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->create();
    $quotation = Quotation::factory()->for($lead)->for($user, 'createdBy')->create();

    Livewire::test('pages::quotations.index')
        ->call('updateStatus', $quotation->id, QuotationStatus::Sent->value);

    expect($quotation->fresh()->sent_at)->not->toBeNull();
});
