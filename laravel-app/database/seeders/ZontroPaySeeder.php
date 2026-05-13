<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ZontroPaySeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        $brandId = 'ZNT' . strtoupper(Str::random(7));
        $adminId = 'ADM' . strtoupper(Str::random(7));

        // 1. Create Default Plan
        DB::table('pp_plans')->insert([
            'name' => 'ZontroPay Enterprise',
            'slug' => 'enterprise',
            'description' => 'The complete enterprise plan for ZontroPay platform management.',
            'price' => 0.00,
            'currency' => 'USD',
            'interval' => 'month',
            'features' => json_encode([
                'invoices' => true,
                'payment_links' => true,
                'api_access' => true,
                'settlements' => true,
                'multi_currency' => true,
            ]),
            'is_active' => 1,
            'is_default' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $planId = DB::getPdo()->lastInsertId();

        // 2. Create Default Brand
        DB::table('pp_brands')->insert([
            'brand_id' => $brandId,
            'name' => 'ZontroPay',
            'identify_name' => 'zontropay',
            'support_email_address' => 'support@zontropay.com',
            'support_phone_number' => '+880123456789',
            'support_website' => 'https://zontropay.com',
            'theme' => 'twenty-six',
            'country' => 'Bangladesh',
            'timezone' => 'Asia/Dhaka',
            'language' => 'en',
            'currency_code' => 'BDT',
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        // 3. Create SuperAdmin User
        DB::table('pp_admin')->insert([
            'a_id' => $adminId,
            'full_name' => 'ZontroPay Root',
            'username' => 'root',
            'email' => 'root@zontropay',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'role' => 'admin',
            'user_type' => 'superadmin',
            'plan_id' => $planId,
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        // 4. Set Permissions
        DB::table('pp_permission')->insert([
            'brand_id' => $brandId,
            'a_id' => $adminId,
            'permission' => json_encode([
                'dashboard' => ['access' => true],
                'merchants' => ['access' => true, 'edit' => true, 'delete' => true],
                'brands' => ['access' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'plans' => ['access' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'invoice' => ['access' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'payment_link' => ['access' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'gateways' => ['access' => true, 'create' => true, 'edit' => true, 'delete' => true],
            ]),
            'status' => 'active',
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        // 5. Default Currency
        DB::table('pp_currency')->insert([
            'brand_id' => $brandId,
            'code' => 'BDT',
            'symbol' => 'Tk',
            'rate' => '1.00000000',
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        // 6. Default Env Settings
        $options = [
            'geneal-application-settings-paymentPath' => '--',
            'geneal-application-settings-invoicePath' => '--',
            'geneal-application-settings-paymentLinkPath' => '--',
            'geneal-application-settings-adminPath' => '--',
            'geneal-application-settings-cronPath' => '--',
            'geneal-application-settings-homepageRedirect' => '--',
        ];

        foreach ($options as $key => $val) {
            DB::table('pp_env')->insert([
                'brand_id' => 'both',
                'option_name' => $key,
                'value' => $val,
                'created_date' => $now,
                'updated_date' => $now,
            ]);
        }
    }
}
