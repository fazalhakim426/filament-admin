<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
        });
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries');
            $table->string('name');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained('states');
            $table->string('name');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('image')->nullable();
        });
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('image')->nullable();
        });

        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('new_supplier_request')->default(true);
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('street')->nullable();
            $table->string('zip')->nullable();
            $table->string('country_id')->nullable()->constrained('countries');
            $table->string('city_id')->nullable()->constrained('cities');
            $table->string('state_id')->nullable()->constrained('states');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['email', 'id']);
        });




        // address Table 
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('address');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('near_by')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('street')->nullable();
            $table->string('zip')->nullable();
            $table->string('country_id')->constrained('countries');
            $table->string('city_id')->constrained('cities');
            $table->string('state_id')->constrained('states');
            $table->timestamps();
        });

        Schema::create('supplier_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('business_name');
            $table->string('contact_person')->nullable();
            $table->string('cnic')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();

            $table->string('website')->nullable();
            $table->string('supplier_type')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories');
            $table->integer('product_available')->default(0);
            $table->string('product_source')->nullable();
            $table->string('product_unit_quality')->nullable();
            $table->boolean('self_listing')->default(false);
            $table->string('product_range')->nullable();
            $table->boolean('using_daraz')->default(false);
            $table->string('daraz_url')->nullable();
            $table->string('ecommerce_experience')->nullable();
            $table->boolean('term_agreed')->default(false);
            $table->text('term_of_services')->nullable();
            $table->string('marketing_type')->nullable();
            $table->timestamp('preferred_contact_time')->nullable();
            $table->softDeletes();
            $table->timestamps();
            //indexing  
        });
        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_user_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('sub_category_id')->constrained('sub_categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('manzil_choice')->default(false);
            $table->boolean('sponsor')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['id']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('unit_selling_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->text('discount_description')->nullable();

            $table->integer('stock_quantity')->default(0);
            $table->string('sku')->nullable();
            $table->timestamps();
        });

        Schema::create('variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->string('attribute_name');
            $table->string('attribute_value');
            $table->timestamps();
        });

        // Orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_number');
            $table->foreignId('customer_user_id')->constrained('users');
            $table->foreignId('supplier_user_id')->constrained('users');
            $table->foreignId('recipient_id')->constrained('addresses');
            $table->foreignId('sender_id')->constrained('addresses');
            $table->decimal('total_price', 10, 2)->nullable()->default(0);
            $table->decimal('shipping_cost', 10, 2)->nullable()->default(0); 
            $table->decimal('items_discount', 10, 2)->nullable()->default(0);
            $table->decimal('items_commission', 10, 2)->nullable()->default(0);
            $table->decimal('items_cost', 10, 2)->nullable()->default(0);
            $table->string('order_status')->default('new');
            //['new', 'processing','confirmed', 'shipped', 'delivered', 'canceled']
            $table->string('payment_status')->default('unpaid');
            //[ 'unpaid','pending', 'paid', 'refunded']
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('order_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status'); // e.g., 'new', 'processing', 'shipped', etc.
            $table->text('note')->nullable(); // Optional note for tracking updates
            $table->timestamps(); // Created_at will act as the tracking timestamp
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Customer who rated
            $table->decimal('rating_stars', 2, 1)->default(0); // Rating stars out of 5
            $table->text('review_text')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'user_id']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_user_id')->constrained('users'); //for searching purpose the get saled items directly not through products and order.for future use.
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('quantity');
            $table->decimal('commission', 10, 2)->nullable()->default(0);
            $table->decimal('discount', 10, 2)->nullable()->default(0);
            $table->decimal('price', 10, 2)->nullable()->default(0);
            $table->enum('order_status', ['pending', 'confirmed', 'canceled'])->default('pending');
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_user_id')->constrained('users');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items');
            $table->enum('type', ['addition', 'deduction']);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('description')->nullable();
            $table->enum('movement_type', ['purchase', 'sale', 'return', 'adjustment']);  // Type of movement
            $table->timestamps();
        });

        // Referrals table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_user_id')->constrained('users');
            $table->foreignId('reseller_user_id')->constrained('users');
            $table->foreignId('order_item_id')->constrained('order_items');
            $table->boolean('reward_released')->default(false);
            $table->decimal('reward_amount', 10, 2)->default(0.00); // 100
            $table->string('referral_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        // Payments table
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference')->nullable();
            $table->foreignId('user_id')->constrained('users'); //deposit own by. 
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('referral_id')->nullable()->constrained('referrals'); //deposit made to supplier. will null for other transactions.
            $table->decimal('amount', 10, 2);
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->string('deposit_type');
            $table->string('currency')->default('PKR');
            $table->string('provider')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
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

        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->enum('type', ['image', 'video']);
            $table->morphs('imageable');
            $table->timestamps();
        });
    }

    public function down()
    {

        Schema::dropIfExists('images');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('variant_options');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('supplier_details');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
