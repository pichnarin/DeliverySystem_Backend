<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Schema;
use Kreait\Firebase\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
{
    $this->app->singleton('firebase.auth', function ($app) {
        return (new Factory)->withServiceAccount(storage_path('app/pizzasprintnotification-firebase-adminsdk-fbsvc-b2f75a1eee.json'))->createAuth();
    });
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Schema::defaultStringLength(191);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
