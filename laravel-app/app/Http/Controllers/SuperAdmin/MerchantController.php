<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PpAdmin;
use App\Models\PpBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MerchantController extends Controller
{
    /**
     * Display a listing of merchants
     */
    /**
     * Display a listing of merchants
     */
    public function index()
    {
        // Join pp_admin with pp_brands via pp_permission
        $merchants = PpAdmin::where('role', 'admin')
            ->leftJoin('pp_permission', 'pp_admin.a_id', '=', 'pp_permission.a_id')
            ->leftJoin('pp_brands', 'pp_permission.brand_id', '=', 'pp_brands.brand_id')
            ->select(
                'pp_admin.*',
                'pp_brands.name as brand_name',
                'pp_brands.logo as brand_logo',
                'pp_brands.country as brand_country',
                'pp_brands.brand_id'
            )
            ->orderBy('pp_admin.created_date', 'desc')
            ->get();

        return view('superadmin.pages.merchants.index', compact('merchants'));
    }

    /**
     * Show the form for creating a new merchant
     */
    public function create()
    {
        $plans = \App\Models\PpPlan::where('is_active', true)->get();
        return view('superadmin.pages.merchants.create', compact('plans'));
    }

    /**
     * Store a newly created merchant
     */
    public function store(Request $request)
    {
        // Ensure legacy functions are loaded for helper functions
        if (!function_exists('generateItemID')) {
            $functionsPath = base_path('app/Support/zp-functions.php');
            if (file_exists($functionsPath)) {
                if (!defined('PipraPay_INIT')) {
                    define('PipraPay_INIT', true);
                }
                require_once $functionsPath;
            }
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|unique:pp_admin,username|max:50',
            'email' => 'required|email|unique:pp_admin,email|max:100',
            'password' => 'required|string|min:3',
            'status' => 'required|in:active,suspend',
            'plan_id' => 'required|exists:pp_plans,id',
            'brand_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:100',
            'support_phone' => 'nullable|string|max:20',
            'support_website' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // 1. Generate IDs
            $a_id = 'ADM' . strtoupper(Str::random(10));
            $brand_id = 'BRD' . strtoupper(Str::random(10));

            // Ensure unique if needed (using generateItemID helper if available)
            if (function_exists('generateItemID')) {
                $a_id = generateItemID();
                $brand_id = generateItemID();
            }

            // 2. Handle Logo Upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = strtolower(Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension());
                $file->move(storage_path('app/public/media'), $filename);
                $logoPath = url('storage/media/' . $filename);
            }

            // 3. Create Admin/Merchant User
            $admin = PpAdmin::create([
                'a_id' => $a_id,
                'full_name' => $request->full_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->status,
                'plan_id' => $request->plan_id,
                'role' => 'admin', // Default role for merchant
                'user_type' => 'merchant',
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

            // 4. Create Brand
            $brand = PpBrand::create([
                'brand_id' => $brand_id,
                'name' => $request->brand_name,
                'identify_name' => $request->brand_name,
                'support_email_address' => $request->support_email,
                'support_phone_number' => $request->support_phone,
                'support_website' => $request->support_website,
                'country' => $request->country,
                'logo' => $logoPath,
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
                'theme' => 'twenty-six',
                'timezone' => 'Asia/Dhaka',
                'language' => 'en',
                'currency_code' => 'BDT',
            ]);

            // 5. Create Permission
            $permissionSchema = [];
            if (function_exists('permissionSchema')) {
                $permissionSchema = permissionSchema();
            } else {
                // Default fallback permissions
                $permissionSchema = [
                    'dashboard' => ['access' => true],
                    'merchants' => ['access' => true, 'edit' => true],
                    'brands' => ['access' => true, 'edit' => true],
                ];
            }

            DB::table('pp_permission')->insert([
                'brand_id' => $brand_id,
                'a_id' => $a_id,
                'permission' => json_encode($permissionSchema),
                'status' => 'active',
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

            DB::commit();

            return redirect()->route('superadmin.merchants.index')->with('success', 'Merchant and Brand created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Merchant creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Error creating merchant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified merchant
     */
    public function show($id)
    {
        $merchant = PpAdmin::where('pp_admin.a_id', $id)
            ->leftJoin('pp_permission', 'pp_admin.a_id', '=', 'pp_permission.a_id')
            ->leftJoin('pp_brands', 'pp_permission.brand_id', '=', 'pp_brands.brand_id')
            ->leftJoin('pp_plans', 'pp_admin.plan_id', '=', 'pp_plans.id')
            ->select(
                'pp_admin.*',
                'pp_brands.name as brand_name',
                'pp_brands.logo as brand_logo',
                'pp_brands.country as brand_country',
                'pp_brands.support_email_address as support_email',
                'pp_brands.support_phone_number as support_phone',
                'pp_brands.support_website',
                'pp_brands.brand_id',
                'pp_plans.name as plan_name',
                'pp_plans.features as plan_features'
            )
            ->firstOrFail();

        return view('superadmin.pages.merchants.show', compact('merchant'));
    }

    /**
     * Show the form for editing the merchant
     */
    public function edit($id)
    {
        $merchant = PpAdmin::where('pp_admin.a_id', $id)
            ->leftJoin('pp_permission', 'pp_admin.a_id', '=', 'pp_permission.a_id')
            ->leftJoin('pp_brands', 'pp_permission.brand_id', '=', 'pp_brands.brand_id')
            ->select(
                'pp_admin.*',
                'pp_brands.name as brand_name',
                'pp_brands.logo as brand_logo',
                'pp_brands.country as brand_country',
                'pp_brands.support_email_address as support_email',
                'pp_brands.support_phone_number as support_phone',
                'pp_brands.support_website',
                'pp_brands.brand_id'
            )
            ->firstOrFail();

        $plans = \App\Models\PpPlan::where('is_active', true)->get();
        return view('superadmin.pages.merchants.edit', compact('merchant', 'plans'));
    }

    /**
     * Update the specified merchant
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:pp_admin,username,' . $id . ',a_id',
            'email' => 'required|email|max:255|unique:pp_admin,email,' . $id . ',a_id',
            'brand_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'status' => 'required|in:active,suspend',
            'plan_id' => 'required|exists:pp_plans,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $merchant = PpAdmin::where('a_id', $id)->firstOrFail();
            $permission = DB::table('pp_permission')->where('a_id', $id)->first();
            $brand = DB::table('pp_brands')->where('brand_id', $permission->brand_id)->first();

            // 1. Update Merchant Account
            $merchantData = [
                'full_name' => $request->full_name,
                'username' => $request->username,
                'email' => $request->email,
                'status' => $request->status,
                'plan_id' => $request->plan_id,
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ];

            if ($request->filled('password')) {
                $merchantData['password'] = Hash::make($request->password);
            }

            PpAdmin::where('a_id', $id)->update($merchantData);

            // 2. Update Brand Identity
            $brandData = [
                'name' => $request->brand_name,
                'support_email_address' => $request->support_email,
                'support_phone_number' => $request->support_phone,
                'support_website' => $request->support_website,
                'country' => $request->country,
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ];

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = time() . '_' . $logo->getClientOriginalName();
                $logo->move(storage_path('app/public/media/logos'), $logoName);
                $brandData['logo'] = 'storage/media/logos/' . $logoName;
            }

            DB::table('pp_brands')->where('brand_id', $brand->brand_id)->update($brandData);

            DB::commit();

            return redirect()->route('superadmin.merchants.show', $id)->with('success', 'Merchant updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Suspend a merchant
     */
    public function suspend($id)
    {
        $merchant = PpAdmin::where('a_id', $id)->firstOrFail();
        $merchant->status = 'suspend';
        $merchant->updated_date = now()->format('Y-m-d H:i:s');
        $merchant->save();

        return redirect()->back()->with('success', 'Merchant suspended successfully');
    }

    /**
     * Reactivate a merchant
     */
    public function reactivate($id)
    {
        $merchant = PpAdmin::where('a_id', $id)->firstOrFail();
        $merchant->status = 'active';
        $merchant->updated_date = now()->format('Y-m-d H:i:s');
        $merchant->save();

        return redirect()->back()->with('success', 'Merchant reactivated successfully');
    }
}
