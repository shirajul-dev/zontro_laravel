<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('m::dashboard.index');
    }

    public function switchBrand($id)
    {
        $merchant = auth()->guard('merchant')->user();
        $brand = $merchant->brands()->where('id', $id)->firstOrFail();

        session(['active_brand_id' => $brand->id]);

        return redirect()->back()->with('success', 'Switched to ' . $brand->name);
    }
}
