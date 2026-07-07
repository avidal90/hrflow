<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('login', function (Request $request): array {
            $email = (string) $request->input('email', '');

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                Limit::perMinute(15)->by($request->ip()),
            ];
        });

        RateLimiter::for('api-authenticated', function (Request $request): array {
            $userId = (string) optional($request->user())->getAuthIdentifier();

            return [
                Limit::perMinute(120)->by('user:'.$userId),
                Limit::perMinute(180)->by('ip:'.$request->ip()),
            ];
        });
    }
}
