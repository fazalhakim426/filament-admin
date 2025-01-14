<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });


        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-increment ID
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('city_id')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('role')->default('customer');
            $table->string('referral_code')->unique()->nullable();
            $table->decimal('balance', 10, 2)->default(0.00);

            $table->softDeletes();
            $table->timestamps();
        });
        // Suppliers table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('business_name');
            $table->string('contact_person')->nullable();
            $table->string('website')->nullable();
            $table->string('supplier_type')->nullable();
            $table->string('main_category_id')->nullable()->constrained('categories');
            $table->string('secondary_category_id')->nullable()->constrained('categories');
            $table->integer('product_available')->default(0);
            $table->string('product_source')->nullable();
            $table->string('product_unit_quality')->nullable();
            $table->boolean('self_listing')->default(false);
            $table->string('product_range')->nullable();
            $table->boolean('using_daraz')->default(false);
            $table->string('daraz_url')->nullable();
            $table->string('ecommerce_experience')->nullable();
            $table->string('term_agreed')->nullable();
            $table->foreignId('marketing_type');
            $table->timestamp('preferred_contact_time')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('selling_price', 10, 2);
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity');
            $table->string('sku')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'canceled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
        });
        // Payments table
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->enum('deposit_type', ['card', 'bank', 'admin', 'wallet']);
            $table->string('transaction_reference')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Referrals table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('referral_code');
            $table->decimal('reward_amount', 10, 2)->default(0.00);
            $table->softDeletes();

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('users');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('cities');
    }
};
