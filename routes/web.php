<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\Api\SearchController; // Importa el controlador de búsqueda
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AreaAdmin\UserController as AreaAdminUserController;
use App\Http\Controllers\AreaAdmin\FolderPermissionController;
use App\Http\Controllers\Admin\OrganigramController; // Importa el controlador del Organigrama
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // ¡Añade esta línea para usar Str::endsWith!
use App\Http\Controllers\FileLinkController;
use App\Http\Controllers\Admin\OrganigramPositionController;

Route::get('/terms-conditions', function () {
    return view('terms-conditions');
})->name('terms.conditions');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy.policy');

Route::get('/cookies-policy', function () {
    return view('cookies-policy');
})->name('cookies.policy');



// REDIRECCIÓN AUTOMÁTICA DE LA RAÍZ A LA PÁGINA DE LOGIN
Route::get('/', function () {
    return redirect()->route('login');
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
    // La ruta de creación de carpetas debe ir antes de la ruta general con parámetro opcional
    Route::get('/folders/create/{folder?}', [FolderController::class, 'create'])->name('folders.create');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');

    // Rutas para la gestión de archivos/enlaces
    Route::get('/folders/{folder}/file-links/create', [FolderController::class, 'createFileLink'])->name('file_links.create');
    Route::post('/folders/{folder}/file-links', [FolderController::class, 'storeFileLink'])->name('file_links.store');
    Route::get('/file-links/{fileLink}/edit', [FileLinkController::class, 'edit'])->name('file_links.edit');
    Route::put('/file-links/{fileLink}', [FileLinkController::class, 'update'])->name('file_links.update');
    Route::delete('/file-links/{fileLink}', [FileLinkController::class, 'destroy'])->name('file_links.destroy');
    // Rutas para arrastrar y soltar carpetas y subir archivos (NUEVAS RUTAS PARA D&D)
    Route::put('/folders/move', [FolderController::class, 'moveFolder'])->name('folders.move'); // Mover carpeta
    Route::post('/folders/upload-dropped-files', [FolderController::class, 'uploadDroppedFiles'])->name('folders.uploadDroppedFiles'); // Subir archivos arrastrados

    // RUTA PARA ELIMINACIÓN MASIVA (¡AÑADE ESTA LÍNEA!)
    Route::delete('/folders/bulk-delete', [FolderController::class, 'bulkDelete'])->name('folders.bulk_delete');
    

    // Ruta para descarga directa de archivos (usada por la búsqueda predictiva y clics en tabla)
    Route::get('/files/{fileLink}/download', function (App\Models\FileLink $fileLink) {
        if ($fileLink->type === 'file' && Storage::disk('public')->exists($fileLink->path)) {
            // Asegúrate de que el usuario tiene permisos para descargar este archivo
            $user = Auth::user();
            if ($user->area && $user->area->name === 'Administración') {
                // Super Admin puede descargar
            } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
                // Admin de Área puede descargar
            } elseif ($fileLink->folder->area_id === $user->area_id && $user->accessibleFolders->contains($fileLink->folder->id)) {
                // Usuario normal con acceso explícito y en su área
            } else {
                abort(403, 'No tienes permiso para descargar este archivo.');
            }

            // Lógica para asegurar el nombre de descarga correcto
            $originalExtension = pathinfo($fileLink->path, PATHINFO_EXTENSION);
            $downloadFileName = $fileLink->name;

            // Si el nombre asignado no termina con la extensión original, la añadimos
            if (!Str::endsWith(strtolower($fileLink->name), '.' . strtolower($originalExtension))) {
                $downloadFileName .= '.' . strtolower($originalExtension);
            }

            return Storage::disk('public')->download($fileLink->path, $downloadFileName);
        }
        abort(404);
    })->name('files.download');


    // La ruta general para listar/mostrar carpetas
    Route::get('/folders/{folder?}', [FolderController::class, 'index'])->name('folders.index');

    // Rutas de edición y eliminación de carpetas
    Route::get('/folders/{folder}/edit', [FolderController::class, 'edit'])->name('folders.edit');
    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    // Ruta para sugerencias de búsqueda (API para frontend, protegida por 'auth' de sesión)
    Route::get('/search-suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions'); // ¡Añadido el nombre de la ruta!
    Route::get('/dashboard-data', [App\Http\Controllers\DashboardController::class, 'data'])->name('dashboard.data');
});


// Rutas de administración (solo accesibles por el área de Administración)
Route::middleware(['auth', 'check.area:Administración'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

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

    // Rutas para la gestión del Organigrama (NUEVAS RUTAS)
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

        // ¡NUEVAS RUTAS para gestionar Posiciones del Organigrama (CRUD)!
        Route::prefix('positions')->name('positions.')->group(function () {
            Route::get('/', [OrganigramPositionController::class, 'index'])->name('index');
            Route::post('/', [OrganigramPositionController::class, 'store'])->name('store');
            Route::put('/{organigram_position}', [OrganigramPositionController::class, 'update'])->name('update');
            Route::delete('/{organigram_position}', [OrganigramPositionController::class, 'destroy'])->name('destroy');

        });
        Route::get('/interactive', [OrganigramController::class, 'interactiveOrganigram'])->name('interactive');
        Route::get('/interactive-data', [OrganigramController::class, 'getInteractiveOrganigramData'])->name('interactive.data');

    });    
});


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
});

// Rutas de perfil de Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
