<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Seeder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Super admin', 'guard_name' => 'web'],
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['name' => 'Employee', 'guard_name' => 'web'],
            ['name' => 'Supplier', 'guard_name' => 'web'],
            ['name' => 'Reseller', 'guard_name' => 'web'],
            ['name' => 'Customer', 'guard_name' => 'web'],
        ];
    
        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']], 
                ['guard_name' => $role['guard_name']]
            );
        }
    }
}
