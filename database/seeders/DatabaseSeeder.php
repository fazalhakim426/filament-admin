<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\City;
use App\Models\Deposit;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            LocationSeeder::class
        ]);
        $user = User::updateOrCreate(
            [
                'email' => 'admin@manzil.com', // Search criteria
            ],
            [
                'name' => 'Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'city_id' => City::inRandomOrder()->first()->id, // Random city from cities table 
                'referral_code' => Str::upper(Str::random(8)),
                'balance' => 0,
                'phone' => fake()->phoneNumber(),
                'whatsapp' => fake()->phoneNumber(),
                'address' => fake()->address(),
            ]
        );


        // Grant all permissions to admin
        // $adminRole = Role::updateOrCreate(['name' => 'Super Admin']);  
        $user->assignRole('Super Admin');
        // Assign all permissions to the admin role
        // $allPermissions = Permission::all();
        // $adminRole->syncPermissions($allPermissions);


        $this->call([
            SupplierDetailSeeder::class,
            ReferralsSeeder::class,
            EmployeeSeeder::class
        ]);
        $order = Order::all();
        foreach ($order as $order) {
            $order->update([
                'items_cost' => $order->items()->sum(DB::raw('price * quantity')),
            ]);
            if ($order->order_status != OrderStatus::New->value) {
                Deposit::factory()->create([
                    'user_id' => $order->customer_user_id,
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                    'deposit_type' => 'debit',
                    'description' => "Payment for {$order->warehouse_number}"
                ]);
                $order->update([
                    'payment_status' => 'paid'
                ]);
            }
        }
    }
}
