<?php

use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('clients.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with the manage-clients-invoices permission can visit the clients page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('clients.index'));
    $response->assertOk();
    $response->assertSee('Clients & Invoices');
});

test('users without the manage-clients-invoices permission cannot visit the clients page', function () {
    $user = User::factory()->withRole(Role::where('slug', 'web_developer')->firstOrFail())->create();
    $this->actingAs($user);

    $response = $this->get(route('clients.index'));
    $response->assertForbidden();
});

test('a Won lead can be converted to a client', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->withStatus(LeadStatus::Won)->create();

    Livewire::test('pages::clients.client-form-modal')
        ->set('lead_id', (string) $lead->id)
        ->call('save')
        ->assertHasNoErrors();

    expect(Client::where('lead_id', $lead->id)->exists())->toBeTrue();
});

test('a lead that is not Won cannot be converted to a client', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = Lead::factory()->withStatus(LeadStatus::Negotiation)->create();

    Livewire::test('pages::clients.client-form-modal')
        ->set('lead_id', (string) $lead->id)
        ->call('save')
        ->assertHasErrors('lead_id');

    expect(Client::where('lead_id', $lead->id)->exists())->toBeFalse();
});

test('an invoice can be created for a client', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $client = Client::factory()->for($user, 'createdBy')->create();

    Livewire::test('pages::clients.invoice-form-modal')
        ->call('open')
        ->set('client_id', (string) $client->id)
        ->set('amount', '50000')
        ->set('paid_amount', '0')
        ->call('save')
        ->assertHasNoErrors();

    expect(Invoice::where('client_id', $client->id)->exists())->toBeTrue();
});

test('an invoice past its due date and not fully paid is flagged as overdue', function () {
    $client = Client::factory()->for(User::factory()->create(), 'createdBy')->create();
    $invoice = Invoice::factory()->for($client)->for(User::factory()->create(), 'createdBy')->overdue()->create();

    expect($invoice->isOverdue())->toBeTrue();
});

test('an invoice gets an auto-generated number once created', function () {
    $client = Client::factory()->for(User::factory()->create(), 'createdBy')->create();
    $invoice = Invoice::factory()->for($client)->for(User::factory()->create(), 'createdBy')->create();

    expect($invoice->invoice_number)->toBe('INV-'.(2000 + $invoice->id));
});

test('a client can be deleted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $client = Client::factory()->for($user, 'createdBy')->create();

    Livewire::test('pages::clients.index')
        ->call('deleteClient', $client->id);

    expect(Client::find($client->id))->toBeNull();
});

test('an invoice can be deleted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $client = Client::factory()->for($user, 'createdBy')->create();
    $invoice = Invoice::factory()->for($client)->for($user, 'createdBy')->create();

    Livewire::test('pages::clients.index')
        ->call('deleteInvoice', $invoice->id);

    expect(Invoice::find($invoice->id))->toBeNull();
});
