<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PipraPayAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->format('Y-m-d H:i:s');

        DB::table('pp_brands')->insert([
            'id' => 1,
            'brand_id' => '6657227357',
            'favicon' => '--',
            'logo' => '--',
            'identify_name' => 'Default',
            'name' => '--',
            'support_email_address' => '--',
            'support_phone_number' => '--',
            'support_website' => '--',
            'whatsapp_number' => '--',
            'telegram' => '--',
            'facebook_messenger' => '--',
            'facebook_page' => '--',
            'theme' => 'twenty-six',
            'street_address' => '--',
            'city_town' => '--',
            'postal_code' => '--',
            'country' => '--',
            'timezone' => 'Asia/Dhaka',
            'language' => 'en',
            'currency_code' => 'BDT',
            'autoExchange' => 'disabled',
            'payment_tolerance' => '0',
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        DB::table('pp_admin')->insert([
            'id' => 1,
            'a_id' => '0784264068',
            'full_name' => 'PipraPay',
            'username' => 'admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('12345678'),
            'temp_password' => Hash::make('12345678'),
            'reset_limit' => '3',
            'status' => 'active',
            'role' => 'admin',
            '2fa_status' => 'disable',
            '2fa_secret' => '72WYO3RE7ZRVSPDW',
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        DB::table('pp_permission')->insert([
            'id' => 1,
            'brand_id' => '6657227357',
            'a_id' => '0784264068',
            'permission' => '{"resources":{},"pages":{}}',
            'status' => 'active',
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        DB::table('pp_currency')->insert([
            'id' => 1,
            'brand_id' => '6657227357',
            'code' => 'BDT',
            'symbol' => 'Tk',
            'rate' => '0.00000000',
            'created_date' => $now,
            'updated_date' => $now,
        ]);

        DB::table('pp_env')->insert([
            [
                'id' => 1,
                'brand_id' => 'both',
                'option_name' => 'geneal-application-settings-paymentPath',
                'value' => '--',
                'created_date' => $now,
                'updated_date' => $now,
            ],
            [
                'id' => 2,
                'brand_id' => 'both',
                'option_name' => 'geneal-application-settings-invoicePath',
                'value' => '--',
                'created_date' => $now,
                'updated_date' => $now,
            ],
            [
                'id' => 3,
                'brand_id' => 'both',
                'option_name' => 'geneal-application-settings-paymentLinkPath',
                'value' => '--',
                'created_date' => $now,
                'updated_date' => $now,
            ],
            [
                'id' => 4,
                'brand_id' => 'both',
                'option_name' => 'geneal-application-settings-adminPath',
                'value' => '--',
                'created_date' => $now,
                'updated_date' => $now,
            ],
            [
                'id' => 5,
                'brand_id' => 'both',
                'option_name' => 'geneal-application-settings-cronPath',
                'value' => '--',
                'created_date' => $now,
                'updated_date' => $now,
            ],
            [
                'id' => 6,
                'brand_id' => 'both',
                'option_name' => 'geneal-application-settings-homepageRedirect',
                'value' => '--',
                'created_date' => $now,
                'updated_date' => $now,
            ],
        ]);
    }
}
