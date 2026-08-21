<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardMetricsService $metrics): View
    {
        $user = $request->user();

        if (! $user->role?->is_founder) {
            return view('dashboard');
        }

        return view('pages.dashboard.founder', [
            'kpis' => [
                'newLeads' => $metrics->newLeadsCount(),
                'activeLeads' => $metrics->activeLeadsCount(),
                'todaysMeetings' => $metrics->todaysMeetingsCount(),
                'overdueFollowUps' => $metrics->overdueFollowUpsCount(),
                'pendingQuotations' => $metrics->pendingQuotationsCount(),
                'wonDeals' => $metrics->wonLeadsCount(),
            ],
            'overdueFollowUps' => $metrics->overdueFollowUps(),
            'missedMeetings' => $metrics->missedMeetings(),
            'leadsWithNoNextAction' => $metrics->leadsWithNoNextAction(),
            'quotationsAwaitingResponse' => $metrics->quotationsAwaitingResponse(),
            'attentionCounts' => [
                'overdueFollowUps' => $metrics->overdueFollowUpsCount(),
                'missedMeetings' => $metrics->missedMeetingsCount(),
                'leadsWithNoNextAction' => $metrics->leadsWithNoNextActionCount(),
                'quotationsAwaitingResponse' => $metrics->quotationsAwaitingResponseCount(),
            ],
            'todaysMeetingsList' => $metrics->todaysMeetings(),
            'pipeline' => $metrics->leadPipeline(),
            'conversion' => [
                'total' => $metrics->totalLeadsCount(),
                'active' => $metrics->activeLeadsCount(),
                'won' => $metrics->wonLeadsCount(),
                'lost' => $metrics->lostLeadsCount(),
                'rate' => $metrics->conversionRate(),
            ],
            'financial' => [
                'quotationValue' => $metrics->quotationPipelineValue(),
                'wonDealValue' => $metrics->wonDealValue(),
                'invoiced' => $metrics->totalInvoiced(),
                'received' => $metrics->totalReceived(),
                'outstanding' => $metrics->totalOutstanding(),
                'overdue' => $metrics->totalOverdueInvoiceAmount(),
            ],
            'actionRequiredCount' => $metrics->actionRequiredCount(),
        ]);
    }
}
