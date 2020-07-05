<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SocketServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('App\Services\SocketRouter', function ($app) {
            return new \App\Services\SocketRouter();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        require_once(base_path('routes/socket.php'));
    }
}
