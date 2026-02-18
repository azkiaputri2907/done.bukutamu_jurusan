<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
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
    Gate::define('admin-only', function ($user) {
        // Karena di Sheet role Admin adalah 1, maka:
        return $user->role == '1'; 
    });
}
}
