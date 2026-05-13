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
        Schema::table('pp_admin', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('user_type');
            
            // If we want to enforce FK, we can, but since pp_admin uses varchar a_id often, 
            // let's just keep it as a nullable bigInt for now to match pp_plans.id
            $table->foreign('plan_id')->references('id')->on('pp_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pp_admin', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};
