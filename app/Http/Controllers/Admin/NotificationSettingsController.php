<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationSettingsController extends Controller
{
    // Define aquí los eventos que se pueden notificar
    private $notifiableEvents = [
        'login' => 'Inicio de Sesión',
        'created_folder' => 'Creó una carpeta',
        'uploaded_file' => 'Subió un archivo',
        'created_link' => 'Creó un enlace',
        'deleted_folder' => 'Eliminó una carpeta',
        'deleted_file_link' => 'Eliminó un archivo/enlace',
        'moved_item' => 'Movió un elemento',
        'downloaded_file' => 'Descargó un archivo',
        'updated_file_link' => 'Editó un archivo/enlace',
        'updated_folder' => 'Editó una carpeta',
        'deleted_folder_bulk' => 'Eliminación masiva de carpeta',
        'deleted_file_link_bulk' => 'Eliminación masiva de archivo/enlace',
        'moved_folder_bulk' => 'Movió una carpeta',
        'moved_file_link_bulk' => 'Movió un archivo/enlace',
    ];

    public function index()
    {
        // Obtenemos solo los usuarios internos para asignarles notificaciones
        $users = User::where('is_client', false)->orderBy('name')->get();
        
        // Obtenemos la configuración actual y la agrupamos POR USUARIO
        $settings = NotificationSetting::all()
            ->groupBy('user_id')
            ->map(function ($group) {
                // Para cada usuario, obtenemos una lista de los eventos a los que está suscrito
                return $group->pluck('event_name');
            });

        return view('admin.notifications.settings', [
            'events' => $this->notifiableEvents,
            'users' => $users,
            'settings' => $settings,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'settings' => 'nullable|array',
            'settings.*' => 'nullable|array',
            'settings.*.*' => 'string', // Los valores ahora son los 'action_key'
        ]);

        DB::transaction(function () use ($request) {
            // 1. Borramos toda la configuración anterior
            NotificationSetting::query()->delete();
            
            // 2. Leemos la nueva configuración del formulario
            $newSettings = $request->input('settings', []);

            // 3. Insertamos los nuevos registros
            foreach ($newSettings as $userId => $eventKeys) {
                if (is_array($eventKeys)) {
                    foreach ($eventKeys as $eventKey) {
                        // Verificamos que el evento exista para evitar datos maliciosos
                        if (array_key_exists($eventKey, $this->notifiableEvents)) {
                            NotificationSetting::create([
                                'user_id' => $userId,
                                'event_name' => $eventKey,
                            ]);
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Configuración de notificaciones actualizada correctamente.');
    }
}