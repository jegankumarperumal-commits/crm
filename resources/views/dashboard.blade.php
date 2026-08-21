<x-layouts::coolapp :title="__('Dashboard')">
    <div class="page-header">
        <div>
            <h1>{{ __('Dashboard') }}</h1>
            <p class="subtitle">{{ __('Welcome back, :name — sample data shown below until real CRM records exist.', ['name' => auth()->user()->name]) }}</p>
        </div>
    </div>

    {{-- KPI strip (sample/placeholder data — no CRM domain tables exist yet) --}}
    <div class="row row-tight">
        <div class="col-sm-6 col-lg-3">
            <article class="stat-card">
                <div class="stat-card__head">
                    <p class="stat-card__label">{{ __('Revenue') }}</p>
                    <span class="stat-card__icon stat-card__icon--c1"><i class="fa-solid fa-dollar-sign" aria-hidden="true"></i></span>
                </div>
                <p class="stat-card__value">$48,217</p>
                <p class="stat-card__delta stat-card__delta--up">
                    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                    12.5%
                    <span class="stat-card__delta-period">vs last 30d</span>
                </p>
                <div class="stat-card__sparkline">
                    <canvas id="kpi-revenue"></canvas>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-lg-3">
            <article class="stat-card">
                <div class="stat-card__head">
                    <p class="stat-card__label">{{ __('Orders') }}</p>
                    <span class="stat-card__icon stat-card__icon--c2"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></span>
                </div>
                <p class="stat-card__value">1,284</p>
                <p class="stat-card__delta stat-card__delta--down">
                    <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                    3.2%
                    <span class="stat-card__delta-period">vs last 30d</span>
                </p>
                <div class="stat-card__sparkline">
                    <canvas id="kpi-orders"></canvas>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-lg-3">
            <article class="stat-card">
                <div class="stat-card__head">
                    <p class="stat-card__label">{{ __('Active users') }}</p>
                    <span class="stat-card__icon stat-card__icon--c3"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
                </div>
                <p class="stat-card__value">8,492</p>
                <p class="stat-card__delta stat-card__delta--up">
                    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                    5.8%
                    <span class="stat-card__delta-period">vs last 30d</span>
                </p>
                <div class="stat-card__sparkline">
                    <canvas id="kpi-users"></canvas>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-lg-3">
            <article class="stat-card">
                <div class="stat-card__head">
                    <p class="stat-card__label">{{ __('Conversion') }}</p>
                    <span class="stat-card__icon stat-card__icon--c4"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
                </div>
                <p class="stat-card__value">3.24%</p>
                <p class="stat-card__delta stat-card__delta--up">
                    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                    0.6pp
                    <span class="stat-card__delta-period">vs last 30d</span>
                </p>
                <div class="stat-card__sparkline">
                    <canvas id="kpi-conversion"></canvas>
                </div>
            </article>
        </div>
    </div>

    {{-- Primary chart + activity feed --}}
    <div class="row row-tight" style="margin-top: 16px;">
        <div class="col-lg-8">
            <section class="m-card" aria-labelledby="rev-trend-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="rev-trend-title">{{ __('Revenue trend') }}</h2>
                        <p class="m-card__subtitle">{{ __('Sample data — daily revenue over the past 30 days.') }}</p>
                    </div>
                </header>
                <div style="height: 280px; position: relative;">
                    <canvas id="primary-chart"></canvas>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="m-card" aria-labelledby="activity-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="activity-title">{{ __('Recent activity') }}</h2>
                        <p class="m-card__subtitle">{{ __('No activity recorded yet.') }}</p>
                    </div>
                </header>
                <p class="m-card__subtitle" style="padding: 20px 25px;">
                    {{ __('This will show real CRM activity once contacts, deals, and tasks exist.') }}
                </p>
            </section>
        </div>
    </div>

    {{-- Tasks + top products (placeholder) --}}
    <div class="row row-tight" style="margin-top: 16px;">
        <div class="col-lg-6">
            <section class="m-card" aria-labelledby="tasks-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="tasks-title">{{ __('My tasks') }}</h2>
                        <p class="m-card__subtitle">{{ __('Sample tasks — task management is not built yet.') }}</p>
                    </div>
                </header>
                <ul class="task-list">
                    <li class="task-item">
                        <input type="checkbox" id="task-1" aria-labelledby="task-1-label" disabled>
                        <label class="task-item__title" id="task-1-label" for="task-1">{{ __('Set up your first contact list') }}</label>
                        <div class="task-item__meta">
                            <span class="priority-chip priority-chip--high">{{ __('High') }}</span>
                        </div>
                    </li>
                    <li class="task-item">
                        <input type="checkbox" id="task-2" aria-labelledby="task-2-label" disabled>
                        <label class="task-item__title" id="task-2-label" for="task-2">{{ __('Invite your team') }}</label>
                        <div class="task-item__meta">
                            <span class="priority-chip priority-chip--medium">{{ __('Medium') }}</span>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="m-card" aria-labelledby="top-products-title">
                <header class="m-card__header">
                    <div>
                        <h2 class="m-card__title" id="top-products-title">{{ __('Pipeline overview') }}</h2>
                        <p class="m-card__subtitle">{{ __('Sample data — deals and pipelines are not built yet.') }}</p>
                    </div>
                </header>
                <table class="m-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Stage') }}</th>
                            <th scope="col" class="num">{{ __('Deals') }}</th>
                            <th scope="col" class="num">{{ __('Value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ __('New') }}</td>
                            <td class="num">12</td>
                            <td class="num">$18,420</td>
                        </tr>
                        <tr>
                            <td>{{ __('Qualified') }}</td>
                            <td class="num">8</td>
                            <td class="num">$9,548</td>
                        </tr>
                        <tr>
                            <td>{{ __('Won') }}</td>
                            <td class="num">3</td>
                            <td class="num">$11,940</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</x-layouts::coolapp>
