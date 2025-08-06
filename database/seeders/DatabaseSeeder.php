<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AreaSeeder::class,
            OrganigramSkillsActivitiesSeeder::class,
            TicketCategorySeeder::class,
        ]);

        // Crea un usuario de prueba y asigna un área
        $rhArea = Area::where('name', 'Recursos Humanos')->first();
        if ($rhArea) {
            User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin RH',
                    'password' => Hash::make('password'),
                    'area_id' => $rhArea->id,
                    'is_area_admin' => true, // ¡Este usuario es ahora un admin de área!
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
                    'is_area_admin' => false, // No es admin de área
                ]
            );
        }

        // Usuario para el área de Administración (Super Admin)
        $adminArea = Area::where('name', 'Administración')->first();
        if ($adminArea) {
            User::firstOrCreate(
                ['email' => 'superadmin@example.com'],
                [
                    'name' => 'Super Administrador',
                    'password' => Hash::make('password'),
                    'area_id' => $adminArea->id,
                    'is_area_admin' => true, // ¡El Super Admin también es un admin de área!
                ]
            );
        }
    }
}
