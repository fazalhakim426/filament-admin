<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            CitiesSeeder::class
        ]);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@manzil.com',
            'role_id' => Role::where('name', 'admin')->first()->id
        ]);

        $this->call([SupplierDetailSeeder::class, ReferralsSeeder::class]);
    }
}
