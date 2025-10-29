<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketCategory;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TicketCategory::truncate();
        \App\Models\TicketSubCategory::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            'Hardware' => [
                'Problema de Impresora',
                'Falla de Monitor o Pantalla',
                'Teclado / Mouse no funciona',
                'Equipo de cómputo no enciende',
                'Problema con teléfono o celular',
                'Falla de otro periférico (cámara, audífonos)',
            ],
            'Software y Aplicaciones' => [
                'Error en una aplicación (SAP, Office, etc.)',
                'Solicitud de instalación de programa',
                'Actualización de sistema operativo',
                'Problemas con licencia de software',
                'Correo electrónico no funciona',
                'Navegador web lento o con errores',
            ],
            'Red e Internet' => [
                'Sin acceso a Internet',
                'Conexión de red intermitente o lenta',
                'Problema para conectar a la VPN',
                'No puedo acceder a carpetas compartidas',
                'Falla en la red WiFi',
            ],
            'Cuentas y Accesos' => [
                'Reseteo de Contraseña',
                'Solicitud de creación de nuevo usuario',
                'Modificación de permisos de acceso',
                'Cuenta de usuario bloqueada',
                'Problemas de acceso a una plataforma',
            ],
            'Solicitudes Generales de TI' => [
                'Solicitud de nuevo equipo',
                'Movimiento de lugar o equipo',
                'Cotización de tecnología',
                'Consulta o asesoría técnica',
                'Otro tipo de solicitud',
            ],
        ];

        foreach ($categories as $categoryName => $subCategories) {
            $category = TicketCategory::create(['name' => $categoryName]);

            foreach ($subCategories as $subCategoryName) {
                $category->subCategories()->create(['name' => $subCategoryName]);
            }
        }
    }
}
