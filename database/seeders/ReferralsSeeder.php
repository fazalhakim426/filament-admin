<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Referral;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;

class ReferralsSeeder extends Seeder
{
    public function run()
    {
        // Get all orders
        $orders = Order::with(['customerUser'])->get();

        foreach ($orders as $order) {
            $orderItems = $order->items;
            foreach ($orderItems as $item) {
                $product = $item->product;
                $supplier = $product->supplierUser;
                // Calculate reward based on product referral percentage and item price.
                $rewardAmount = ($product->referral_reward_type == 'fixed') ? $product->referral_reward_value : ($product->referral_reward_value / 100) *( $item->price * $item->quantity);

                $role = Role::where('name', 'reseller')->first();
                $resellerUser = User::inRandomOrder()->first();
                Referral::create([
                    'supplier_user_id' => $supplier->id,
                    'reseller_user_id' =>  $resellerUser->id,
                    'order_item_id' => $item->id,
                    'reward_released' => false,
                    'reward_amount' => $rewardAmount,
                    'referral_code' => $resellerUser->referral_code,
                ]);
            }
        }
    }
}
