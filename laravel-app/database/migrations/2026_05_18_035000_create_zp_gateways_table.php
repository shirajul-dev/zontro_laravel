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
        Schema::create('zp_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_id', 20)->index();
            $table->string('brand_id', 20)->index();
            $table->string('slug', 50)->index();
            $table->string('name')->nullable();
            $table->string('display')->nullable();
            $table->string('logo')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('min_allow', 20, 8)->default(0.00000000);
            $table->decimal('max_allow', 20, 8)->default(0.00000000);
            $table->decimal('fixed_discount', 20, 8)->default(0.00000000);
            $table->decimal('percentage_discount', 20, 8)->default(0.00000000);
            $table->decimal('fixed_charge', 20, 8)->default(0.00000000);
            $table->decimal('percentage_charge', 20, 8)->default(0.00000000);
            $table->string('primary_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('btn_color')->nullable();
            $table->string('btn_text_color')->nullable();
            $table->string('tab', 20)->default('global'); // mfs, bank, global
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('zp_gateway_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('brand_id', 20)->index();
            $table->string('gateway_id', 20)->index();
            $table->string('option_name', 100);
            $table->text('value');
            $table->timestamps();
        });

        // Migrate data from pp_gateways if it exists
        if (Schema::hasTable('pp_gateways')) {
            $legacyGateways = DB::table('pp_gateways')->get();
            foreach ($legacyGateways as $gw) {
                DB::table('zp_gateways')->insert([
                    'gateway_id' => $gw->gateway_id,
                    'brand_id' => $gw->brand_id,
                    'slug' => $gw->slug,
                    'name' => $gw->name,
                    'display' => $gw->display,
                    'logo' => $gw->logo,
                    'currency' => $gw->currency ?? 'USD',
                    'min_allow' => $gw->min_allow ?? 0,
                    'max_allow' => $gw->max_allow ?? 0,
                    'fixed_discount' => $gw->fixed_discount ?? 0,
                    'percentage_discount' => $gw->percentage_discount ?? 0,
                    'fixed_charge' => $gw->fixed_charge ?? 0,
                    'percentage_charge' => $gw->percentage_charge ?? 0,
                    'primary_color' => $gw->primary_color,
                    'text_color' => $gw->text_color,
                    'btn_color' => $gw->btn_color,
                    'btn_text_color' => $gw->btn_text_color,
                    'tab' => $gw->tab ?? 'global',
                    'status' => $gw->status ?? 'active',
                    'created_at' => $gw->created_date ?? now(),
                    'updated_at' => $gw->updated_date ?? now(),
                ]);
            }
        }

        // Migrate parameters data from pp_gateways_parameter if it exists
        if (Schema::hasTable('pp_gateways_parameter')) {
            $legacyParams = DB::table('pp_gateways_parameter')->get();
            foreach ($legacyParams as $param) {
                DB::table('zp_gateway_parameters')->insert([
                    'brand_id' => $param->brand_id,
                    'gateway_id' => $param->gateway_id,
                    'option_name' => $param->option_name,
                    'value' => $param->value,
                    'created_at' => $param->created_date ?? now(),
                    'updated_at' => $param->updated_date ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zp_gateway_parameters');
        Schema::dropIfExists('zp_gateways');
    }
};
