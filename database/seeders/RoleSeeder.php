<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'supplier', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'reseller', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'customer', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('roles')->insert($roles);
    }
}
