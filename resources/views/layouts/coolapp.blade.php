@props(['title' => null, 'dark' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

    <title>{{ filled($title) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link href="{{ asset('vendor/cooladmin/css/font-face.css') }}" rel="stylesheet" media="all"/>
    <link rel="preconnect" href="https://rsms.me/"/>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css"/>
    <link href="{{ asset('vendor/cooladmin/vendor/fontawesome-7.3.1/css/all.min.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/vendor/bootstrap-5.3.8.min.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/vendor/css-hamburgers/hamburgers.min.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/css/theme.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/css/app.css') }}?v={{ @filemtime(public_path('vendor/cooladmin/css/app.css')) ?: '1' }}" rel="stylesheet" media="all"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app @if($dark) dashboard-dark @endif">
    <a class="visually-hidden-focusable skip-link" href="#main-content">Skip to main content</a>
    <div class="page-wrapper">
        <header class="header-mobile d-block d-lg-none">
            <div class="header-mobile__bar">
                <div class="container-fluid">
                    <div class="header-mobile-inner">
                        <a class="logo" href="{{ route('dashboard') }}">
                            <span class="logo-mark" aria-hidden="true">{{ \Illuminate\Support\Str::substr(config('app.name', 'Laravel'), 0, 1) }}</span>
                            <span class="logo-text">{{ config('app.name', 'Laravel') }}</span>
                        </a>
                        <button class="hamburger hamburger--slider js-sidebar-toggle" type="button" aria-label="Toggle navigation">
                            <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                        </button>
                    </div>
                </div>
            </div>
            <nav class="navbar-mobile">
                <div class="container-fluid">
                    <ul class="navbar-mobile__list list-unstyled">
                        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}"><i class="fa-solid fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        @can('manage-leads')
                        <li class="{{ request()->routeIs('leads.*') ? 'active' : '' }}">
                            <a href="{{ route('leads.index') }}"><i class="fa-solid fa-user-tag"></i>Leads</a>
                        </li>
                        @endcan
                        @can('manage-follow-ups')
                        <li class="{{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
                            <a href="{{ route('follow-ups.index') }}"><i class="fa-solid fa-calendar-check"></i>Follow-ups</a>
                        </li>
                        @endcan
                        @can('manage-meetings')
                        <li class="{{ request()->routeIs('meetings.*') ? 'active' : '' }}">
                            <a href="{{ route('meetings.index') }}"><i class="fa-solid fa-calendar-days"></i>Meetings</a>
                        </li>
                        @endcan
                        @can('manage-quotations')
                        <li class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                            <a href="{{ route('quotations.index') }}"><i class="fa-solid fa-file-invoice"></i>Quotations</a>
                        </li>
                        @endcan
                        @can('manage-clients-invoices')
                        <li class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <a href="{{ route('clients.index') }}"><i class="fa-solid fa-user-check"></i>Clients & Invoices</a>
                        </li>
                        @endcan
                        @can('view-reports')
                        <li class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}"><i class="fa-solid fa-chart-line"></i>Reports</a>
                        </li>
                        @endcan
                        @can('manage-team')
                        <li class="{{ request()->routeIs('team.*') ? 'active' : '' }}">
                            <a href="{{ route('team.index') }}"><i class="fa-solid fa-users-gear"></i>Team</a>
                        </li>
                        <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}"><i class="fa-solid fa-user-shield"></i>Roles</a>
                        </li>
                        @endcan
                        <li class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                            <a href="{{ route('profile.edit') }}"><i class="fa-solid fa-user-gear"></i>Settings</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <aside class="menu-sidebar" id="main-sidebar">
            <div class="logo">
                <a class="logo-link" href="{{ route('dashboard') }}" aria-label="{{ config('app.name', 'Laravel') }} home">
                    <span class="logo-mark" aria-hidden="true">{{ \Illuminate\Support\Str::substr(config('app.name', 'Laravel'), 0, 1) }}</span>
                    <span class="logo-text">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <button class="sidebar-close js-sidebar-toggle" type="button" aria-label="Close navigation"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar">
                    <ul class="list-unstyled navbar__list">
                        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}"><i class="fa-solid fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        @can('manage-leads')
                        <li class="{{ request()->routeIs('leads.*') ? 'active' : '' }}">
                            <a href="{{ route('leads.index') }}"><i class="fa-solid fa-user-tag"></i>Leads</a>
                        </li>
                        @endcan
                        @can('manage-follow-ups')
                        <li class="{{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
                            <a href="{{ route('follow-ups.index') }}"><i class="fa-solid fa-calendar-check"></i>Follow-ups</a>
                        </li>
                        @endcan
                        @can('manage-meetings')
                        <li class="{{ request()->routeIs('meetings.*') ? 'active' : '' }}">
                            <a href="{{ route('meetings.index') }}"><i class="fa-solid fa-calendar-days"></i>Meetings</a>
                        </li>
                        @endcan
                        @can('manage-quotations')
                        <li class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                            <a href="{{ route('quotations.index') }}"><i class="fa-solid fa-file-invoice"></i>Quotations</a>
                        </li>
                        @endcan
                        @can('manage-clients-invoices')
                        <li class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <a href="{{ route('clients.index') }}"><i class="fa-solid fa-user-check"></i>Clients & Invoices</a>
                        </li>
                        @endcan
                        @can('view-reports')
                        <li class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}"><i class="fa-solid fa-chart-line"></i>Reports</a>
                        </li>
                        @endcan
                        @can('manage-team')
                        <li class="{{ request()->routeIs('team.*') ? 'active' : '' }}">
                            <a href="{{ route('team.index') }}"><i class="fa-solid fa-users-gear"></i>Team</a>
                        </li>
                        <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}"><i class="fa-solid fa-user-shield"></i>Roles</a>
                        </li>
                        @endcan
                        <li class="{{ request()->routeIs('profile.edit', 'appearance.edit', 'security.edit') ? 'active' : '' }}">
                            <a href="{{ route('profile.edit') }}"><i class="fa-solid fa-user-gear"></i>Settings</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="page-container">
            <header class="header-desktop">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="header-wrap">
                            <button class="sidebar-toggle js-sidebar-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="main-sidebar"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
                            <form class="form-header" role="search" onsubmit="return false">
                                <i class="fa-solid fa-magnifying-glass form-header__icon" aria-hidden="true"></i>
                                <input class="au-input au-input--xl" type="search" name="search" placeholder="Search anything…" aria-label="Search">
                            </form>
                            <div class="header-button">
                                <div class="account-wrap">
                                    <div class="account-item clearfix js-item-menu" role="button" tabindex="0" aria-haspopup="true" aria-label="Account menu">
                                        <div class="image img-cir img-40 d-flex align-items-center justify-content-center bg-primary text-white fw-semibold">
                                            {{ auth()->user()->initials() }}
                                        </div>
                                        <div class="content"><a class="js-acc-btn" href="#">{{ auth()->user()->name }}</a></div>
                                        <div class="account-dropdown js-dropdown">
                                            <div class="info clearfix">
                                                <div class="image img-cir img-40 d-flex align-items-center justify-content-center bg-primary text-white fw-semibold">
                                                    {{ auth()->user()->initials() }}
                                                </div>
                                                <div class="content">
                                                    <h5 class="name"><a href="#">{{ auth()->user()->name }}</a></h5>
                                                    <span class="email">{{ auth()->user()->email }}</span>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__body">
                                                <div class="account-dropdown__item">
                                                    <a href="{{ route('profile.edit') }}"><i class="fa-solid fa-gear"></i>Settings</a>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__footer">
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="cursor-pointer"
                                                        style="display:block; width:100%; text-align:left; padding:15px 25px; font-size:14px; color:#333; background:none; border:0;"
                                                        data-test="logout-button"
                                                    >
                                                        <i class="fa-solid fa-power-off"></i>Logout
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="main-content" id="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        {{ $slot }}

                        <div class="row" style="margin-top: 28px;">
                            <div class="col-md-12">
                                <div class="copyright">
                                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="{{ asset('vendor/cooladmin/js/vanilla-utils.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/vendor/bootstrap-5.3.8.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/vendor/chartjs/chart.umd.js-4.5.1.min.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/js/bootstrap5-init.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/js/main-vanilla.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/js/modern-plugins.js') }}"></script>

</body>
</html>
