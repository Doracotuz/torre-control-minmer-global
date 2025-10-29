<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AreaSeeder::class,
            OrganigramSkillsActivitiesSeeder::class,
            TicketCategorySeeder::class,
        ]);

        $rhArea = Area::where('name', 'Recursos Humanos')->first();
        if ($rhArea) {
            User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin RH',
                    'password' => Hash::make('password'),
                    'area_id' => $rhArea->id,
                    'is_area_admin' => true,
                ]
            );
        }

        $customerServiceArea = Area::where('name', 'Customer Service')->first();
        if ($customerServiceArea) {
            User::firstOrCreate(
                ['email' => 'customer@example.com'],
                [
                    'name' => 'Customer User',
                    'password' => Hash::make('password'),
                    'area_id' => $customerServiceArea->id,
                    'is_area_admin' => false,
                ]
            );
        }

        $adminArea = Area::where('name', 'Administración')->first();
        if ($adminArea) {
            User::firstOrCreate(
                ['email' => 'superadmin@example.com'],
                [
                    'name' => 'Super Administrador',
                    'password' => Hash::make('password'),
                    'area_id' => $adminArea->id,
                    'is_area_admin' => true,
                ]
            );
        }
    }
}
