<?php

namespace App\Providers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider; 

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
        // Disable mass-assignment protection for all models
        Model::unguard();
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
        Gate::before(function (User $user, string $ability) {
            return  $user->hasRole('Super admin') ? true : null;
        });
            // Register Customer API Docs
    // config(['l5-swagger' => require config_path('l5-swagger-customer.php')]);
    // app(GeneratorFactory::class)->generateDocs();

    // // Register Supplier API Docs
    // config(['l5-swagger' => require config_path('l5-swagger-supplier.php')]);
    // app(GeneratorFactory::class)->generateDocs();
    }
}
