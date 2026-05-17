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
        Schema::create('zp_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('brand_id', 20)->index();
            $table->string('title');
            $table->text('description');
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        // Migrate data from pp_faq if it exists
        if (Schema::hasTable('pp_faq')) {
            $legacyFaqs = DB::table('pp_faq')->get();
            foreach ($legacyFaqs as $faq) {
                DB::table('zp_faqs')->insert([
                    'brand_id' => $faq->brand_id,
                    'title' => $faq->title,
                    'description' => $faq->description,
                    'status' => $faq->status,
                    'created_at' => $faq->created_date ?? now(),
                    'updated_at' => $faq->updated_date ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zp_faqs');
    }
};
