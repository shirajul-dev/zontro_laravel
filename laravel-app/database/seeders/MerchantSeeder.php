<?php

namespace Database\Seeders;

use App\Models\ZpAdmin;
use App\Models\ZpBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchant = ZpAdmin::updateOrCreate(
            ['username' => 'merchant'],
            [
                'a_id' => 'M' . strtoupper(Str::random(10)),
                'username' => 'merchant',
                'email' => 'merchant@zontropay',
                'full_name' => 'Default Merchant',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 'active',
                'user_type' => 'merchant',
            ]
        );

        // Create a default brand for this merchant using the new ZpBrand model
        ZpBrand::updateOrCreate(
            ['admin_id' => $merchant->id, 'is_default' => true],
            [
                'brand_id' => 'B' . strtoupper(Str::random(10)),
                'name' => 'Default',
                'is_default' => true,
                'currency_code' => 'BDT',
                'timezone' => 'Asia/Dhaka',
                'language' => 'en',
                'support_email' => 'support@piprapay.com',
                'support_phone' => '+880123456789',
                'street_address' => '123 Business Way',
                'city_town' => 'Dhaka',
                'country' => 'Bangladesh',
                'telegram' => '@piprapay_support',
            ]
        );

        // Create a secondary brand for cross-checking
        ZpBrand::updateOrCreate(
            ['admin_id' => $merchant->id, 'name' => 'PipraPay Pro'],
            [
                'brand_id' => 'B' . strtoupper(Str::random(10)),
                'name' => 'PipraPay Pro',
                'is_default' => false,
                'currency_code' => 'USD',
                'timezone' => 'UTC',
                'language' => 'en',
                'support_email' => 'pro@piprapay.com',
                'street_address' => '456 Global Tower',
                'city_town' => 'Singapore',
                'country' => 'Singapore',
            ]
        );

        $this->command->info('Default merchant created: merchant@zontropay / 12345678');
        $this->command->info('Two ZpBrands created for the merchant.');
    }
}
