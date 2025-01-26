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
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        // Roles Table
        // Schema::create('roles', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name')->unique();
        //     $table->text('description')->nullable();  
        //     $table->timestamps();
        // });


        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->text('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['email', 'id']);
        });

        Schema::create('supplier_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('business_name');
            $table->string('contact_person')->nullable();
            $table->string('website')->nullable();
            $table->string('supplier_type')->nullable();
            $table->foreignId('main_category_id')->nullable()->constrained('categories');
            $table->foreignId('secondary_category_id')->nullable()->constrained('categories');
            $table->integer('product_available')->default(0);
            $table->string('product_source')->nullable();
            $table->string('product_unit_quality')->nullable();
            $table->boolean('self_listing')->default(false);
            $table->string('product_range')->nullable();
            $table->boolean('using_daraz')->default(false);
            $table->string('daraz_url')->nullable();
            $table->string('ecommerce_experience')->nullable();
            $table->boolean('term_agreed')->default(false);
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
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('referral_reward_value', 10, 2)->nullable()->default(0);
            $table->enum('referral_reward_type', ['fixed','percentage'])->default('fixed');
            $table->integer('stock_quantity')->default(0);
            $table->decimal('unit_selling_price', 10, 2); //current selling price
            $table->string('sku')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['id']);
        });
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_user_id')->constrained('users');
            $table->foreignId('product_id')->constrained('products');
            $table->enum('type', ['addition', 'deduction']); // Record whether stock is added or sold
            $table->integer('quantity');
            $table->decimal('unit_cost_price', 10, 2);
            $table->string('description');
            $table->timestamps();
        });

        // Orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_number');
            $table->foreignId('customer_user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed' ,'paid','refund','shipped', 'delivered', 'canceled'])->default('pending');
            //order status handle by admin. admin will handle the order because the order belong to many suppliers through thier products.
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('supplier_user_id')->constrained('users'); //for searching purpose the get saled items directly not through products and order.for future use.
            $table->integer('quantity');
            $table->decimal('profit', 10, 2); // ( unit_selling_price  - unit_cost_price ) * quantity
            $table->decimal('price', 10, 2); //quantity * unit selling price 
            $table->decimal('unit_cost_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed','canceled'])->default('pending');
            //item status handle by supplier as the item belong to supplier.
            $table->decimal('unit_selling_price', 10, 2); 
            //To dedect profit made by suppliers.supplier cant updte this value need to create another product if selling value is different
        });

        // Referrals table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reseller_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->boolean('reward_released')->default(false);
            $table->decimal('reward_amount', 10, 2)->default(0.00);
            $table->string('referral_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        // Payments table
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); //deposit own by. 
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->foreignId('referral_id')->nullable()->constrained('referrals')->onDelete('cascade'); //deposit made to supplier. will null for other transactions.
            $table->decimal('amount', 10, 2);
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->string('deposit_type');
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('description')->nullable();
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
            $table->unsignedBigInteger('imageable_id');
            $table->string('imageable_type');
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
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('supplier_details');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('cities');
    }
};
