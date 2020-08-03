<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Repositories\RoomsRepositoryInterface::class,
            \App\Repositories\RoomsRepository::class
        );

        $this->app->bind(
            \App\Repositories\TasksRepositoryInterface::class,
            \App\Repositories\TasksRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
