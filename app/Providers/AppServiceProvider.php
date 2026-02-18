<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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

public function boot()
    {
        // Paksa HTTPS jika di produksi (Vercel)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
