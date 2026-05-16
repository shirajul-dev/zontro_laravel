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
        View::addNamespace('m', [
            resource_path("views/merchant/{$theme}/pages"),
            resource_path("views/merchant/{$theme}"),
        ]);
        
        // Fallback to default theme
        if ($theme !== 'default') {
            View::addNamespace('m', [
                resource_path("views/merchant/default/pages"),
                resource_path("views/merchant/default"),
            ]);
        }

        // Register View Composers
        View::composer('merchant.default.partials.sidebar', \App\Http\View\Composers\MerchantSidebarComposer::class);
    }
}
