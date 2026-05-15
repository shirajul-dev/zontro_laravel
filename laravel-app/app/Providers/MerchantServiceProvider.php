<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class MerchantServiceProvider extends ServiceProvider
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
        $theme = config('piprapay.merchant_theme', 'default');
        
        // Register the 'm' namespace for professional theme management
        // It will look in the 'pages' directory first, then the theme root (for layouts)
        View::addNamespace('m', [
            resource_path("views/merchant/{$theme}/pages"),
            resource_path("views/merchant/{$theme}"),
        ]);
        
        // Fallback to default theme if the active theme doesn't have the view
        if ($theme !== 'default') {
            View::addNamespace('m', [
                resource_path("views/merchant/default/pages"),
                resource_path("views/merchant/default"),
            ]);
        }
    }
}
