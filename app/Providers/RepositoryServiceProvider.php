<?php

namespace App\Providers;

use App\Repositories\AuthRepository;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Repositories\Interfaces\IntervenantRepositoryInterface;
use App\Repositories\IntervenantRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(IntervenantRepositoryInterface::class, IntervenantRepository::class);

    }


    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
