<?php

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

function leadsImportCsv(string $rows): UploadedFile
{
    $header = "Name,Company,Email,Phone,Source,Status,Notes\n";

    return UploadedFile::fake()->createWithContent('leads.csv', $header.$rows);
}

test('importing a CSV creates leads and resolves source and status by name', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = leadsImportCsv("Jane Doe,Acme Corp,jane@acme.com,+1 555 0100,WhatsApp,Contacted,Interested lead\n");

    Livewire::test('pages::leads.import-modal')
        ->call('open')
        ->set('file', $file)
        ->call('import')
        ->assertSet('importedCount', 1)
        ->assertSet('failureMessages', []);

    $lead = Lead::where('email', 'jane@acme.com')->firstOrFail();

    expect($lead->name)->toBe('Jane Doe')
        ->and($lead->company)->toBe('Acme Corp')
        ->and($lead->source->value)->toBe('whatsapp')
        ->and($lead->status->value)->toBe('contacted');
});

test('a purely numeric phone number imports without failing the string rule', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = leadsImportCsv("Jane Doe,,jane@acme.com,9878678900,,,\n");

    Livewire::test('pages::leads.import-modal')
        ->call('open')
        ->set('file', $file)
        ->call('import')
        ->assertSet('importedCount', 1)
        ->assertSet('failureMessages', []);

    $lead = Lead::where('email', 'jane@acme.com')->firstOrFail();

    expect($lead->phone)->toBe('9878678900');
});

test('rows missing a name are skipped and reported, valid rows still import', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = leadsImportCsv(
        "Jane Doe,,jane@acme.com,,,,\n".
        ",Missing Name Co,noone@acme.com,,,,\n"
    );

    Livewire::test('pages::leads.import-modal')
        ->call('open')
        ->set('file', $file)
        ->call('import')
        ->assertSet('importedCount', 1);

    expect(Lead::where('email', 'jane@acme.com')->exists())->toBeTrue()
        ->and(Lead::where('email', 'noone@acme.com')->exists())->toBeFalse();
});

test('rows duplicating an existing lead email are skipped', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Lead::factory()->create(['email' => 'existing@acme.com']);

    $file = leadsImportCsv("New Name,,existing@acme.com,,,,\n");

    $component = Livewire::test('pages::leads.import-modal')
        ->call('open')
        ->set('file', $file)
        ->call('import')
        ->assertSet('importedCount', 0);

    expect($component->get('failureMessages'))->toHaveCount(1)
        ->and(Lead::where('email', 'existing@acme.com')->count())->toBe(1);
});

test('leads without a recognizable source or status default to Bulk Import and New', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = leadsImportCsv("No Source Lead,,,,,,\n");

    Livewire::test('pages::leads.import-modal')
        ->call('open')
        ->set('file', $file)
        ->call('import')
        ->assertSet('importedCount', 1);

    $lead = Lead::where('name', 'No Source Lead')->firstOrFail();

    expect($lead->source->value)->toBe('bulk_import')
        ->and($lead->status->value)->toBe('new');
});
