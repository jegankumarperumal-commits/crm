<x-layouts::coolapp :title="__('Dashboard')" :dark="true">
    <div class="page-header">
        <div>
            <h1>{{ __('Dashboard') }}</h1>
            <p class="subtitle">{{ __('Welcome back, :name — here\'s what needs your attention today.', ['name' => auth()->user()->name]) }}</p>
        </div>
        <div class="page-header__actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <a href="{{ route('leads.index') }}?statusFilter=" style="text-decoration:none;">
                <span class="badge {{ $actionRequiredCount > 0 ? 'bg-danger' : 'bg-success' }}" style="font-size:13px; padding:8px 14px;">
                    <i class="fa-solid {{ $actionRequiredCount > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' }}" aria-hidden="true"></i>
                    {{ __('Action Required: :count', ['count' => $actionRequiredCount]) }}
                </span>
            </a>
            <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Add Lead') }}</a>
            <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Schedule Meeting') }}</a>
            <a href="{{ route('quotations.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Create Quotation') }}</a>
            <a href="{{ route('clients.index') }}" class="btn btn-sm btn-primary">{{ __('Create Invoice') }}</a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row row-tight">
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <a href="{{ route('leads.index') }}?statusFilter=new" style="text-decoration:none; color:inherit; display:block;">
                <article class="stat-card">
                    <div class="stat-card__head">
                        <p class="stat-card__label">{{ __('New Leads') }}</p>
                        <span class="stat-card__icon stat-card__icon--c2"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></span>
                    </div>
                    <p class="stat-card__value">{{ $kpis['newLeads'] }}</p>
                </article>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <a href="{{ route('leads.index') }}?statusFilter=active" style="text-decoration:none; color:inherit; display:block;">
                <article class="stat-card">
                    <div class="stat-card__head">
                        <p class="stat-card__label">{{ __('Active Leads') }}</p>
                        <span class="stat-card__icon stat-card__icon--c3"><i class="fa-solid fa-user-tag" aria-hidden="true"></i></span>
                    </div>
                    <p class="stat-card__value">{{ $kpis['activeLeads'] }}</p>
                </article>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <a href="{{ route('meetings.index') }}?dateFilter=today" style="text-decoration:none; color:inherit; display:block;">
                <article class="stat-card">
                    <div class="stat-card__head">
                        <p class="stat-card__label">{{ __("Today's Meetings") }}</p>
                        <span class="stat-card__icon stat-card__icon--c1"><i class="fa-solid fa-calendar-day" aria-hidden="true"></i></span>
                    </div>
                    <p class="stat-card__value">{{ $kpis['todaysMeetings'] }}</p>
                </article>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <a href="{{ route('follow-ups.index') }}?statusFilter=overdue" style="text-decoration:none; color:inherit; display:block;">
                <article class="stat-card">
                    <div class="stat-card__head">
                        <p class="stat-card__label">{{ __('Overdue Follow-ups') }}</p>
                        <span class="stat-card__icon stat-card__icon--danger"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                    </div>
                    <p class="stat-card__value">{{ $kpis['overdueFollowUps'] }}</p>
                </article>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <a href="{{ route('quotations.index') }}?statusFilter=pending" style="text-decoration:none; color:inherit; display:block;">
                <article class="stat-card">
                    <div class="stat-card__head">
                        <p class="stat-card__label">{{ __('Pending Quotations') }}</p>
                        <span class="stat-card__icon stat-card__icon--warning"><i class="fa-solid fa-file-invoice" aria-hidden="true"></i></span>
                    </div>
                    <p class="stat-card__value">{{ $kpis['pendingQuotations'] }}</p>
                </article>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <a href="{{ route('leads.index') }}?statusFilter=won" style="text-decoration:none; color:inherit; display:block;">
                <article class="stat-card">
                    <div class="stat-card__head">
                        <p class="stat-card__label">{{ __('Won Deals') }}</p>
                        <span class="stat-card__icon stat-card__icon--success"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
                    </div>
                    <p class="stat-card__value">{{ $kpis['wonDeals'] }}</p>
                </article>
            </a>
        </div>
    </div>

    {{-- TODAY'S MEETINGS — surfaced first so the day's schedule is the first thing seen --}}
    <div class="row row-tight" style="margin-top:16px;">
        <div class="col-12">
            <section class="m-card" aria-labelledby="today-meetings-title" style="border-top:3px solid var(--m-c1, #4272d7);">
                <header class="m-card__header" style="align-items:center;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="width:38px; height:38px; border-radius:10px; background:var(--m-c1-soft, #eaf0fc); color:var(--m-c1, #4272d7); display:inline-flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                            <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
                        </span>
                        <div>
                            <h2 class="m-card__title" id="today-meetings-title" style="display:flex; align-items:center; gap:8px; margin:0;">
                                {{ __("Today's Meetings") }}
                                @if ($todaysMeetingsList->isNotEmpty())
                                    <span style="background:var(--m-c1, #4272d7); color:#fff; border-radius:999px; padding:1px 9px; font-size:12.5px; font-weight:700;">{{ $todaysMeetingsList->count() }}</span>
                                @endif
                            </h2>
                            <p class="m-card__subtitle" style="margin:2px 0 0;">{{ __("Sorted chronologically — see who's next.") }}</p>
                        </div>
                    </div>
                </header>

                @if ($todaysMeetingsList->isEmpty())
                    <p class="m-card__subtitle" style="padding:20px 25px;">{{ __('No meetings scheduled for today. Enjoy the breathing room.') }}</p>
                @else
                    <div style="padding:4px 25px 20px; display:flex; flex-direction:column; gap:8px;">
                        @foreach ($todaysMeetingsList as $meeting)
                            <div style="display:flex; align-items:center; gap:16px; padding:12px 16px; border-radius:10px; background:var(--m-surface-2, #f8fafc); border-left:3px solid var(--m-c1, #4272d7);">
                                <div style="min-width:76px; font-weight:700; color:var(--m-c1, #4272d7); font-size:14px;">{{ $meeting->scheduled_at->format('g:i A') }}</div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-weight:600;">{{ $meeting->lead->name }}</div>
                                    <div style="font-size:12.5px; color:var(--m-text-muted);">{{ $meeting->type->label() }}{{ $meeting->lead->company ? ' · '.$meeting->lead->company : '' }}</div>
                                </div>
                                <span class="badge {{ $meeting->status->badgeClass() }}">{{ $meeting->status->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div style="padding:0 25px 20px;">
                    <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('View All Meetings') }}</a>
                </div>
            </section>
        </div>
    </div>

    {{-- NEEDS MY ATTENTION — tabbed by category so it stays short and each kind of item is visually distinct --}}
    @php
        $attentionTabs = [
            'overdue' => ['label' => __('Overdue Follow-ups'), 'icon' => 'fa-triangle-exclamation', 'var' => 'm-danger', 'soft' => 'm-danger-soft', 'count' => $attentionCounts['overdueFollowUps']],
            'missed' => ['label' => __('Missed Meetings'), 'icon' => 'fa-calendar-xmark', 'var' => 'm-warning', 'soft' => 'm-warning-soft', 'count' => $attentionCounts['missedMeetings']],
            'leads' => ['label' => __('No Next Action'), 'icon' => 'fa-user-clock', 'var' => 'm-c1', 'soft' => 'm-c1-soft', 'count' => $attentionCounts['leadsWithNoNextAction']],
            'quotes' => ['label' => __('Pending Quotes'), 'icon' => 'fa-file-invoice', 'var' => 'm-c4', 'soft' => 'm-c4-soft', 'count' => $attentionCounts['quotationsAwaitingResponse']],
        ];
        $defaultAttentionTab = 'overdue';
        foreach ($attentionTabs as $tabKey => $tab) {
            if ($tab['count'] > 0) {
                $defaultAttentionTab = $tabKey;
                break;
            }
        }
        $totalAttentionItems = collect($attentionTabs)->sum('count');
    @endphp
    <div class="row row-tight" style="margin-top:16px;">
        <div class="col-12">
            <section class="m-card" aria-labelledby="attention-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="attention-title">{{ __('Needs My Attention') }}</h2>
                        <p class="m-card__subtitle">{{ __('Everything that currently needs a decision or a nudge.') }}</p>
                    </div>
                </header>

                @if ($totalAttentionItems === 0)
                    <p class="m-card__subtitle" style="padding:20px 25px;">{{ __('No urgent actions. Everything is up to date.') }}</p>
                @else
                    <div style="display:flex; flex-wrap:wrap; gap:8px; padding:0 25px 18px;">
                        @foreach ($attentionTabs as $tabKey => $tab)
                            <button
                                type="button"
                                class="attention-tab{{ $tabKey === $defaultAttentionTab ? ' is-active' : '' }}"
                                data-tab="{{ $tabKey }}"
                                style="--tab-color:var(--{{ $tab['var'] }}, #94a3b8); --tab-soft:var(--{{ $tab['soft'] }}, #f1f5f9);"
                            >
                                <i class="fa-solid {{ $tab['icon'] }}" aria-hidden="true"></i>
                                {{ $tab['label'] }}
                                <span style="background:var(--{{ $tab['var'] }}, #94a3b8); color:#fff; border-radius:999px; padding:1px 7px; font-size:11px; min-width:18px; text-align:center;">{{ $tab['count'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div style="padding:0 25px 20px;">
                        <div class="attention-panel" data-tab="overdue" style="display:{{ $defaultAttentionTab === 'overdue' ? 'flex' : 'none' }}; flex-direction:column; gap:6px;">
                            @forelse ($overdueFollowUps as $followUp)
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 14px; border-radius:8px; background:var(--m-surface-2, #f8fafc); border-left:3px solid var(--m-danger, #ef4444);">
                                    <div style="min-width:0;">
                                        <a href="{{ route('follow-ups.index') }}?statusFilter=overdue" style="text-decoration:none; color:inherit; font-weight:600;">{{ $followUp->lead->name }}</a>
                                        <div style="font-size:12.5px; color:var(--m-text-muted);">
                                            {{ __('Due :date · :days days overdue', ['date' => $followUp->due_at->format('M j'), 'days' => (int) abs(now()->diffInDays($followUp->due_at))]) }}
                                        </div>
                                    </div>
                                    <a href="{{ route('follow-ups.index') }}?statusFilter=overdue" class="btn btn-sm btn-outline-secondary">{{ __('View') }}</a>
                                </div>
                            @empty
                                <p class="text-muted small">{{ __('No overdue follow-ups.') }}</p>
                            @endforelse
                        </div>

                        <div class="attention-panel" data-tab="missed" style="display:{{ $defaultAttentionTab === 'missed' ? 'flex' : 'none' }}; flex-direction:column; gap:6px;">
                            @forelse ($missedMeetings as $meeting)
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 14px; border-radius:8px; background:var(--m-surface-2, #f8fafc); border-left:3px solid var(--m-warning, #f59e0b);">
                                    <div style="min-width:0;">
                                        <a href="{{ route('meetings.index') }}" style="text-decoration:none; color:inherit; font-weight:600;">{{ $meeting->lead->name }}</a>
                                        <div style="font-size:12.5px; color:var(--m-text-muted);">
                                            {{ $meeting->scheduled_at->format('M j, g:i A') }} · {{ $meeting->type->label() }}
                                        </div>
                                    </div>
                                    <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('View') }}</a>
                                </div>
                            @empty
                                <p class="text-muted small">{{ __('No missed meetings.') }}</p>
                            @endforelse
                        </div>

                        <div class="attention-panel" data-tab="leads" style="display:{{ $defaultAttentionTab === 'leads' ? 'flex' : 'none' }}; flex-direction:column; gap:6px;">
                            @forelse ($leadsWithNoNextAction as $lead)
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 14px; border-radius:8px; background:var(--m-surface-2, #f8fafc); border-left:3px solid var(--m-c1, #4272d7);">
                                    <div style="min-width:0;">
                                        <a href="{{ route('leads.index') }}?statusFilter=active" style="text-decoration:none; color:inherit; font-weight:600;">{{ $lead->name }}</a>
                                        <div style="font-size:12.5px; color:var(--m-text-muted);">
                                            {{ $lead->status->label() }}{{ $lead->company ? ' · '.$lead->company : '' }}
                                        </div>
                                    </div>
                                    <a href="{{ route('leads.index') }}?statusFilter=active" class="btn btn-sm btn-outline-secondary">{{ __('View') }}</a>
                                </div>
                            @empty
                                <p class="text-muted small">{{ __('Every active lead has a next action scheduled.') }}</p>
                            @endforelse
                        </div>

                        <div class="attention-panel" data-tab="quotes" style="display:{{ $defaultAttentionTab === 'quotes' ? 'flex' : 'none' }}; flex-direction:column; gap:6px;">
                            @forelse ($quotationsAwaitingResponse as $quotation)
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 14px; border-radius:8px; background:var(--m-surface-2, #f8fafc); border-left:3px solid var(--m-c4, #ec4899);">
                                    <div style="min-width:0;">
                                        <a href="{{ route('quotations.index') }}?statusFilter=pending" style="text-decoration:none; color:inherit; font-weight:600;">{{ $quotation->lead->name }}</a>
                                        <div style="font-size:12.5px; color:var(--m-text-muted);">
                                            {{ $quotation->quotation_number ?? '—' }} · ₹{{ number_format((float) $quotation->amount, 2) }} · {{ $quotation->status->label() }}
                                        </div>
                                    </div>
                                    <a href="{{ route('quotations.index') }}?statusFilter=pending" class="btn btn-sm btn-outline-secondary">{{ __('View') }}</a>
                                </div>
                            @empty
                                <p class="text-muted small">{{ __('No pending quotations.') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.attention-tab');
            const panels = document.querySelectorAll('.attention-panel');

            tabs.forEach((btn) => btn.addEventListener('click', () => {
                tabs.forEach((b) => b.classList.toggle('is-active', b === btn));
                panels.forEach((panel) => {
                    panel.style.display = panel.dataset.tab === btn.dataset.tab ? 'flex' : 'none';
                });
            }));
        });
    </script>

    {{-- LEAD PIPELINE — proportional funnel bar instead of plain boxes --}}
    @php
        $pipelineColors = [
            'bg-info' => 'var(--m-c1, #4272d7)',
            'bg-primary' => 'var(--m-accent, #4272d7)',
            'bg-warning' => 'var(--m-warning, #f59e0b)',
            'bg-success' => 'var(--m-success, #10b981)',
            'bg-danger' => 'var(--m-danger, #ef4444)',
            'bg-secondary' => '#94a3b8',
        ];
        $pipelineTotal = collect($pipeline)->sum('count');
    @endphp
    <div class="row row-tight" style="margin-top:16px;">
        <div class="col-12">
            <section class="m-card" aria-labelledby="pipeline-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="pipeline-title">{{ __('Lead Pipeline') }}</h2>
                        <p class="m-card__subtitle">{{ __('Every stage — click any to see those leads.') }}</p>
                    </div>
                </header>

                @if ($pipelineTotal === 0)
                    <p class="m-card__subtitle" style="padding:20px 25px;">{{ __('No leads in the pipeline.') }}</p>
                @else
                    <div style="padding:20px 25px 8px;">
                        <div style="display:flex; height:14px; border-radius:999px; overflow:hidden; background:var(--m-surface-2, #f8fafc);">
                            @foreach ($pipeline as $row)
                                @if ($row['count'] > 0)
                                    <a
                                        href="{{ route('leads.index') }}?statusFilter={{ $row['status']->value }}"
                                        title="{{ $row['status']->label() }}: {{ $row['count'] }}"
                                        style="flex-grow:{{ $row['count'] }}; flex-basis:0; background:{{ $pipelineColors[$row['status']->badgeClass()] ?? '#94a3b8' }};"
                                    ></a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div style="display:flex; flex-wrap:wrap; gap:10px; padding:12px 25px 22px;">
                        @foreach ($pipeline as $row)
                            <a
                                href="{{ route('leads.index') }}?statusFilter={{ $row['status']->value }}"
                                style="display:flex; align-items:center; gap:8px; text-decoration:none; color:inherit; padding:8px 12px; border-radius:8px; background:var(--m-surface-2, #f8fafc); border:1px solid var(--m-border, #e4e7ec); {{ $row['count'] === 0 ? 'opacity:.5;' : '' }}"
                            >
                                <span style="width:9px; height:9px; border-radius:50%; background:{{ $pipelineColors[$row['status']->badgeClass()] ?? '#94a3b8' }}; flex-shrink:0;"></span>
                                <span style="font-weight:700; font-size:14px;">{{ $row['count'] }}</span>
                                <span style="font-size:12.5px; color:var(--m-text-muted);">{{ $row['status']->label() }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>

    {{-- CONVERSION SUMMARY + FINANCIAL SUMMARY --}}
    <div class="row row-tight" style="margin-top:16px;">
        <div class="col-lg-5">
            <section class="m-card chart-card" aria-labelledby="conversion-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="conversion-title">{{ __('Conversion Summary') }}</h2>
                        <p class="m-card__subtitle">{{ __(':total total leads', ['total' => $conversion['total']]) }}</p>
                    </div>
                </header>

                <div style="position:relative; height:190px; margin:4px 0 8px;">
                    <canvas id="conversion-chart" role="img" aria-label="{{ __('Doughnut chart of active, won, and lost leads') }}"></canvas>
                    <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                        <span style="font-size:26px; font-weight:700; color:var(--m-text);">{{ $conversion['rate'] }}%</span>
                        <span style="font-size:11px; color:var(--m-text-muted); text-transform:uppercase; letter-spacing:.04em;">{{ __('Won rate') }}</span>
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:8px; padding-top:4px;">
                    <span style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--m-text-muted);"><span style="width:9px; height:9px; border-radius:50%; background:var(--m-c1);"></span>{{ __('Active') }} <strong style="color:var(--m-text);">{{ $conversion['active'] }}</strong></span>
                    <span style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--m-text-muted);"><span style="width:9px; height:9px; border-radius:50%; background:var(--m-success);"></span>{{ __('Won') }} <strong style="color:var(--m-text);">{{ $conversion['won'] }}</strong></span>
                    <span style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--m-text-muted);"><span style="width:9px; height:9px; border-radius:50%; background:var(--m-danger);"></span>{{ __('Lost') }} <strong style="color:var(--m-text);">{{ $conversion['lost'] }}</strong></span>
                </div>
            </section>
        </div>
        <div class="col-lg-7">
            <section class="m-card chart-card" aria-labelledby="financial-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="financial-title">{{ __('Financial Summary') }}</h2>
                        <p class="m-card__subtitle">{{ __('Pipeline value through to what\'s actually been collected.') }}</p>
                    </div>
                </header>
                <div style="height:230px;">
                    <canvas id="financial-chart" role="img" aria-label="{{ __('Bar chart of quotation value, won deal value, invoiced, received, outstanding, and overdue amounts') }}"></canvas>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isDashboardDark = document.body.classList.contains('dashboard-dark');
            const gridColor = isDashboardDark ? 'rgba(255,255,255,0.06)' : 'rgba(15,23,42,0.06)';
            const tickColor = isDashboardDark ? '#9aa1ac' : '#475569';
            const rupee = (value) => '₹' + Number(value).toLocaleString('en-IN', { maximumFractionDigits: 0 });

            new Chart(document.getElementById('conversion-chart'), {
                type: 'doughnut',
                data: {
                    labels: [@json(__('Active')), @json(__('Won')), @json(__('Lost'))],
                    datasets: [{
                        data: [{{ $conversion['active'] }}, {{ $conversion['won'] }}, {{ $conversion['lost'] }}],
                        backgroundColor: ['#5b8cf5', '#34d399', '#f87171'],
                        borderColor: isDashboardDark ? '#121317' : '#ffffff',
                        borderWidth: 3,
                    }],
                },
                options: {
                    cutout: '72%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.parsed}` } },
                    },
                },
            });

            new Chart(document.getElementById('financial-chart'), {
                type: 'bar',
                data: {
                    labels: [
                        @json(__('Quotation Value')),
                        @json(__('Won Deal Value')),
                        @json(__('Invoiced')),
                        @json(__('Received')),
                        @json(__('Outstanding')),
                        @json(__('Overdue')),
                    ],
                    datasets: [{
                        data: [
                            {{ $financial['quotationValue'] }},
                            {{ $financial['wonDealValue'] }},
                            {{ $financial['invoiced'] }},
                            {{ $financial['received'] }},
                            {{ $financial['outstanding'] }},
                            {{ $financial['overdue'] }},
                        ],
                        backgroundColor: ['#5b8cf5', '#2dd4bf', '#fb923c', '#34d399', '#fbbf24', '#f87171'],
                        borderRadius: 6,
                        maxBarThickness: 44,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => rupee(ctx.parsed.y) } },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
                        y: { grid: { color: gridColor }, ticks: { color: tickColor, callback: (value) => rupee(value) } },
                    },
                },
            });
        });
    </script>
</x-layouts::coolapp>
