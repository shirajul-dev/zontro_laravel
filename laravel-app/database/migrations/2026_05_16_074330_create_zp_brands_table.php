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
        Schema::create('zp_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('brand_id', 15)->unique();
            $table->string('identify_name', 50)->default('Default');
            $table->string('name')->nullable();
            $table->text('logo')->nullable();
            $table->text('favicon')->nullable();
            
            // Support Information
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('support_website')->nullable();
            
            // Preferences
            $table->string('theme', 50)->default('default');
            $table->string('currency_code', 10)->default('USD');
            $table->string('timezone', 100)->default('UTC');
            $table->string('language', 10)->default('en');
            
            // Legacy mapping (optional, for backward compatibility if needed)
            $table->string('legacy_brand_id', 15)->nullable();
            
            $table->timestamps();
            
            $table->foreign('admin_id')->references('id')->on('zp_admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zp_brands');
    }
};
