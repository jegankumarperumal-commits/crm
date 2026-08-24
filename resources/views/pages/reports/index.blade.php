<?php

use App\Enums\LeadSource;
use App\Models\Lead;
use App\Services\DashboardMetricsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reports')] #[Layout('layouts::coolapp')] class extends Component {
    #[Computed]
    public function totalLeads(): int
    {
        return app(DashboardMetricsService::class)->totalLeadsCount();
    }

    #[Computed]
    public function wonCount(): int
    {
        return app(DashboardMetricsService::class)->wonLeadsCount();
    }

    #[Computed]
    public function lostCount(): int
    {
        return app(DashboardMetricsService::class)->lostLeadsCount();
    }

    #[Computed]
    public function conversionRate(): float
    {
        return app(DashboardMetricsService::class)->conversionRate();
    }

    /**
     * @return array<int, array{status: \App\Enums\LeadStatus, count: int}>
     */
    #[Computed]
    public function pipeline(): array
    {
        return app(DashboardMetricsService::class)->leadPipeline();
    }

    /**
     * @return array<int, array{source: LeadSource, count: int}>
     */
    #[Computed]
    public function sources(): array
    {
        $counts = Lead::query()
            ->selectRaw('source, count(*) as aggregate')
            ->groupBy('source')
            ->pluck('aggregate', 'source');

        return collect(LeadSource::cases())
            ->map(fn (LeadSource $source) => [
                'source' => $source,
                'count' => (int) ($counts[$source->value] ?? 0),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    #[Computed]
    public function overdueFollowUps(): int
    {
        return app(DashboardMetricsService::class)->overdueFollowUpsCount();
    }

    #[Computed]
    public function missedMeetings(): int
    {
        return app(DashboardMetricsService::class)->missedMeetingsCount();
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Reports') }}</h1>
        <p class="subtitle">{{ __('Pipeline and conversion summary — pulled from your real leads data.') }}</p>
    </div>
</div>

<div class="row row-tight">
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Total Leads') }}</p>
                <span class="stat-card__icon stat-card__icon--c2"><i class="fa-solid fa-user-tag" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->totalLeads }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Won') }}</p>
                <span class="stat-card__icon stat-card__icon--success"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->wonCount }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Lost') }}</p>
                <span class="stat-card__icon stat-card__icon--danger"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->lostCount }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Conversion Rate') }}</p>
                <span class="stat-card__icon stat-card__icon--c3"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->conversionRate }}%</p>
        </article>
    </div>
</div>

<div class="row row-tight" style="margin-top: 16px;">
    <div class="col-lg-6">
        <section class="m-card" aria-labelledby="pipeline-title">
            <header class="m-card__header">
                <div>
                    <h2 class="m-card__title" id="pipeline-title">{{ __('Pipeline by status') }}</h2>
                    <p class="m-card__subtitle">{{ __('How many leads are sitting in each stage.') }}</p>
                </div>
            </header>
            <table class="m-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col" class="num">{{ __('Leads') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->pipeline as $row)
                        <tr>
                            <td>{{ $row['status']->label() }}</td>
                            <td class="num">{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="m-card" aria-labelledby="sources-title">
            <header class="m-card__header">
                <div>
                    <h2 class="m-card__title" id="sources-title">{{ __('Leads by source') }}</h2>
                    <p class="m-card__subtitle">{{ __('Where your leads are coming from.') }}</p>
                </div>
            </header>
            <table class="m-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Source') }}</th>
                        <th scope="col" class="num">{{ __('Leads') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->sources as $row)
                        <tr>
                            <td>{{ $row['source']->label() }}</td>
                            <td class="num">{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
</div>

<div class="row row-tight" style="margin-top: 16px;">
    <div class="col-lg-6">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Overdue Follow-ups') }}</p>
                <span class="stat-card__icon stat-card__icon--danger"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->overdueFollowUps }}</p>
        </article>
    </div>
    <div class="col-lg-6">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Missed Meetings') }}</p>
                <span class="stat-card__icon stat-card__icon--danger"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->missedMeetings }}</p>
        </article>
    </div>
</div>
</div>
