<?php

namespace Database\Seeders;

use App\Models\Area; // Importa el modelo Area
use App\Models\User; // Importa el modelo User
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Importa Hash

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AreaSeeder::class,
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
                ]
            );
        }

        // Puedes crear más usuarios de prueba para otras áreas si lo deseas
    }
}