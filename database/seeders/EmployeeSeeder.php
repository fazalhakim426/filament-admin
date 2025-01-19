<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User; // Replace with your User model's namespace if different
use Spatie\Permission\Models\Permission;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $this->call(SyncPermissionsSeeder::class);

        $employees = User::factory(5)->create();
        foreach ($employees as $employee) {
            $employee->assignRole('Employee');
        }

        $employeeRole = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $allPermissions = Permission::all();
        $employeeRole->givePermissionTo($allPermissions);
    }

    // public function run()
    // {
    //     $this->call(SyncPermissionsSeeder::class); 
    //     // Get the 'Employee' role or create it if it doesn't exist
    //     $employeeRole = Role::firstOrCreate(
    //         ['name' => 'Employee'],
    //         ['guard_name' => 'web']
    //     );

    //     // Get all permissions
    //     $allPermissions = Permission::all();

    //     // Assign all permissions to the 'Employee' role
    //     $employeeRole->givePermissionTo($allPermissions);
    // }
}
