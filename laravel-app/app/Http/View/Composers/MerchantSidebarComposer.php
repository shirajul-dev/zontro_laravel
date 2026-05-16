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
            
            $activeBrandId = session('active_brand_id');
            $activeBrand = null;
            
            if ($activeBrandId) {
                $activeBrand = $brands->where('id', $activeBrandId)->first();
            }
            
            if (!$activeBrand) {
                $activeBrand = $brands->where('is_default', true)->first() ?? $brands->first();
            }
            
            $view->with('activeBrand', $activeBrand);
            $view->with('merchantBrands', $brands);
        }
    }
}
