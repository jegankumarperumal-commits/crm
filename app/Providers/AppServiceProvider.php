<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureGates();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure the application's authorization gates.
     */
    protected function configureGates(): void
    {
        Gate::define('manage-team', fn (User $user): bool => $user->hasPermission(Permission::ManageTeam));
        Gate::define('manage-leads', fn (User $user): bool => $user->hasPermission(Permission::ManageLeads));
        Gate::define('manage-follow-ups', fn (User $user): bool => $user->hasPermission(Permission::ManageFollowUps));
        Gate::define('manage-meetings', fn (User $user): bool => $user->hasPermission(Permission::ManageMeetings));
        Gate::define('manage-quotations', fn (User $user): bool => $user->hasPermission(Permission::ManageQuotations));
        Gate::define('manage-clients-invoices', fn (User $user): bool => $user->hasPermission(Permission::ManageClientsInvoices));
        Gate::define('view-reports', fn (User $user): bool => $user->hasPermission(Permission::ViewReports));
    }
}
