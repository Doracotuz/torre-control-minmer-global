<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
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
            'Administración',
            'Tráfico Importaciones',
            'Proyectos',
        ];

        foreach ($areas as $areaName) {
            Area::firstOrCreate(['name' => $areaName]);
        }
    }
}
