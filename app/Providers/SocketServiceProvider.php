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
        /** @psalm-suppress MissingClosureParamType */
        $this->app->singleton('App\Services\SocketRouterContract', function($container, $parameters) {
            return $this->app->make(\App\Services\SocketRouter::class, $parameters);
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
