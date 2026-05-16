<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zp_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('brand_id', 20)->index();
            $table->string('code', 10);
            $table->string('symbol', 10)->nullable();
            $table->decimal('rate', 20, 10)->default(1.00000000);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Migrate data from pp_currency if it exists
        if (Schema::hasTable('pp_currency')) {
            $legacyCurrencies = DB::table('pp_currency')->get();
            foreach ($legacyCurrencies as $currency) {
                DB::table('zp_currencies')->insert([
                    'brand_id' => $currency->brand_id,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'rate' => $currency->rate,
                    'is_default' => false, // We'll set this based on brand settings if needed
                    'created_at' => $currency->created_date ?? now(),
                    'updated_at' => $currency->updated_date ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zp_currencies');
    }
};
