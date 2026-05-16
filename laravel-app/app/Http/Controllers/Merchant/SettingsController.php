<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard.
     */
    public function index()
    {
        return view('m::pages.settings.index');
    }

    /**
     * Get the currently active brand for the merchant.
     */
    protected function getActiveBrand()
    {
        $merchant = auth()->guard('merchant')->user();
        if (!$merchant) return null;

        $activeBrandId = session('active_brand_id');
        $brand = null;

        if ($activeBrandId) {
            $brand = $merchant->brands()->where('zp_brands.id', $activeBrandId)->first();
        }

        if (!$brand) {
            $brand = $merchant->brands()->where('is_default', true)->first() ?? $merchant->brands()->first();
            if ($brand) {
                session(['active_brand_id' => $brand->id]);
            }
        }

        return $brand;
    }

    /**
     * Display the General Settings page.
     */
    public function general()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return redirect()->route('merchant.dashboard')->with('error', 'No active brand found.');
        }

        if (request()->ajax()) {
            return view('m::pages.settings.sections.general', compact('brand'))->render();
        }

        return view('m::pages.settings.general', compact('brand'));
    }

    /**
     * Display the Branding & Logos page.
     */
    public function branding()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return redirect()->route('merchant.dashboard')->with('error', 'No active brand found.');
        }

        if (request()->ajax()) {
            return view('m::pages.settings.sections.branding', compact('brand'))->render();
        }

        return view('m::pages.settings.branding', compact('brand'));
    }

    /**
     * Display the Social Profiles page.
     */
    public function social()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return redirect()->route('merchant.dashboard')->with('error', 'No active brand found.');
        }

        if (request()->ajax()) {
            return view('m::pages.settings.sections.social', compact('brand'))->render();
        }

        return view('m::pages.settings.social', compact('brand'));
    }

    /**
     * Update General Settings.
     */
    public function updateGeneral(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return response()->json(['status' => 'error', 'message' => 'No active brand found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string|max:20',
            'support_website' => 'nullable|url',
            'currency_code' => 'required|string|max:3',
            'timezone' => 'required|string',
            'language' => 'required|string|max:5',
            'street_address' => 'nullable|string|max:255',
            'city_town' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'whatsapp_number' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
            'facebook_messenger' => 'nullable|string|max:100',
            'facebook_page' => 'nullable|url',
            'auto_exchange' => 'nullable|boolean',
            'payment_tolerance' => 'nullable|numeric|min:0',
        ]);

        $brand->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'General settings updated successfully.',
            'brand' => $brand
        ]);
    }
    /**
     * Update Branding Settings.
     */
    public function updateBranding(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return response()->json(['status' => 'error', 'message' => 'No active brand found.'], 404);
        }

        // Note: Actual file upload logic would go here
        // For now, validating existing paths or placeholders
        $validated = $request->validate([
            'logo' => 'nullable|string|max:255',
            'favicon' => 'nullable|string|max:255',
        ]);

        $brand->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Branding assets updated successfully.',
            'brand' => $brand
        ]);
    }

    /**
     * Update Social Profiles.
     */
    public function updateSocial(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return response()->json(['status' => 'error', 'message' => 'No active brand found.'], 404);
        }

        $validated = $request->validate([
            'whatsapp_number' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
            'facebook_messenger' => 'nullable|string|max:100',
            'facebook_page' => 'nullable|url',
        ]);

        $brand->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Social profiles updated successfully.',
            'brand' => $brand
        ]);
    }
}
