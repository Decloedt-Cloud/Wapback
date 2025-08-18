<?php

namespace App\Providers;

use App\Repositories\AuthRepository;
use App\Repositories\CategorieRepository;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Repositories\Interfaces\CategorieRepositoryInterface;
use App\Repositories\Interfaces\IntervenantRepositoryInterface;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use App\Repositories\Interfaces\VendorRepositoryIterface;
use App\Repositories\IntervenantRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\VendorRepository;
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
        $this->app->bind(CategorieRepositoryInterface::class, CategorieRepository::class);
        $this->app->bind(VendorRepositoryIterface::class, VendorRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);

    }


    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
