<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pp_addon', function (Blueprint $table) {
            $table->increments('id');
            $table->string('addon_id', 15);
            $table->string('slug', 40)->default('--');
            $table->text('name')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['addon_id', 'status', 'created_date', 'updated_date'], 'addon_id');
        });

        Schema::create('pp_addon_parameter', function (Blueprint $table) {
            $table->increments('id');
            $table->string('addon_id', 15);
            $table->string('option_name', 50);
            $table->text('value');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['addon_id', 'option_name', 'created_date', 'updated_date'], 'addon_id');
        });

        Schema::create('pp_admin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('a_id', 15);
            $table->text('full_name');
            $table->string('username', 50);
            $table->string('email', 100);
            $table->text('password');
            $table->text('temp_password')->nullable();
            $table->string('reset_limit', 10)->default('3');
            $table->enum('status', ['active', 'suspend'])->default('active');
            $table->enum('role', ['admin', 'staff'])->default('admin');
            $table->enum('user_type', ['superadmin', 'merchant'])->default('merchant');
            $table->enum('2fa_status', ['enable', 'disable'])->default('disable');
            $table->string('2fa_secret', 20)->default('--');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['a_id', 'email'], 'a_id');
            $table->index('username', 'username');
            $table->index(['created_date', 'updated_date'], 'created_date');
        });

        Schema::create('pp_api', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->text('name');
            $table->string('api_key', 60);
            $table->text('expired_date')->nullable();
            $table->text('api_scopes');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['brand_id', 'api_key', 'created_date', 'updated_date'], 'brand_id');
        });

        Schema::create('pp_balance_verification', function (Blueprint $table) {
            $table->increments('id');
            $table->string('device_id', 15);
            $table->string('sender_key', 15);
            $table->enum('type', ['Personal', 'Agent', 'Merchant'])->default('Personal');
            $table->decimal('current_balance', 20, 8);
            $table->string('simslot', 6);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['device_id', 'sender_key', 'type', 'created_date', 'updated_date'], 'device_id');
            $table->index('simslot', 'simslot');
            $table->index('status', 'status');
        });

        Schema::create('pp_brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->text('favicon')->nullable();
            $table->text('logo')->nullable();
            $table->string('identify_name', 50)->default('Default');
            $table->text('name')->nullable();
            $table->text('support_email_address')->nullable();
            $table->text('support_phone_number')->nullable();
            $table->text('support_website')->nullable();
            $table->text('whatsapp_number')->nullable();
            $table->text('telegram')->nullable();
            $table->text('facebook_messenger')->nullable();
            $table->text('facebook_page')->nullable();
            $table->string('theme', 120)->default('twenty-six');
            $table->text('street_address')->nullable();
            $table->text('city_town')->nullable();
            $table->text('postal_code')->nullable();
            $table->text('country')->nullable();
            $table->string('timezone', 150)->default('Asia/Dhaka');
            $table->string('language', 150)->default('en');
            $table->string('currency_code', 150)->default('BDT');
            $table->enum('autoExchange', ['disabled', 'enabled'])->default('disabled');
            $table->string('payment_tolerance', 150)->default('0');
            $table->string('created_date', 20)->default('--');
            $table->string('updated_date', 20)->default('--');

            $table->index('brand_id', 'brand_id');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index('identify_name', 'identify_name');
            $table->index('autoExchange', 'autoExchange');
        });

        Schema::create('pp_browser_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('a_id', 15);
            $table->string('cookie', 40);
            $table->string('browser', 10);
            $table->string('device', 10);
            $table->string('ip', 15);
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['a_id', 'cookie', 'created_date', 'updated_date'], 'a_id');
            $table->index('created_date', 'created_date');
            $table->index('status', 'status');
        });

        Schema::create('pp_currency', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->string('code', 6);
            $table->string('symbol', 5);
            $table->decimal('rate', 20, 8)->default('0.00000000');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['brand_id', 'code', 'symbol'], 'brand_id');
        });

        Schema::create('pp_customer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref', 15);
            $table->string('brand_id', 15);
            $table->text('name');
            $table->string('email', 100);
            $table->string('mobile', 15);
            $table->enum('status', ['active', 'suspend'])->default('active');
            $table->text('suspend_reason')->nullable();
            $table->enum('inserted_via', ['manual', 'checkout'])->default('manual');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['ref', 'brand_id', 'email', 'mobile'], 'ref');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index(['status', 'inserted_via'], 'status');
        });

        Schema::create('pp_device', function (Blueprint $table) {
            $table->increments('id');
            $table->string('d_id', 40);
            $table->string('device_id', 15);
            $table->string('otp', 15);
            $table->text('name')->nullable();
            $table->text('model')->nullable();
            $table->text('android_level')->nullable();
            $table->text('app_version')->nullable();
            $table->enum('status', ['processing', 'used'])->default('processing');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);
            $table->string('last_sync', 20)->default('--');

            $table->index('device_id', 'device_id');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index('d_id', 'a_id');
            $table->index('otp', 'otp');
            $table->index('status', 'status');
        });

        Schema::create('pp_domain', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain', 50);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index('domain', 'domain');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index('status', 'status');
        });

        Schema::create('pp_env', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15)->default('both');
            $table->string('option_name', 50);
            $table->text('value');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index('option_name', 'option_name');
            $table->index('brand_id', 'brand_id');
            $table->index(['created_date', 'updated_date'], 'created_date');
        });

        Schema::create('pp_faq', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->text('title');
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['brand_id', 'created_date', 'updated_date'], 'brand_id');
            $table->index('status', 'status');
        });

        Schema::create('pp_gateways', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gateway_id', 15);
            $table->string('brand_id', 15);
            $table->string('slug', 40)->default('--');
            $table->text('name')->nullable();
            $table->text('display')->nullable();
            $table->text('logo')->nullable();
            $table->string('currency', 6);
            $table->decimal('min_allow', 20, 8)->default('0.00000000');
            $table->decimal('max_allow', 20, 8)->default('0.00000000');
            $table->decimal('fixed_discount', 20, 8)->default('0.00000000');
            $table->decimal('percentage_discount', 20, 8)->default('0.00000000');
            $table->decimal('fixed_charge', 20, 8)->default('0.00000000');
            $table->decimal('percentage_charge', 20, 8)->default('0.00000000');
            $table->text('primary_color')->nullable();
            $table->text('text_color')->nullable();
            $table->text('btn_color')->nullable();
            $table->text('btn_text_color')->nullable();
            $table->enum('tab', ['mfs', 'bank', 'global']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['brand_id', 'slug'], 'brand_id');
            $table->index('gateway_id', 'g_id');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index('tab', 'tab');
            $table->index('status', 'status');
        });

        Schema::create('pp_gateways_parameter', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->string('gateway_id', 15);
            $table->string('option_name', 50);
            $table->text('value');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['gateway_id', 'option_name'], 'slug');
            $table->index('brand_id', 'brand_id');
            $table->index(['created_date', 'updated_date'], 'created_date');
        });

        Schema::create('pp_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref', 30);
            $table->string('brand_id', 15);
            $table->text('customer_info')->nullable();
            $table->string('gateway_id', 15)->default('--');
            $table->text('currency');
            $table->text('due_date')->nullable();
            $table->string('shipping', 250)->default('0');
            $table->enum('status', ['paid', 'unpaid', 'refunded', 'canceled']);
            $table->text('note')->nullable();
            $table->text('private_note')->nullable();
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['ref', 'brand_id'], 'ref');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index('status', 'status');
        });

        Schema::create('pp_invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->string('invoice_id', 30);
            $table->text('description')->nullable();
            $table->decimal('amount', 20, 8)->default('0.00000000');
            $table->integer('quantity')->default(0);
            $table->decimal('discount', 20, 8)->default('0.00000000');
            $table->decimal('vat', 20, 8)->default('0.00000000');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index('invoice_id', 'invoice_id');
            $table->index('brand_id', 'brand_id');
            $table->index(['created_date', 'updated_date'], 'created_date');
        });

        Schema::create('pp_payment_link', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref', 30);
            $table->string('brand_id', 15);
            $table->text('product_info');
            $table->decimal('amount', 20, 8)->default('0.00000000');
            $table->integer('quantity')->default(0);
            $table->text('currency');
            $table->text('expired_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['ref', 'brand_id', 'created_date', 'updated_date'], 'ref');
            $table->index('status', 'status');
        });

        Schema::create('pp_payment_link_field', function (Blueprint $table) {
            $table->increments('id');
            $table->string('paymentLinkID', 30);
            $table->text('formType');
            $table->text('fieldName');
            $table->text('value');
            $table->enum('required', ['true', 'false'])->default('true');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index('paymentLinkID', 'paymentLinkID');
        });

        Schema::create('pp_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->string('a_id', 15);
            $table->text('permission');
            $table->enum('status', ['active', 'suspend'])->default('active');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['brand_id', 'a_id', 'created_date', 'updated_date'], 'brand_id');
        });

        Schema::create('pp_sms_data', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('source', ['app', 'web'])->default('web');
            $table->string('device_id', 15);
            $table->string('sender', 15)->default('--');
            $table->string('sender_key', 15);
            $table->text('simslot')->nullable();
            $table->string('number', 20)->default('--');
            $table->decimal('amount', 20, 8)->default('0.00000000');
            $table->string('currency', 10)->default('--');
            $table->string('trx_id', 100)->default('--');
            $table->string('balance', 70)->default('--');
            $table->text('message')->nullable();
            $table->text('reason')->nullable();
            $table->enum('type', ['Personal', 'Agent', 'Merchant'])->default('Personal');
            $table->enum('entry_type', ['manual', 'automatic'])->default('automatic');
            $table->enum('edit_status', ['done', 'pending'])->default('pending');
            $table->enum('status', ['approved', 'awaiting-review', 'used', 'error'])->default('approved');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['sender_key', 'amount', 'trx_id'], 'device_id');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index('number', 'number');
            $table->index('balance', 'balance');
            $table->index('device_id', 'device_id_2');
            $table->index('sender', 'sender');
            $table->index('source', 'source');
            $table->index(['type', 'entry_type', 'edit_status', 'status'], 'type');
        });

        Schema::create('pp_transaction', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_id', 15);
            $table->enum('source', ['invoice', 'payment-link', 'payment-link-default', 'api'])->default('api');
            $table->string('ref', 30);
            $table->text('customer_info');
            $table->decimal('amount', 20, 8)->default('0.00000000');
            $table->decimal('processing_fee', 20, 8)->default('0.00000000');
            $table->decimal('discount_amount', 20, 8)->default('0.00000000');
            $table->decimal('local_net_amount', 20, 8)->default('0.00000000');
            $table->text('currency')->nullable();
            $table->text('local_currency')->nullable();
            $table->string('sender', 50)->default('--');
            $table->string('trx_id', 70)->default('--');
            $table->text('trx_slip')->nullable();
            $table->string('gateway_id', 50)->default('--');
            $table->string('sender_key', 50)->default('--');
            $table->string('sender_type', 11);
            $table->text('source_info')->nullable();
            $table->text('metadata')->nullable();
            $table->enum('status', ['completed', 'pending', 'refunded', 'initiated', 'canceled'])->default('initiated');
            $table->text('return_url')->nullable();
            $table->text('webhook_url')->nullable();
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index(['brand_id', 'ref', 'trx_id'], 'brand_id');
            $table->index(['gateway_id', 'sender_key'], 'payment_method_id');
            $table->index('sender_key', 'gateway_slug');
            $table->index(['created_date', 'updated_date'], 'created_date');
            $table->index('sender', 'sender');
            $table->index(['source', 'status'], 'source');
            $table->index('sender_type', 'sender_type');
        });

        Schema::create('pp_webhook_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref', 15);
            $table->string('brand_id', 15);
            $table->text('payload');
            $table->text('url');
            $table->integer('attempts')->default(0);
            $table->text('response_body')->nullable();
            $table->text('http_code')->nullable();
            $table->enum('status', ['completed', 'pending', 'canceled'])->default('pending');
            $table->string('created_date', 20);
            $table->string('updated_date', 20);

            $table->index('ref', 'ref');
            $table->index('brand_id', 'brand_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_webhook_log');
        Schema::dropIfExists('pp_transaction');
        Schema::dropIfExists('pp_sms_data');
        Schema::dropIfExists('pp_permission');
        Schema::dropIfExists('pp_payment_link_field');
        Schema::dropIfExists('pp_payment_link');
        Schema::dropIfExists('pp_invoice_items');
        Schema::dropIfExists('pp_invoice');
        Schema::dropIfExists('pp_gateways_parameter');
        Schema::dropIfExists('pp_gateways');
        Schema::dropIfExists('pp_faq');
        Schema::dropIfExists('pp_env');
        Schema::dropIfExists('pp_domain');
        Schema::dropIfExists('pp_device');
        Schema::dropIfExists('pp_customer');
        Schema::dropIfExists('pp_currency');
        Schema::dropIfExists('pp_browser_log');
        Schema::dropIfExists('pp_brands');
        Schema::dropIfExists('pp_balance_verification');
        Schema::dropIfExists('pp_api');
        Schema::dropIfExists('pp_admin');
        Schema::dropIfExists('pp_addon_parameter');
        Schema::dropIfExists('pp_addon');
    }
};
