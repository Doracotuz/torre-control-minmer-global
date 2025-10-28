<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationSettingsController extends Controller
{
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
        $users = User::where('is_client', false)->orderBy('name')->get();
        
        $settings = NotificationSetting::all()
            ->groupBy('user_id')
            ->map(function ($group) {
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
            'settings.*.*' => 'string',
        ]);

        DB::transaction(function () use ($request) {
            NotificationSetting::query()->delete();
            
            $newSettings = $request->input('settings', []);

            foreach ($newSettings as $userId => $eventKeys) {
                if (is_array($eventKeys)) {
                    foreach ($eventKeys as $eventKey) {
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