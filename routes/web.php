<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FolderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
    // Las rutas más específicas deben ir primero
    Route::get('/folders/create/{folder?}', [FolderController::class, 'create'])->name('folders.create');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');

    // Rutas para la gestión de archivos/enlaces
    Route::get('/folders/{folder}/file-links/create', [FolderController::class, 'createFileLink'])->name('file_links.create');
    Route::post('/folders/{folder}/file-links', [FolderController::class, 'storeFileLink'])->name('file_links.store');
    Route::get('/file-links/{fileLink}/edit', [FolderController::class, 'editFileLink'])->name('file_links.edit');
    Route::put('/file-links/{fileLink}', [FolderController::class, 'updateFileLink'])->name('file_links.update');
    Route::delete('/file-links/{fileLink}', [FolderController::class, 'destroyFileLink'])->name('file_links.destroy');

    // La ruta general para listar/mostrar carpetas (debe ir al final de las rutas de 'folders')
    Route::get('/folders/{folder?}', [FolderController::class, 'index'])->name('folders.index');

    // Rutas de edición y eliminación de carpetas (pueden ir aquí o antes de folders.index)
    Route::get('/folders/{folder}/edit', [FolderController::class, 'edit'])->name('folders.edit');
    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');
});

// Rutas de administración (solo accesibles por el área de Administración)
Route::middleware(['auth', 'check.area:Administración'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Rutas para la gestión de Áreas
    Route::get('/areas', [App\Http\Controllers\Admin\AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/create', [App\Http\Controllers\Admin\AreaController::class, 'create'])->name('areas.create');
    Route::post('/areas', [App\Http\Controllers\Admin\AreaController::class, 'store'])->name('areas.store');
    Route::get('/areas/{area}/edit', [App\Http\Controllers\Admin\AreaController::class, 'edit'])->name('areas.edit');
    Route::put('/areas/{area}', [App\Http\Controllers\Admin\AreaController::class, 'update'])->name('areas.update');
    Route::delete('/areas/{area}', [App\Http\Controllers\Admin\AreaController::class, 'destroy'])->name('areas.destroy');

    // Rutas para la gestión de Usuarios (NUEVAS RUTAS)
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

});

// Rutas para Administradores de Área (solo accesibles por usuarios con is_area_admin = true)
Route::middleware(['auth', 'check.area:area_admin'])->prefix('area-admin')->name('area_admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('area_admin.dashboard');
    })->name('dashboard');

    // Rutas para la gestión de Usuarios por Administrador de Área
    Route::get('/users', [App\Http\Controllers\AreaAdmin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\AreaAdmin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\AreaAdmin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\AreaAdmin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\AreaAdmin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\AreaAdmin\UserController::class, 'destroy'])->name('users.destroy');

    // Rutas para la gestión de Permisos de Carpetas por Administrador de Área (NUEVAS RUTAS)
    Route::get('/folder-permissions', [App\Http\Controllers\AreaAdmin\FolderPermissionController::class, 'index'])->name('folder_permissions.index');
    Route::get('/folder-permissions/{folder}/edit', [App\Http\Controllers\AreaAdmin\FolderPermissionController::class, 'edit'])->name('folder_permissions.edit');
    Route::put('/folder-permissions/{folder}', [App\Http\Controllers\AreaAdmin\FolderPermissionController::class, 'update'])->name('folder_permissions.update');

});


// Rutas de perfil de Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
