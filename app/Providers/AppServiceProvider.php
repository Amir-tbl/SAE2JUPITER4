<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // Enregistre les services de l'application
    public function register(): void
    {
        //
    }

    // Initialise les services de l'application
    public function boot(): void
    {
        // Utilise le style Bootstrap 5 pour la pagination
        Paginator::useBootstrapFive();
    }
}
