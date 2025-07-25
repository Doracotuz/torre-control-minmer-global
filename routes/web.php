<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AreaAdmin\UserController as AreaAdminUserController;
use App\Http\Controllers\AreaAdmin\FolderPermissionController;
use App\Http\Controllers\Admin\OrganigramController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\FileLinkController;
use App\Http\Controllers\Admin\OrganigramPositionController;
use App\Models\FileLink;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\Rutas\RutasDashboardController;
use App\Http\Controllers\Rutas\RutaController;
use App\Http\Controllers\Rutas\AsignacionController;
use App\Http\Controllers\Rutas\MonitoreoController;
use App\Http\Controllers\Rutas\OperadorController;
use App\Http\Controllers\Rutas\ClienteController;

Route::get('/terms-conditions', function () {
    return view('terms-conditions');
})->name('terms.conditions');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy.policy');

Route::get('/cookies-policy', function () {
    return view('cookies-policy');
})->name('cookies.policy');

// Route::get('/', function () {
//     return redirect()->route('login');
// });

Route::get('/', function () {
    return view('auth.login'); // O el nombre exacto de tu vista de login, que generalmente es 'auth.login' para Breeze/Jetstream
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas protegidas por área (ejemplos del paso anterior)
Route::middleware(['auth', 'check.area:Recursos Humanos'])->group(function () {
    Route::get('/rh-dashboard', function () {
        return "<h1>Bienvenido al Dashboard de Recursos Humanos!</h1>";
    })->name('rh.dashboard');
});

Route::middleware(['auth', 'check.area:Customer Service'])->group(function () {
    Route::get('/customer-service-dashboard', function () {
        return "<h1>Bienvenido al Dashboard de Customer Service!</h1>";
    })->name('customer.dashboard');
});

Route::middleware(['auth', 'check.area:Almacén'])->group(function () {
    Route::get('/almacen-dashboard', function () {
        return "<h1>Bienvenido al Dashboard de Almacén!</h1>";
    })->name('almacen.dashboard');
});

// Rutas para la gestión de carpetas y archivos/enlaces
Route::middleware(['auth'])->group(function () {
    Route::get('/folders/create/{folder?}', [FolderController::class, 'create'])->name('folders.create');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');

    Route::get('/folders/{folder}/file-links/create', [FolderController::class, 'createFileLink'])->name('file_links.create');
    Route::post('/folders/{folder}/file-links', [FolderController::class, 'storeFileLink'])->name('file_links.store');
    Route::get('/file-links/{fileLink}/edit', [FileLinkController::class, 'edit'])->name('file_links.edit');
    Route::put('/file-links/{fileLink}', [FileLinkController::class, 'update'])->name('file_links.update');
    Route::delete('/file-links/{fileLink}', [FileLinkController::class, 'destroy'])->name('file_links.destroy');

    Route::put('/folders/move', [FolderController::class, 'moveFolder'])->name('folders.move');
    Route::post('/folders/upload-dropped-files', [FolderController::class, 'uploadDroppedFiles'])->name('folders.uploadDroppedFiles');

    Route::delete('/folders/bulk-delete', [FolderController::class, 'bulkDelete'])->name('folders.bulk_delete');
    
    Route::get('/folders/api/children', [FolderController::class, 'apiChildren'])->name('folders.api.children');
    Route::post('/items/bulk-move', [FolderController::class, 'bulkMove'])->name('items.bulk_move');

    // Ruta para descarga directa de archivos (usada por la búsqueda predictiva y clics en tabla)
    Route::get('/files/{fileLink}/download', [FileLinkController::class, 'download'])->name('files.download');

    // Route::get('/files/{fileLink}/download', function (FileLink $fileLink) {
    //     if ($fileLink->type === 'file' && Storage::disk('public')->exists($fileLink->path)) {
    //         $user = Auth::user();
    //         if ($user->area && $user->area->name === 'Administración') {
    //             // Super Admin puede descargar
    //         } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
    //             // Admin de Área puede descargar
    //         } elseif ($user->isClient() && $user->accessibleFolders->contains($fileLink->folder->id)) {
    //             // Cliente puede descargar
    //         } elseif ($fileLink->folder->area_id === $user->area_id && $user->accessibleFolders->contains($fileLink->folder->id)) {
    //             // Usuario normal con acceso explícito y en su área
    //         } else {
    //             abort(403, 'No tienes permiso para descargar este archivo.');
    //         }

    //         $originalExtension = pathinfo($fileLink->path, PATHINFO_EXTENSION);
    //         $downloadFileName = $fileLink->name;

    //         if (!Str::endsWith(strtolower($fileLink->name), '.' . strtolower($originalExtension))) {
    //             $downloadFileName .= '.' . strtolower($originalExtension);
    //         }

    //         return Storage::disk('public')->download($fileLink->path, $downloadFileName);
    //     }
    //     abort(404);
    // })->name('files.download');

    Route::get('/folders/{folder?}', [FolderController::class, 'index'])->name('folders.index');
    Route::get('/folders/{folder}/edit', [FolderController::class, 'edit'])->name('folders.edit');
    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::get('/search-suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/dashboard-data', [App\Http\Controllers\DashboardController::class, 'data'])->name('dashboard.data');
});

// Rutas de administración (solo accesibles por el área de Administración)
Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () { //
        return view('admin.dashboard'); //
    })->name('dashboard'); //

    // Rutas para la gestión de Áreas
    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
    Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
    Route::get('/areas/{area}/edit', [AreaController::class, 'edit'])->name('areas.edit');
    Route::put('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');

    // Rutas para la gestión de Usuarios
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/api/folders-for-client-access', [App\Http\Controllers\FolderController::class, 'getFoldersForClientAccess'])->name('api.folders_for_client_access');

    // Rutas para la gestión del Organigrama
    Route::prefix('organigram')->name('organigram.')->group(function () {
        Route::get('/', [OrganigramController::class, 'index'])->name('index');
        Route::get('/create', [OrganigramController::class, 'create'])->name('create');
        Route::post('/', [OrganigramController::class, 'store'])->name('store');
        Route::get('/{organigram_member}/edit', [OrganigramController::class, 'edit'])->name('edit');
        Route::put('/{organigram_member}', [OrganigramController::class, 'update'])->name('update');
        Route::delete('/{organigram_member}', [OrganigramController::class, 'destroy'])->name('destroy');

        // Rutas para gestionar Actividades del Organigrama (CRUD)
        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [OrganigramController::class, 'activitiesIndex'])->name('index');
            Route::post('/', [OrganigramController::class, 'activitiesStore'])->name('store');
            Route::put('/{activity}', [OrganigramController::class, 'activitiesUpdate'])->name('update');
            Route::delete('/{activity}', [OrganigramController::class, 'activitiesDestroy'])->name('destroy');
        });

        // Rutas para gestionar Habilidades del Organigrama (CRUD)
        Route::prefix('skills')->name('skills.')->group(function () {
            Route::get('/', [OrganigramController::class, 'skillsIndex'])->name('index');
            Route::post('/', [OrganigramController::class, 'skillsStore'])->name('store');
            Route::put('/{skill}', [OrganigramController::class, 'skillsUpdate'])->name('update');
            Route::delete('/{skill}', [OrganigramController::class, 'skillsDestroy'])->name('destroy');
        });

        // Rutas para gestionar Posiciones del Organigrama (CRUD)
        Route::prefix('positions')->name('positions.')->group(function () {
            Route::get('/', [OrganigramPositionController::class, 'index'])->name('index');
            Route::post('/', [OrganigramPositionController::class, 'store'])->name('store');
            Route::put('/{organigram_position}', [OrganigramPositionController::class, 'update'])->name('update');
            Route::delete('/{organigram_position}', [OrganigramPositionController::class, 'destroy'])->name('destroy');
        });

        // Rutas interactivas para el organigrama (consolidadas aquí)
        Route::get('/interactive', [OrganigramController::class, 'interactiveOrganigram'])->name('interactive');
        Route::get('/interactive-data', [OrganigramController::class, 'getInteractiveOrganigramData'])->name('interactive.data');
        Route::get('/interactive-data-without-areas', [OrganigramController::class, 'getInteractiveOrganigramDataWithoutAreas'])->name('interactive.data.without-areas');

    }); // Cierra Route::prefix('organigram')
}); // Cierra Route::middleware(['auth', 'check.area:Administración'])


// Rutas para Administradores de Área (solo accesibles por usuarios con is_area_admin = true)
Route::middleware(['auth', 'check.area:area_admin'])->prefix('area-admin')->name('area_admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('area_admin.dashboard');
    })->name('dashboard');

    // Rutas para la gestión de Usuarios por Administrador de Área
    Route::get('/users', [AreaAdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AreaAdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AreaAdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AreaAdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AreaAdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AreaAdminUserController::class, 'destroy'])->name('users.destroy');

    // Rutas para la gestión de Permisos de Carpetas por Administrador de Área
    Route::get('/folder-permissions', [FolderPermissionController::class, 'index'])->name('folder_permissions.index');
    Route::get('/folder-permissions/{folder}/edit', [FolderPermissionController::class, 'edit'])->name('folder_permissions.edit');
    Route::put('/folder-permissions/{folder}', [FolderPermissionController::class, 'update'])->name('folder_permissions.update');

    Route::prefix('visits')->name('visits.')->group(function () {
        Route::get('/create', [App\Http\Controllers\VisitController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\VisitController::class, 'store'])->name('store');
        Route::get('/', [VisitController::class, 'index'])->name('index');
        Route::delete('/{visit}', [VisitController::class, 'destroy'])->name('destroy');
    });
    
});

Route::middleware(['auth', 'check.area:area_admin'])->prefix('rutas')->name('rutas.')->group(function () {
    // Rutas principales del módulo
    Route::get('/dashboard', [RutasDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [RutasDashboardController::class, 'exportCsv'])->name('dashboard.export');
    Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo.index');

    // --- GRUPO PARA GESTIÓN DE PLANTILLAS DE RUTA ---
    Route::prefix('plantillas')->name('plantillas.')->group(function() {
        Route::get('/', [RutaController::class, 'index'])->name('index');
        Route::get('/create', [RutaController::class, 'create'])->name('create');
        Route::post('/', [RutaController::class, 'store'])->name('store');
        Route::get('/{ruta}/edit', [RutaController::class, 'edit'])->name('edit');
        Route::put('/{ruta}', [RutaController::class, 'update'])->name('update');
        Route::delete('/{ruta}', [RutaController::class, 'destroy'])->name('destroy');
        Route::post('/{ruta}/duplicate', [RutaController::class, 'duplicate'])->name('duplicate');
        Route::get('/export', [RutaController::class, 'exportCsv'])->name('export');
        Route::get('/search', [RutaController::class, 'search'])->name('search');
        Route::get('/filter', [RutaController::class, 'filter'])->name('filter');

    });


    // --- NUEVO GRUPO: Para todo lo relacionado con Asignaciones ---
    Route::prefix('asignaciones')->name('asignaciones.')->group(function () {
        Route::get('/', [AsignacionController::class, 'index'])->name('index');
        Route::get('/create', [AsignacionController::class, 'create'])->name('create');
        Route::post('/', [AsignacionController::class, 'store'])->name('store');
        Route::post('/{guia}/assign', [AsignacionController::class, 'assignRoute'])->name('assign');
        Route::post('/import', [AsignacionController::class, 'importCsv'])->name('import');
        Route::get('/template', [AsignacionController::class, 'downloadTemplate'])->name('template');
        Route::get('/export', [AsignacionController::class, 'exportCsv'])->name('export');
        Route::get('/{guia}/edit', [AsignacionController::class, 'edit'])->name('edit');
        Route::put('/{guia}', [AsignacionController::class, 'update'])->name('update');

    });

    Route::prefix('monitoreo')->name('monitoreo.')->group(function () {
        Route::get('/', [MonitoreoController::class, 'index'])->name('index');
        Route::get('/filter', [MonitoreoController::class, 'filter'])->name('filter');
        Route::get('/report', [MonitoreoController::class, 'getReportData'])->name('report');
        Route::get('/regions', [MonitoreoController::class, 'getAvailableRegions'])->name('regions');
        Route::post('/{guia}/start', [MonitoreoController::class, 'startRoute'])->name('start'); 
        Route::post('/{guia}/events', [MonitoreoController::class, 'storeEvent'])->name('events.store');
    });
});

// Route::prefix('tracking')->name('tracking.')->group(function() {
//     // Vista para consultar una o varias facturas
//     Route::get('/', [App\Http\Controllers\Rutas\ClienteController::class, 'index'])->name('index');
    
//     // API para obtener datos de seguimiento de facturas
//     Route::get('/search', [App\Http\Controllers\Rutas\ClienteController::class, 'search'])->name('search');
// });

Route::prefix('v')->name('visits.')->group(function () {
    // Página que muestra la cámara para escanear
    Route::get('/scan', [App\Http\Controllers\VisitController::class, 'showScanPage'])->name('scan.page');
    // Página a la que redirige el QR para validar el token
    Route::get('/validate/{token}', [App\Http\Controllers\VisitController::class, 'showValidationResult'])->name('validate.show');
});

Route::prefix('operador')->name('operador.')->group(function () {
    // Muestra el formulario para ingresar el número de guía
    Route::get('/', [App\Http\Controllers\Rutas\OperadorController::class, 'showLoginForm'])->name('login');

    // Valida el número de guía y redirige a la vista de la ruta
    Route::post('/guia', [App\Http\Controllers\Rutas\OperadorController::class, 'accessGuia'])->name('access');

    // La vista principal del operador para una guía específica
    // Usaremos un parámetro simple por ahora, luego podemos asegurarlo más
    Route::get('/guia/{guia:guia}', [App\Http\Controllers\Rutas\OperadorController::class, 'showGuia'])->name('guia.show');

    Route::post('/guia/{guia:guia}/start', [App\Http\Controllers\Rutas\OperadorController::class, 'startRoute'])->name('guia.start');

    Route::post('/guia/{guia:guia}/event', [App\Http\Controllers\Rutas\OperadorController::class, 'storeEvent'])->name('guia.event.store');
    
});

    Route::get('/tracking', [App\Http\Controllers\Rutas\TrackingController::class, 'index'])->name('tracking.index');


// Route::prefix('operador')->name('operador.')->group(function() {
//     // Página para ingresar el número de guía
//     Route::get('/', [OperadorController::class, 'index'])->name('index'); //

//     // Ruta para verificar la guía y redirigir a los detalles
//     Route::post('/check', [OperadorController::class, 'checkGuia'])->name('check'); //

//     // Importante: Agrupa todas las rutas que usan el parámetro {guia} para que Route Model Binding funcione correctamente
//     // Esto le dice a Laravel que dentro de este grupo, {guia} debe resolverse usando getRouteKeyName() del modelo Guia
//     Route::scopeBindings()->group(function () {
//         // Rutas para la vista de detalles del operador (mostrando los datos de la guía)
//         Route::get('/{guia}', [OperadorController::class, 'show'])->name('show'); //

//         // Ruta para iniciar la ruta
//         Route::post('/{guia}/start-trip', [App\Http\Controllers\Rutas\OperadorController::class, 'startTrip'])->name('start-trip'); //

//         // Rutas para eventos de facturas (Entrega / No Entrega)
//         Route::post('/{guia}/facturas/{factura}/event', [OperadorController::class, 'storeFacturaEvent'])->name('facturas.event'); //

//         // Rutas para eventos de notificación
//         Route::post('/{guia}/notifications/event', [OperadorController::class, 'storeNotificationEvent'])->name('notifications.event'); //
//     });
// });

// Rutas de perfil de Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});



require __DIR__.'/auth.php';