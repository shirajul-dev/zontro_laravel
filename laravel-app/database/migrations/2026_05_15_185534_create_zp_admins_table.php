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
        Schema::create('zp_admins', function (Blueprint $table) {
            $table->id();
            $table->string('a_id', 15)->unique();
            $table->string('full_name');
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('status', ['active', 'suspend'])->default('active');
            $table->enum('role', ['admin', 'staff'])->default('admin');
            $table->enum('user_type', ['superadmin', 'merchant'])->default('merchant');
            $table->enum('two_fa_status', ['enable', 'disable'])->default('disable');
            $table->string('two_fa_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zp_admins');
    }
};
