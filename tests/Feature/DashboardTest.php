<?php

use App\Enums\InvoiceStatus;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Quotation;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('non-founder users see the existing placeholder dashboard, unchanged', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('sample data shown below until real CRM records exist', escape: false);
    $response->assertDontSee('Needs My Attention');
});

test('founders see the real founder dashboard', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Needs My Attention');
});

test('KPI cards reflect real database counts', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    Lead::factory()->withStatus(LeadStatus::New)->count(3)->create();
    Lead::factory()->withStatus(LeadStatus::Contacted)->count(2)->create();
    Lead::factory()->withStatus(LeadStatus::Won)->count(1)->create();
    Lead::factory()->withStatus(LeadStatus::Lost)->count(1)->create();

    // Attached to a Lost-status lead so these incidental records don't
    // inflate the New/Active/Won counts asserted below.
    $miscLead = Lead::factory()->withStatus(LeadStatus::Lost)->create();

    Meeting::factory()->for($miscLead)->for($founder, 'createdBy')->create(['scheduled_at' => today()->addHours(2)]);
    Meeting::factory()->for($miscLead)->for($founder, 'createdBy')->create(['scheduled_at' => today()->addDays(3)]);

    FollowUp::factory()->for($miscLead)->for($founder, 'createdBy')->overdue()->create();

    Quotation::factory()->for($miscLead)->for($founder, 'createdBy')->withStatus(QuotationStatus::Sent)->create();
    Quotation::factory()->for($miscLead)->for($founder, 'createdBy')->withStatus(QuotationStatus::Accepted)->create();

    $response = $this->get(route('dashboard'));

    // 3 New + Contacted/Won/Lost leads created above = 7 total, 3 New
    $response->assertViewHas('kpis', function (array $kpis) {
        return $kpis['newLeads'] === 3
            && $kpis['activeLeads'] === 5 // New(3) + Contacted(2), Won/Lost excluded
            && $kpis['todaysMeetings'] === 1
            && $kpis['overdueFollowUps'] === 1
            && $kpis['pendingQuotations'] === 1 // Sent only; Accepted is resolved
            && $kpis['wonDeals'] === 1;
    });
});

test('the lead pipeline shows every status with correct counts', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    Lead::factory()->withStatus(LeadStatus::New)->count(2)->create();
    Lead::factory()->withStatus(LeadStatus::Won)->create();

    $response = $this->get(route('dashboard'));

    $response->assertViewHas('pipeline', function (array $pipeline) {
        $byStatus = collect($pipeline)->keyBy(fn ($row) => $row['status']->value);

        return $byStatus['new']['count'] === 2
            && $byStatus['won']['count'] === 1
            && $byStatus['lost']['count'] === 0
            && count($pipeline) === count(LeadStatus::cases());
    });
});

test('conversion rate matches the Reports page formula', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    Lead::factory()->withStatus(LeadStatus::Won)->count(2)->create();
    Lead::factory()->withStatus(LeadStatus::Lost)->create();

    $response = $this->get(route('dashboard'));

    // 2 won / (2 won + 1 lost) = 66.7%
    $response->assertViewHas('conversion', fn (array $conversion) => $conversion['rate'] === 66.7);
});

test('financial summary reflects real quotation and invoice data', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $wonLead = Lead::factory()->withStatus(LeadStatus::Won)->create();
    Quotation::factory()->for($wonLead)->for($founder, 'createdBy')->withStatus(QuotationStatus::Accepted)->create(['amount' => 50000]);

    $client = Client::factory()->for($wonLead)->for($founder, 'createdBy')->create();
    Invoice::factory()->for($client)->for($founder, 'createdBy')->create(['amount' => 40000, 'paid_amount' => 15000, 'status' => InvoiceStatus::Sent]);
    Invoice::factory()->for($client)->for($founder, 'createdBy')->overdue()->create(['amount' => 10000, 'paid_amount' => 0]);

    $response = $this->get(route('dashboard'));

    $response->assertViewHas('financial', function (array $financial) {
        return $financial['wonDealValue'] === 50000.0
            && $financial['invoiced'] === 50000.0
            && $financial['received'] === 15000.0
            && $financial['outstanding'] === 35000.0
            && $financial['overdue'] === 10000.0;
    });
});

test('needs my attention lists overdue follow-ups, missed meetings, and pending quotations', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $lead = Lead::factory()->create(['name' => 'Ritu Sharma']);
    FollowUp::factory()->for($lead)->for($founder, 'createdBy')->overdue()->create();
    Meeting::factory()->for($lead)->for($founder, 'createdBy')->missed()->create();
    Quotation::factory()->for($lead)->for($founder, 'createdBy')->withStatus(QuotationStatus::Sent)->create();

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSeeInOrder(['Needs My Attention', 'Ritu Sharma']);
});

test('needs my attention shows an empty state when nothing is outstanding', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    $response = $this->get(route('dashboard'));
    $response->assertSee('No urgent actions. Everything is up to date.');
});

test('leads with no next action are active leads without a pending follow-up', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    Lead::factory()->withStatus(LeadStatus::New)->create(); // no follow-up, active — counts

    $leadWithFollowUp = Lead::factory()->withStatus(LeadStatus::New)->create();
    FollowUp::factory()->for($leadWithFollowUp)->for($founder, 'createdBy')->create(); // has a pending follow-up — excluded

    Lead::factory()->withStatus(LeadStatus::Won)->create(); // closed — excluded

    $response = $this->get(route('dashboard'));

    $response->assertViewHas('leadsWithNoNextAction', fn ($leads) => $leads->count() === 1);
});

test('the action required count sums overdue follow-ups, missed meetings, leads with no next action, and sent quotations', function () {
    $founder = User::factory()->founder()->create();
    $this->actingAs($founder);

    // Has an overdue follow-up, so it won't also count as "no next action".
    $lead = Lead::factory()->create();
    FollowUp::factory()->for($lead)->for($founder, 'createdBy')->overdue()->create();
    Meeting::factory()->for($lead)->for($founder, 'createdBy')->missed()->create();
    Quotation::factory()->for($lead)->for($founder, 'createdBy')->withStatus(QuotationStatus::Sent)->create();
    Lead::factory()->withStatus(LeadStatus::New)->create(); // active lead with no follow-up

    $response = $this->get(route('dashboard'));

    $response->assertViewHas('actionRequiredCount', 4);
});
