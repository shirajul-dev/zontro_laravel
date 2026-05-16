<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('zp_brands', function (Blueprint $table) {
            // Social & Contact
            $table->string('whatsapp_number')->nullable()->after('support_website');
            $table->string('telegram')->nullable()->after('whatsapp_number');
            $table->string('facebook_messenger')->nullable()->after('telegram');
            $table->string('facebook_page')->nullable()->after('facebook_messenger');
            
            // Address
            $table->text('street_address')->nullable()->after('theme');
            $table->string('city_town')->nullable()->after('street_address');
            $table->string('postal_code')->nullable()->after('city_town');
            $table->string('country')->nullable()->after('postal_code');
            
            // Logic fields
            $table->enum('auto_exchange', ['disabled', 'enabled'])->default('disabled')->after('language');
            $table->string('payment_tolerance')->default('0')->after('auto_exchange');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zp_brands', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number', 'telegram', 'facebook_messenger', 'facebook_page',
                'street_address', 'city_town', 'postal_code', 'country',
                'auto_exchange', 'payment_tolerance'
            ]);
        });
    }
};
