<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class MerchantSidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $merchant = Auth::guard('merchant')->user();
        
        if ($merchant) {
            $brands = $merchant->brands()->get();
            $activeBrand = $brands->first(); // Default to first brand
            
            $view->with('activeBrand', $activeBrand);
            $view->with('merchantBrands', $brands);
        }
    }
}
