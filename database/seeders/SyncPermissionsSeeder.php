<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class SyncPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 
        // Artisan::call('permissions:sync', [
        //     '-C' => true, // Pass the `-C` option
        // ]);  
        // //run 
        // //php artisan permissions:sync -C if no permission found.
        // $this->command->info('Permissions have been synced successfully!');
    }
}
