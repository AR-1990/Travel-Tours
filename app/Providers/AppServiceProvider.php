<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Load helper file early to ensure functions are available
        if (file_exists($helperPath = app_path('Helpers/ImageHelper.php'))) {
            require_once $helperPath;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Map old User namespace to new one for backward compatibility with polymorphic relationships
        // Using morphMap (not enforceMorphMap) so other models can still use their class names
        Relation::morphMap([
            'App\Models\User' => 'App\Models\Users\User',
        ]);
    }
}
