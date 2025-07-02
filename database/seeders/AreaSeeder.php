<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Area; // Importa el modelo Area

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            'Recursos Humanos',
            'Customer Service',
            'Tráfico',
            'Almacén',
            'Valor Agregado',
            'POSM',
            'Brokerage',
            'Innovación y Desarrollo',
            'Administración', // ¡Nueva área!
        ];

        foreach ($areas as $areaName) {
            Area::firstOrCreate(['name' => $areaName]);
        }
    }
}
