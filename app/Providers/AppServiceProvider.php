<?php

namespace App\Providers;

use App\Policies\AreaPolicy;
use App\Policies\PopPolicy;
use App\Policies\RouterPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Modules\ActiveDevice\Models\Router;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

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
        $this->registerPolicies();

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }

    protected function registerPolicies(): void
    {
        Gate::policy(InfrastructureArea::class, AreaPolicy::class);
        Gate::policy(Pop::class, PopPolicy::class);
        Gate::policy(Router::class, RouterPolicy::class);
    }

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
            : null
        );
    }
}
