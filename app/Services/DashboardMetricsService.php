<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\LeadStatus;
use App\Enums\MeetingStatus;
use App\Enums\QuotationStatus;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;

/**
 * Aggregate CRM metrics shared by the Reports page and the Founder dashboard,
 * kept in one place so both surfaces agree on what each number means.
 */
class DashboardMetricsService
{
    /**
     * Lead statuses that count as "closed" — no longer active in the pipeline.
     *
     * @return array<int, LeadStatus>
     */
    protected function closedLeadStatuses(): array
    {
        return [LeadStatus::Won, LeadStatus::Lost];
    }

    public function totalLeadsCount(): int
    {
        return Lead::query()->count();
    }

    public function newLeadsCount(): int
    {
        return Lead::query()->where('status', LeadStatus::New)->count();
    }

    public function activeLeadsCount(): int
    {
        return Lead::query()->whereNotIn('status', $this->closedLeadStatuses())->count();
    }

    public function wonLeadsCount(): int
    {
        return Lead::query()->where('status', LeadStatus::Won)->count();
    }

    public function lostLeadsCount(): int
    {
        return Lead::query()->where('status', LeadStatus::Lost)->count();
    }

    public function conversionRate(): float
    {
        $won = $this->wonLeadsCount();
        $closed = $won + $this->lostLeadsCount();

        return $closed > 0 ? round(($won / $closed) * 100, 1) : 0.0;
    }

    /**
     * @return array<int, array{status: LeadStatus, count: int}>
     */
    public function leadPipeline(): array
    {
        $counts = Lead::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(LeadStatus::cases())
            ->map(fn (LeadStatus $status) => [
                'status' => $status,
                'count' => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }

    /**
     * Active leads with no pending follow-up — nobody has a next step queued
     * for them, so they're the ones that silently stall without a nudge.
     */
    public function leadsWithNoNextActionCount(): int
    {
        return Lead::query()
            ->whereNotIn('status', $this->closedLeadStatuses())
            ->whereDoesntHave('followUps', fn ($query) => $query->whereNull('completed_at'))
            ->count();
    }

    /**
     * @return Collection<int, Lead>
     */
    public function leadsWithNoNextAction(int $limit = 5): Collection
    {
        return Lead::query()
            ->whereNotIn('status', $this->closedLeadStatuses())
            ->whereDoesntHave('followUps', fn ($query) => $query->whereNull('completed_at'))
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function overdueFollowUpsCount(): int
    {
        return FollowUp::query()->whereNull('completed_at')->whereDate('due_at', '<', today())->count();
    }

    /**
     * @return Collection<int, FollowUp>
     */
    public function overdueFollowUps(int $limit = 5): Collection
    {
        return FollowUp::query()
            ->with(['lead'])
            ->whereNull('completed_at')
            ->whereDate('due_at', '<', today())
            ->orderBy('due_at')
            ->limit($limit)
            ->get();
    }

    public function todaysMeetingsCount(): int
    {
        return Meeting::query()->whereDate('scheduled_at', today())->count();
    }

    /**
     * @return Collection<int, Meeting>
     */
    public function todaysMeetings(): Collection
    {
        return Meeting::query()
            ->with(['lead'])
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();
    }

    public function missedMeetingsCount(): int
    {
        return Meeting::query()->where('status', MeetingStatus::Scheduled)->where('scheduled_at', '<', now())->count();
    }

    /**
     * @return Collection<int, Meeting>
     */
    public function missedMeetings(int $limit = 5): Collection
    {
        return Meeting::query()
            ->with(['lead'])
            ->where('status', MeetingStatus::Scheduled)
            ->where('scheduled_at', '<', now())
            ->orderByDesc('scheduled_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Quotations not yet resolved either way (accepted or rejected).
     */
    public function pendingQuotationsCount(): int
    {
        return Quotation::query()->whereNotIn('status', [QuotationStatus::Accepted, QuotationStatus::Rejected])->count();
    }

    /**
     * Quotations already sent to the client and awaiting their response —
     * the subset of "pending" that actually needs a follow-up nudge.
     */
    public function quotationsAwaitingResponseCount(): int
    {
        return Quotation::query()->whereIn('status', [QuotationStatus::Sent, QuotationStatus::Negotiation])->count();
    }

    /**
     * @return Collection<int, Quotation>
     */
    public function quotationsAwaitingResponse(int $limit = 5): Collection
    {
        return Quotation::query()
            ->with(['lead'])
            ->whereIn('status', [QuotationStatus::Sent, QuotationStatus::Negotiation])
            ->orderBy('sent_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Total value of quotations still in play (not rejected).
     */
    public function quotationPipelineValue(): float
    {
        return (float) Quotation::query()->where('status', '!=', QuotationStatus::Rejected->value)->sum('amount');
    }

    /**
     * Value of accepted quotations tied to leads that went on to Won.
     */
    public function wonDealValue(): float
    {
        return (float) Quotation::query()
            ->where('status', QuotationStatus::Accepted)
            ->whereHas('lead', fn ($query) => $query->where('status', LeadStatus::Won))
            ->sum('amount');
    }

    public function totalInvoiced(): float
    {
        return (float) Invoice::query()->sum('amount');
    }

    public function totalReceived(): float
    {
        return (float) Invoice::query()->sum('paid_amount');
    }

    public function totalOutstanding(): float
    {
        return max(0.0, $this->totalInvoiced() - $this->totalReceived());
    }

    public function totalOverdueInvoiceAmount(): float
    {
        return (float) Invoice::query()
            ->whereNotNull('due_at')
            ->where('due_at', '<', today())
            ->where('status', '!=', InvoiceStatus::Paid->value)
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
            ->value('total');
    }

    /**
     * Count behind the "Action Required" indicator: everything in
     * "Needs My Attention" that the Founder hasn't dealt with yet.
     */
    public function actionRequiredCount(): int
    {
        return $this->overdueFollowUpsCount()
            + $this->missedMeetingsCount()
            + $this->leadsWithNoNextActionCount()
            + $this->quotationsAwaitingResponseCount();
    }
}
