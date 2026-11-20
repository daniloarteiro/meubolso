<?php

namespace App\Providers;

use App\Mail\BrevoTransport;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
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
        Paginator::useBootstrapFive();

        Mail::extend('brevo', function () {
            return new BrevoTransport((string) config('services.brevo.key', ''));
        });
    }
}
