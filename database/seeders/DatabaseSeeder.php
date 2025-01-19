<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;  
use App\Models\City; 
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            CitiesSeeder::class
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
            'contact_number' => fake()->phoneNumber(),
            'whatsapp_number' => fake()->phoneNumber(), 
            'address' => fake()->address(),  
        ]);

     
        // Grant all permissions to admin
        // $adminRole = Role::updateOrCreate(['name' => 'Super Admin']);  
        $user->assignRole('Super Admin');

        // Assign all permissions to the admin role
        // $allPermissions = Permission::all();
        // $adminRole->syncPermissions($allPermissions);


        $this->call([SupplierDetailSeeder::class, ReferralsSeeder::class,
        EmployeeSeeder::class
    ]);
    }
}
