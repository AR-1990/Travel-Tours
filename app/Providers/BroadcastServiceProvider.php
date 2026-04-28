<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Broadcasting disabled - using Firebase for real-time messaging
        // Broadcast::routes();

        // if (file_exists(base_path('routes/channels.php'))) {
        //     require base_path('routes/channels.php');
        // }
    }
}
