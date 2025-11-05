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
use App\Http\Controllers\TableroController;
use App\Http\Controllers\IndicadoresController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\TicketCategoryController;
use App\Http\Controllers\Rutas\ManiobristaController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CustomerService\CustomerServiceController;
use App\Http\Controllers\CustomerService\ProductController;
use App\Http\Controllers\CustomerService\BrandController;
use App\Http\Controllers\CustomerService\CustomerController;
use App\Http\Controllers\CustomerService\WarehouseController;
use App\Http\Controllers\CustomerService\OrderController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\CustomerService\ReverseLogisticsController;
use App\Http\Controllers\CustomerService\CreditNoteController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WMS\WMSBrandController;
use App\Http\Controllers\WMS\WMSProductTypeController;
use App\Http\Controllers\WMS\WMSProductController;
use App\Http\Controllers\WMS\WMSLocationController;
use App\Http\Controllers\WMS\WMSPurchaseOrderController;
use App\Http\Controllers\WMS\WMSInboundController;
use App\Http\Controllers\WMS\WMSInventoryController;
use App\Http\Controllers\WMS\WMSPhysicalCountController;
use App\Http\Controllers\WMS\WMSSalesOrderController;
use App\Http\Controllers\WMS\WMSPickingController;
use App\Http\Controllers\WMS\WMSReportController;
use App\Http\Controllers\WMS\WMSWarehouseController;
use App\Http\Controllers\WMS\WMSReceivingController;
use App\Http\Controllers\WMS\WMSLpnController;
use App\Http\Controllers\WMS\WMSQualityController;
use App\Http\Controllers\WMS\WMSDashboardController;
use App\Http\Controllers\FriendsAndFamily\FfDashboardController;
use App\Http\Controllers\FriendsAndFamily\FfProductController;
use App\Http\Controllers\FriendsAndFamily\FfInventoryController;
use App\Http\Controllers\FriendsAndFamily\FfSalesController;
use App\Http\Controllers\FriendsAndFamily\FfReportController;

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
    return view('auth.login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->isClient()) {
        return redirect()->route('tablero.index');
    }

    if ($user->isSuperAdmin()) {
        return redirect()->route('admin.home'); 
    }
    
    if ($user->is_area_admin) {
        return redirect()->route('area_admin.dashboard');
    }

    return redirect()->route('user.dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

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

    Route::get('/files/{fileLink}/download', [FileLinkController::class, 'download'])->name('files.download');
    Route::get('/indicadores/{folder}', [IndicadoresController::class, 'show'])->name('indicadores.show')->middleware('auth');
    Route::post('/folders/upload-directory', [FolderController::class, 'uploadDirectory'])->name('folders.uploadDirectory');
    


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
    Route::get('/area-dashboard-data', [App\Http\Controllers\AreaAdmin\DashboardController::class, 'data'])
         ->name('area_admin.dashboard.data');
    Route::get('/my-dashboard', function () {
        return view('area_admin.dashboard');
    })->middleware('verified')->name('user.dashboard');    
    Route::get('/organigrama-interactivo', [OrganigramController::class, 'interactiveOrganigram'])->name('client.organigram.interactive');
    Route::get('/organigrama-data', [OrganigramController::class, 'getInteractiveOrganigramData'])->name('client.organigram.data');
    Route::get('/organigrama-data-sin-areas', [OrganigramController::class, 'getInteractiveOrganigramDataWithoutAreas'])->name('client.organigram.data.without-areas');
    Route::get('/tablero', [TableroController::class, 'index'])->name('tablero.index');
    Route::post('/tablero/upload-kpis', [TableroController::class, 'uploadKpis'])->name('tablero.uploadKpis');
    Route::get('/rfq', [RfqController::class, 'index'])->name('rfq.index');


});

Route::middleware(['auth', 'high.privilege'])->prefix('asset-management')->name('asset-management.')->group(function () {
    Route::get('/', [App\Http\Controllers\AssetManagement\AssetController::class, 'index'])->name('dashboard');

    Route::resource('assets', App\Http\Controllers\AssetManagement\AssetController::class);
    Route::get('assets-export-csv', [App\Http\Controllers\AssetManagement\AssetController::class, 'exportCsv'])->name('assets.export-csv');
    Route::get('assets-filter', [App\Http\Controllers\AssetManagement\AssetController::class, 'filter'])->name('assets.filter');    
    Route::get('assets/{asset}/assign', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('assets/{asset}/assign', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('assignments/import', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'createImport'])->name('assignments.import.create');
    Route::post('assignments/import', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'storeImport'])->name('assignments.import.store');
    Route::get('assignments/import-template', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'downloadTemplate'])->name('assignments.import.template');    
    
    Route::post('assignments/{assignment}/return', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'return'])->name('assignments.return');

    Route::resource('categories', App\Http\Controllers\AssetManagement\CategoryController::class)->except(['show']);
    Route::resource('manufacturers', App\Http\Controllers\AssetManagement\ManufacturerController::class)->except(['show']);
    Route::resource('sites', App\Http\Controllers\AssetManagement\SiteController::class)->except(['show']);
    Route::resource('models', App\Http\Controllers\AssetManagement\ModelController::class);

    Route::get('assignments/{assignment}/pdf', [App\Http\Controllers\AssetManagement\PdfController::class, 'generateAssignmentPdf'])->name('assignments.pdf');
    Route::resource('software-licenses', App\Http\Controllers\AssetManagement\SoftwareLicenseController::class)->except(['show']);
    Route::get('assets/{asset}/software-assignments/create', [App\Http\Controllers\AssetManagement\SoftwareAssignmentController::class, 'create'])->name('software-assignments.create');
    Route::post('assets/{asset}/software-assignments', [App\Http\Controllers\AssetManagement\SoftwareAssignmentController::class, 'store'])->name('software-assignments.store');
    Route::delete('software-assignments/{assignment}', [App\Http\Controllers\AssetManagement\SoftwareAssignmentController::class, 'destroy'])->name('software-assignments.destroy');
    Route::get('assets/{asset}/loan', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'createLoan'])->name('assignments.createLoan');
    Route::post('assets/{asset}/loan', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'storeLoan'])->name('assignments.storeLoan');
    Route::post('assignments/{assignment}/upload-receipt', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'uploadReceipt'])->name('assignments.uploadReceipt');
    Route::get('maintenances', [App\Http\Controllers\AssetManagement\MaintenanceController::class, 'index'])->name('maintenances.index');
    Route::get('assets/{asset}/maintenance/create', [App\Http\Controllers\AssetManagement\MaintenanceController::class, 'create'])->name('maintenances.create');
    Route::post('assets/{asset}/maintenance', [App\Http\Controllers\AssetManagement\MaintenanceController::class, 'store'])->name('maintenances.store');
    Route::get('maintenances/{maintenance}/edit', [App\Http\Controllers\AssetManagement\MaintenanceController::class, 'edit'])->name('maintenances.edit');
    Route::put('maintenances/{maintenance}', [App\Http\Controllers\AssetManagement\MaintenanceController::class, 'update'])->name('maintenances.update');
    Route::get('maintenances/{maintenance}/pdf', [App\Http\Controllers\AssetManagement\MaintenanceController::class, 'generatePdf'])->name('maintenances.pdf');
    Route::post('assignments/{assignment}/upload-return-receipt', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'uploadReturnReceipt'])->name('assignments.uploadReturnReceipt');
    Route::get('assignments/{assignment}/edit', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('assignments/{assignment}', [App\Http\Controllers\AssetManagement\AssignmentController::class, 'update'])->name('assignments.update');
    Route::get('user-dashboard', [App\Http\Controllers\AssetManagement\UserDashboardController::class, 'index'])->name('user-dashboard.index');
    Route::get('user-dashboard/{member}', [App\Http\Controllers\AssetManagement\UserDashboardController::class, 'show'])->name('user-dashboard.show');
    Route::get('user-dashboard/{member}/pdf', [App\Http\Controllers\AssetManagement\UserDashboardController::class, 'generateConsolidatedPdf'])->name('user-dashboard.pdf');
    Route::post('user-dashboard/{member}/upload-receipt', [App\Http\Controllers\AssetManagement\UserDashboardController::class, 'uploadConsolidatedReceipt'])->name('user-dashboard.uploadReceipt');



});

Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::get('/dashboard-data', [App\Http\Controllers\Admin\StatisticsController::class, 'getChartData'])->name('dashboard.data');    
    Route::get('/home', function () {
        return view('dashboard');
    })->name('home');    
    Route::get('/main-dashboard-data', [App\Http\Controllers\DashboardController::class, 'data'])->name('main_dashboard.data');
    Route::resource('ticket-categories', TicketCategoryController::class);
    Route::post('ticket-sub-categories', [TicketCategoryController::class, 'storeSubCategory'])->name('ticket-sub-categories.store');
    Route::put('ticket-sub-categories/{subCategory}', [TicketCategoryController::class, 'updateSubCategory'])->name('ticket-sub-categories.update');
    Route::delete('ticket-sub-categories/{subCategory}', [TicketCategoryController::class, 'destroySubCategory'])->name('ticket-sub-categories.destroy');
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/export-csv', [StatisticsController::class, 'exportCsv'])->name('statistics.export-csv');
    Route::get('/statistics/charts', [StatisticsController::class, 'charts'])->name('statistics.charts');
    Route::get('/notification-settings', [App\Http\Controllers\Admin\NotificationSettingsController::class, 'index'])->name('notifications.settings.index');
    Route::post('/notification-settings', [App\Http\Controllers\Admin\NotificationSettingsController::class, 'store'])->name('notifications.settings.store');    
    Route::post('/users/bulk-delete', [AdminUserController::class, 'bulkDelete'])->name('users.bulk_delete');
    Route::post('/users/bulk-resend-welcome', [AdminUserController::class, 'bulkResendWelcome'])->name('users.bulk_resend_welcome');



    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
    Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
    Route::get('/areas/{area}/edit', [AreaController::class, 'edit'])->name('areas.edit');
    Route::put('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{userToDelete}/transfer-and-destroy', [AdminUserController::class, 'transferAndDestroy'])->name('users.transfer_and_destroy');    

    Route::get('/api/folders-for-client-access', [App\Http\Controllers\FolderController::class, 'getFoldersForClientAccess'])->name('api.folders_for_client_access');

});

Route::middleware(['auth', 'check.organigram.admin'])->prefix('admin/organigram')->name('admin.organigram.')->group(function () {
    Route::get('/', [OrganigramController::class, 'index'])->name('index');
    Route::delete('/bulk-delete', [OrganigramController::class, 'bulkDestroy'])->name('bulk-delete');    
    Route::get('/create', [OrganigramController::class, 'create'])->name('create');
    Route::post('/', [OrganigramController::class, 'store'])->name('store');
    Route::get('/{organigram_member}/edit', [OrganigramController::class, 'edit'])->name('edit');
    Route::put('/{organigram_member}', [OrganigramController::class, 'update'])->name('update');
    Route::delete('/{organigram_member}', [OrganigramController::class, 'destroy'])->name('destroy');

    Route::get('/template', [OrganigramController::class, 'downloadTemplate'])->name('download-template');
    Route::post('/import-csv', [OrganigramController::class, 'importCsv'])->name('import-csv');
    Route::get('/export-csv', [OrganigramController::class, 'exportCsv'])->name('export-csv');    

    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [OrganigramController::class, 'activitiesIndex'])->name('index');
        Route::post('/', [OrganigramController::class, 'activitiesStore'])->name('store');
        Route::put('/{activity}', [OrganigramController::class, 'activitiesUpdate'])->name('update');
        Route::delete('/{activity}', [OrganigramController::class, 'activitiesDestroy'])->name('destroy');
    });

    Route::prefix('skills')->name('skills.')->group(function () {
        Route::get('/', [OrganigramController::class, 'skillsIndex'])->name('index');
        Route::post('/', [OrganigramController::class, 'skillsStore'])->name('store');
        Route::put('/{skill}', [OrganigramController::class, 'skillsUpdate'])->name('update');
        Route::delete('/{skill}', [OrganigramController::class, 'skillsDestroy'])->name('destroy');
    });

    Route::prefix('positions')->name('positions.')->group(function () {
        Route::get('/', [OrganigramPositionController::class, 'index'])->name('index');
        Route::post('/', [OrganigramPositionController::class, 'store'])->name('store');
        Route::put('/{organigram_position}', [OrganigramPositionController::class, 'update'])->name('update');
        Route::delete('/{organigram_position}', [OrganigramPositionController::class, 'destroy'])->name('destroy');
    });

    Route::get('/interactive', [OrganigramController::class, 'interactiveOrganigram'])->name('interactive');
    Route::get('/interactive-data', [OrganigramController::class, 'getInteractiveOrganigramData'])->name('interactive.data');
    Route::get('/interactive-data-without-areas', [OrganigramController::class, 'getInteractiveOrganigramDataWithoutAreas'])->name('interactive.data.without-areas');
});


Route::middleware(['auth', 'check.area:area_admin'])->prefix('area-admin')->name('area_admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('area_admin.dashboard');
    })->name('dashboard');
    Route::post('/switch-area', [\App\Http\Controllers\AreaAdmin\AreaSwitchController::class, 'switch'])->name('switch_area');    

    Route::get('/users', [AreaAdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AreaAdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AreaAdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AreaAdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AreaAdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AreaAdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{userToDelete}/transfer-and-destroy', [AreaAdminUserController::class, 'transferAndDestroy'])->name('users.transfer_and_destroy');

    Route::get('/folder-permissions', [FolderPermissionController::class, 'index'])->name('folder_permissions.index');
    Route::get('/folder-permissions/{folder}/edit', [FolderPermissionController::class, 'edit'])->name('folder_permissions.edit');
    Route::put('/folder-permissions/{folder}', [FolderPermissionController::class, 'update'])->name('folder_permissions.update');

    Route::prefix('visits')->name('visits.')->group(function () {
        Route::get('/create', [App\Http\Controllers\VisitController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\VisitController::class, 'store'])->name('store');
        Route::get('/', [VisitController::class, 'index'])->name('index');
        Route::delete('/{visit}', [VisitController::class, 'destroy'])->name('destroy');
        Route::get('/export', [App\Http\Controllers\VisitController::class, 'exportCsv'])->name('export');
        Route::get('/charts', [App\Http\Controllers\VisitController::class, 'getChartData'])->name('charts');
        Route::get('/{visit}', [VisitController::class, 'show'])->name('show');

    });
    
});

Route::middleware(['auth', 'check.area:area_admin'])->prefix('rutas')->name('rutas.')->group(function () {
    Route::get('/dashboard', [RutasDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [RutasDashboardController::class, 'exportCsv'])->name('dashboard.export');
    Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo.index');
    Route::get('/dashboard/export-tiempos', [RutasDashboardController::class, 'exportTiemposReport'])->name('dashboard.exportTiempos');


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


    Route::prefix('asignaciones')->name('asignaciones.')->group(function () {
        Route::get('/', [AsignacionController::class, 'index'])->name('index');
        Route::get('/create', [AsignacionController::class, 'create'])->name('create');
        Route::get('/search', [AsignacionController::class, 'search'])->name('search');
        Route::post('/add-orders-to-guia', [AsignacionController::class, 'addOrdersToGuia'])->name('add-orders-to-guia');        
        Route::post('/', [AsignacionController::class, 'store'])->name('store');
        Route::post('/{guia}/assign', [AsignacionController::class, 'assignRoute'])->name('assign');
        Route::post('/import', [AsignacionController::class, 'importCsv'])->name('import');
        Route::get('/template', [AsignacionController::class, 'downloadTemplate'])->name('template');
        Route::get('/export', [AsignacionController::class, 'exportCsv'])->name('export');
        Route::get('/{guia}/edit', [AsignacionController::class, 'edit'])->name('edit');
        Route::put('/{guia}', [AsignacionController::class, 'update'])->name('update');
        Route::put('/{guia}/update-number', [AsignacionController::class, 'updateNumber'])->name('update-number');
        Route::get('/{guia}/details', [AsignacionController::class, 'details'])->name('details');

    });

    Route::prefix('monitoreo')->name('monitoreo.')->group(function () {
        Route::get('/', [MonitoreoController::class, 'index'])->name('index');
        Route::get('/filter', [MonitoreoController::class, 'filter'])->name('filter');
        Route::get('/report', [MonitoreoController::class, 'getReportData'])->name('report');
        Route::get('/regions', [MonitoreoController::class, 'getAvailableRegions'])->name('regions');
        Route::post('/{guia}/start', [MonitoreoController::class, 'startRoute'])->name('start'); 
        Route::post('/{guia}/events', [MonitoreoController::class, 'storeEvent'])->name('events.store');
        Route::get('/export-report', [MonitoreoController::class, 'exportReportCsv'])->name('export.report');
        Route::get('/get-statuses', [MonitoreoController::class, 'getAvailableStatuses'])->name('get-statuses');

        
    });
});

// Route::prefix('tracking')->name('tracking.')->group(function() {
//     // Vista para consultar una o varias facturas
//     Route::get('/', [App\Http\Controllers\Rutas\ClienteController::class, 'index'])->name('index');
    
//     // API para obtener datos de seguimiento de facturas
//     Route::get('/search', [App\Http\Controllers\Rutas\ClienteController::class, 'search'])->name('search');
// });

Route::prefix('v')->name('visits.')->group(function () {
    Route::get('/scan', [App\Http\Controllers\VisitController::class, 'showScanPage'])->name('scan.page');
    Route::get('/validate/{token}', [App\Http\Controllers\VisitController::class, 'showValidationResult'])->name('validate.show');
});

Route::prefix('operador')->name('operador.')->group(function () {
    Route::get('/', [App\Http\Controllers\Rutas\OperadorController::class, 'showLoginForm'])->name('login');

    Route::post('/guia', [App\Http\Controllers\Rutas\OperadorController::class, 'accessGuia'])->name('access');

    Route::get('/guia/{guia:guia}', [App\Http\Controllers\Rutas\OperadorController::class, 'showGuia'])->name('guia.show');

    Route::post('/guia/{guia:guia}/start', [App\Http\Controllers\Rutas\OperadorController::class, 'startRoute'])->name('guia.start');

    Route::post('/guia/{guia:guia}/event', [App\Http\Controllers\Rutas\OperadorController::class, 'storeEvent'])->name('guia.event.store');
    
});

Route::middleware(['auth', 'not_client'])->group(function () {
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'storeReply'])->name('tickets.reply.store');
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::post('/tickets/{ticket}/approve-closure', [TicketController::class, 'approveClosure'])->name('tickets.approve-closure');
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assignAgent'])->name('tickets.assign');
    Route::get('/tickets-dashboard', [App\Http\Controllers\TicketDashboardController::class, 'index'])->name('tickets.dashboard');
    Route::get('/tickets-dashboard/charts', [App\Http\Controllers\TicketDashboardController::class, 'getChartData'])->name('tickets.charts');
    Route::post('/tickets/{ticket}/rate', [TicketController::class, 'storeRating'])->name('tickets.rating.store');
    Route::post('/tickets/{ticket}/reject-closure', [TicketController::class, 'rejectClosure'])->name('tickets.reject-closure');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->middleware('super.admin')->name('tickets.destroy');

});

    Route::get('/tracking', [App\Http\Controllers\Rutas\TrackingController::class, 'index'])->name('tracking.index');

Route::prefix('maniobrista')->name('maniobrista.')->group(function () {
    Route::get('/', [ManiobristaController::class, 'showLoginForm'])->name('login');
    Route::post('/acceder', [ManiobristaController::class, 'accessGuia'])->name('access');
    Route::get('/guia/{guia:guia}/{empleado}', [ManiobristaController::class, 'showGuia'])->name('guia.show');
    Route::post('/guia/{guia:guia}/{empleado}/evento', [ManiobristaController::class, 'storeEvent'])->name('guia.event.store');
    Route::post('/guia/{guia:guia}/{empleado}/evidencias', [ManiobristaController::class, 'storeFacturaEvidencias'])->name('guia.evidencias.store');
});

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


Route::middleware(['auth', 'check.area:Customer Service,Administración,Tráfico'])->prefix('customer-service')->name('customer-service.')->group(function () {
    Route::get('/', [CustomerServiceController::class, 'index'])->name('index');

    Route::middleware('is_area_admin')->prefix('products')->name('products.')->group(function() {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/search', [ProductController::class, 'search'])->name('search');        
        Route::get('/filter', [ProductController::class, 'filter'])->name('filter');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

        Route::get('/export', [ProductController::class, 'exportCsv'])->name('export');
        Route::post('/import', [ProductController::class, 'importCsv'])->name('import');
        Route::get('/template', [ProductController::class, 'downloadTemplate'])->name('template');
        
        Route::get('/dashboard', [ProductController::class, 'dashboard'])->name('dashboard');

    });
        Route::middleware('is_area_admin')->prefix('brands')->name('brands.')->group(function() {
            Route::get('/', [BrandController::class, 'index'])->name('index');
            Route::post('/', [BrandController::class, 'store'])->name('store');
            Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('is_area_admin')->prefix('customers')->name('customers.')->group(function() {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/filter', [CustomerController::class, 'filter'])->name('filter');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::get('/search', [CustomerController::class, 'search'])->name('search');
            Route::get('/{customer}/specifications', [CustomerController::class, 'getSpecifications'])->name('specifications.get');
            Route::post('/{customer}/specifications', [CustomerController::class, 'updateSpecifications'])->name('specifications.update');            
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::get('/export', [CustomerController::class, 'exportCsv'])->name('export');
            Route::post('/import', [CustomerController::class, 'importCsv'])->name('import');
            Route::get('/template', [CustomerController::class, 'downloadTemplate'])->name('template');
            Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    });
    
    Route::middleware('is_area_admin')->prefix('warehouses')->name('warehouses.')->group(function() {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/filter', [WarehouseController::class, 'filter'])->name('filter');
        Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
        Route::get('/export', [WarehouseController::class, 'exportCsv'])->name('export');
        Route::post('/import', [WarehouseController::class, 'importCsv'])->name('import');
        Route::get('/template', [WarehouseController::class, 'downloadTemplate'])->name('template');
        Route::get('/dashboard', [WarehouseController::class, 'dashboard'])->name('dashboard');

    });

    Route::prefix('orders')->name('orders.')->group(function() {
        Route::get('/export-csv', [OrderController::class, 'exportCsv'])->name('export-csv');        
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/filter', [OrderController::class, 'filter'])->name('filter');
        Route::post('/import', [OrderController::class, 'importCsv'])->name('import');
        Route::get('/template', [OrderController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-errors', [OrderController::class, 'downloadImportErrors'])->name('download-errors');
        Route::post('/clear-import-errors', [OrderController::class, 'clearImportErrorsSession'])->name('clear-import-errors');
        Route::post('/bulk-plan', [OrderController::class, 'bulkMoveToPlan'])->name('bulk-plan');


        
        Route::get('/{order}/edit-original', [OrderController::class, 'editOriginalData'])->name('edit-original');
        Route::put('/{order}/edit-original', [OrderController::class, 'updateOriginalData'])->name('update-original');

    
        
        Route::get('/dashboard', [OrderController::class, 'dashboard'])->name('dashboard');
        Route::get('/bulk-edit', [OrderController::class, 'bulkEdit'])->name('bulk-edit');
        Route::post('/bulk-update', [OrderController::class, 'bulkUpdate'])->name('bulk-update');        

        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/plan', [OrderController::class, 'moveToPlan'])->name('plan');

        Route::get('/{order}/logistica-inversa', [ReverseLogisticsController::class, 'create'])->name('reverse-logistics.create');
        Route::post('/{order}/logistica-inversa', [ReverseLogisticsController::class, 'store'])->name('reverse-logistics.store');


        
        Route::post('/{order}/evidence', [OrderController::class, 'uploadEvidence'])->name('evidence.upload');
        Route::delete('/evidence/{evidence}', [OrderController::class, 'deleteEvidence'])->name('evidence.delete');        
    });  
        Route::get('credit-notes/export/csv', [CreditNoteController::class, 'exportCsv'])->name('credit-notes.export.csv');
        Route::get('credit-notes/dashboard', [CreditNoteController::class, 'dashboard'])->name('credit-notes.dashboard');                
        Route::resource('credit-notes', CreditNoteController::class);

    Route::prefix('planning')->name('planning.')->group(function() {
        Route::get('/', [App\Http\Controllers\CustomerService\PlanningController::class, 'index'])->name('index');
        Route::get('/filter', [App\Http\Controllers\CustomerService\PlanningController::class, 'filter'])->name('filter');
        Route::get('/create', [App\Http\Controllers\CustomerService\PlanningController::class, 'create'])->name('create');
        Route::get('/bulk-edit', [App\Http\Controllers\CustomerService\PlanningController::class, 'bulkEdit'])->name('bulk-edit');
        Route::get('/export-csv', [App\Http\Controllers\CustomerService\PlanningController::class, 'exportCsv'])->name('export-csv');
        
        Route::post('/', [App\Http\Controllers\CustomerService\PlanningController::class, 'store'])->name('store');
        Route::post('/bulk-update', [App\Http\Controllers\CustomerService\PlanningController::class, 'bulkUpdate'])->name('bulk-update');

        Route::get('/{planning}', [App\Http\Controllers\CustomerService\PlanningController::class, 'show'])->name('show');
        Route::get('/{planning}/edit', [App\Http\Controllers\CustomerService\PlanningController::class, 'edit'])->name('edit');
        Route::put('/{planning}', [App\Http\Controllers\CustomerService\PlanningController::class, 'update'])->name('update');
        Route::post('/{planning}/schedule', [App\Http\Controllers\CustomerService\PlanningController::class, 'schedule'])->name('schedule');
        Route::post('/{planning}/add-scales', [App\Http\Controllers\CustomerService\PlanningController::class, 'addScales'])->name('add-scales');
        Route::post('/{planning}/mark-as-direct', [App\Http\Controllers\CustomerService\PlanningController::class, 'markAsDirect'])->name('mark-as-direct');
        Route::post('/{planning}/disassociate-from-guia', [App\Http\Controllers\CustomerService\PlanningController::class, 'disassociateFromGuia'])->name('disassociate-from-guia');
        Route::post('/planning/bulk-update-capacity', [\App\Http\Controllers\CustomerService\PlanningController::class, 'bulkUpdateCapacity'])->name('bulk-update-capacity');
        Route::post('/send-email', [\App\Http\Controllers\CustomerService\PlanningController::class, 'sendRouteEmail'])->name('send-email');

    });

    Route::prefix('validation')->name('validation.')->group(function() {
        Route::get('/', [App\Http\Controllers\CustomerService\ValidationController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\CustomerService\ValidationController::class, 'store'])->name('store');
        Route::get('/template', [App\Http\Controllers\CustomerService\ValidationController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [App\Http\Controllers\CustomerService\ValidationController::class, 'importCsv'])->name('importCsv');
    });    

    Route::prefix('audit-reports')->name('audit-reports.')->group(function() {
        Route::get('/', [App\Http\Controllers\CustomerService\AuditReportController::class, 'index'])->name('index');
        
        Route::get('/{guia}', [App\Http\Controllers\CustomerService\AuditReportController::class, 'show'])->name('show');
        
        Route::get('/{guia}/pdf', [App\Http\Controllers\CustomerService\AuditReportController::class, 'generatePdf'])->name('pdf');
    });

});


Route::middleware(['auth', 'verified'])->prefix('audit')->name('audit.')->group(function () {
    
    Route::middleware('check.area:Auditoría')->group(function() {

        Route::get('/', [App\Http\Controllers\AuditController::class, 'index'])->name('index');

        Route::get('/plan-de-carga', [App\Http\Controllers\AuditController::class, 'showCargaPlan'])->name('carga-plan.show');

        Route::get('/warehouse/{audit}', [App\Http\Controllers\AuditController::class, 'showWarehouseAudit'])->name('warehouse.show');
        Route::post('/warehouse/{audit}', [App\Http\Controllers\AuditController::class, 'storeWarehouseAudit'])->name('warehouse.store');

        Route::get('/patio/{audit}', [App\Http\Controllers\AuditController::class, 'showPatioAudit'])->name('patio.show');
        Route::post('/patio/{audit}', [App\Http\Controllers\AuditController::class, 'storePatioAudit'])->name('patio.store');

        Route::get('/loading/{audit}', [App\Http\Controllers\AuditController::class, 'showLoadingAudit'])->name('loading.show');
        Route::post('/loading/{audit}', [App\Http\Controllers\AuditController::class, 'storeLoadingAudit'])->name('loading.store');

        Route::post('/reopen/guia/{guia}', [App\Http\Controllers\AuditController::class, 'reopenAudit'])->name('reopen');

    });
});

Route::middleware(['auth'])->prefix('projects')->name('projects.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProjectController::class, 'index'])->name('index');
    Route::get('/list', [App\Http\Controllers\ProjectController::class, 'list'])->name('list');
    Route::get('/review', [App\Http\Controllers\ProjectController::class, 'review'])->name('review');
    
    Route::resource('/', App\Http\Controllers\ProjectController::class)
         ->parameters(['' => 'project']);

    Route::post('/{project}/tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');

    Route::put('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.status.update');
    Route::delete('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/{project}/comments', [App\Http\Controllers\ProjectCommentController::class, 'store'])->name('comments.store');
    Route::patch('/{project}/status', [App\Http\Controllers\ProjectController::class, 'updateStatus'])->name('update.status');
    Route::post('/{project}/files', [ProjectFileController::class, 'store'])->name('files.store');
    Route::post('/{project}/expenses', [App\Http\Controllers\ProjectExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/{project}/report-pdf', [App\Http\Controllers\ProjectController::class, 'generateReportPdf'])->name('report.pdf');    
    
});

Route::middleware(['auth'])->prefix('wms')->name('wms.')->group(function () {
    Route::get('/dashboard', [WMSDashboardController::class, 'index'])->name('dashboard');    
    Route::get('/api/find-lpn', [WMSInventoryController::class, 'apiFindLpn'])->name('api.find-lpn');
    Route::resource('brands', WMSBrandController::class);
    Route::resource('product-types', WMSProductTypeController::class);
    Route::get('products/export-csv', [WMSProductController::class, 'exportCsv'])->name('products.export-csv');
    Route::get('products-template', [WMSProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('products-import', [WMSProductController::class, 'importCsv'])->name('products.import');
    Route::resource('products', WMSProductController::class);
    // Route::resource('locations', WMSLocationController::class)->except(['show']);
    Route::get('locations/export-csv', [WMSLocationController::class, 'exportCsv'])->name('locations.export-csv');
    Route::resource('locations', WMSLocationController::class);
    Route::post('locations/import', [WMSLocationController::class, 'importCsv'])->name('locations.import');
    Route::post('locations/print-labels', [WMSLocationController::class, 'printLabels'])->name('locations.print-labels');
    Route::get('locations/template', [WMSLocationController::class, 'downloadTemplate'])->name('locations.template');
    Route::get('locations/fetch-filtered-ids', [WMSLocationController::class, 'fetchFilteredIds'])->name('locations.fetch-filtered-ids');
    Route::get('/purchase-orders/export-csv', [WMSPurchaseOrderController::class, 'exportCsv'])->name('purchase-orders.export-csv');
    Route::resource('purchase-orders', WMSPurchaseOrderController::class);
    Route::post('purchase-orders/{purchaseOrder}/receive', [WMSInboundController::class, 'storeReceipt'])->name('inbound.store');
    Route::post('purchase-orders/{purchaseOrder}/register-arrival', [WMSPurchaseOrderController::class, 'registerArrival'])->name('purchase-orders.register-arrival');
    Route::post('purchase-orders/{purchaseOrder}/register-departure', [WMSPurchaseOrderController::class, 'registerDeparture'])->name('purchase-orders.register-departure');
    Route::post('/purchase-orders/{purchaseOrder}/complete', [WMSPurchaseOrderController::class, 'completeReceipt'])->name('purchase-orders.complete');
    Route::post('purchase-orders/{purchaseOrder}/upload-evidence', [WMSPurchaseOrderController::class, 'uploadEvidence'])->name('purchase-orders.upload-evidence');
    Route::get('purchase-orders/{purchaseOrder}/arrival-report-pdf', [WMSPurchaseOrderController::class, 'generateArrivalReportPdf'])->name('purchase-orders.arrival-report-pdf');
    Route::delete('receipt-evidence/{evidence}', [WMSPurchaseOrderController::class, 'destroyEvidence'])->name('purchase-orders.destroy-evidence');    
    Route::get('inventory', [WMSInventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/transfer', [WMSInventoryController::class, 'createTransfer'])->name('inventory.transfer.create');
    Route::post('inventory/transfer', [WMSInventoryController::class, 'storeTransfer'])->name('inventory.transfer.store');
    Route::post('inventory/find-lpn', [WMSInventoryController::class, 'findLpnForTransfer'])->name('inventory.find-lpn');
    Route::get('inventory/split', [WMSInventoryController::class, 'createSplit'])->name('inventory.split.create');
    Route::post('inventory/split', [WMSInventoryController::class, 'storeSplit'])->name('inventory.split.store');
    Route::get('inventory/pallet-info', [WMSInventoryController::class, 'showPalletInfoForm'])->name('inventory.pallet-info.index');
    Route::post('inventory/pallet-info', [WMSInventoryController::class, 'findPalletInfo'])->name('inventory.pallet-info.find');    
    Route::get('inventory/export-csv', [WMSInventoryController::class, 'exportCsv'])->name('inventory.export-csv');
    Route::post('inventory/pallet-items/{palletItem}/adjust', [WMSInventoryController::class, 'adjustItemQuantity'])->name('inventory.item.adjust');
    Route::get('inventory/adjustments', [WMSInventoryController::class, 'showAdjustmentsLog'])->name('inventory.adjustments.log');    
    Route::get('physical-counts/template', [WMSPhysicalCountController::class, 'downloadTemplate'])->name('physical-counts.template');
    Route::resource('physical-counts', WMSPhysicalCountController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('physical-counts/tasks/{task}/perform', [WMSPhysicalCountController::class, 'showCountTask'])->name('physical-counts.tasks.perform');
    Route::post('physical-counts/tasks/{task}/perform', [WMSPhysicalCountController::class, 'recordCount'])->name('physical-counts.tasks.record');    
    Route::post('physical-counts/tasks/{task}/adjust', [WMSPhysicalCountController::class, 'adjustInventory'])->name('physical-counts.tasks.adjust');
    Route::get('physical-counts/tasks/{task}/candidate-lpns', [WMSPhysicalCountController::class, 'getCandidateLpns'])->name('physical-counts.tasks.candidates');

    Route::get('/api/search-stock-products', [App\Http\Controllers\WMS\WMSSalesOrderController::class, 'apiSearchStockProducts'])->name('api.search-stock-products');    

    Route::resource('sales-orders', WMSSalesOrderController::class);
    Route::post('sales-orders/{salesOrder}/cancel', [WMSSalesOrderController::class, 'cancel'])->name('sales-orders.cancel');  
    Route::get('sales-orders-export-csv', [WMSSalesOrderController::class, 'exportCsv'])->name('sales-orders.export-csv');
    Route::get('sales-orders-template', [WMSSalesOrderController::class, 'downloadTemplate'])->name('sales-orders.template');
    Route::post('sales-orders-import-new', [WMSSalesOrderController::class, 'importCsv'])->name('sales-orders.import-new');
    Route::post('sales-orders/{salesOrder}/import-update', [WMSSalesOrderController::class, 'importCsv'])->name('sales-orders.import-update');

    Route::post('sales-orders/{salesOrder}/generate-picklist', [WMSPickingController::class, 'generate'])->name('picking.generate');
    Route::get('picking/{pickList}', [WMSPickingController::class, 'show'])->name('picking.show');
    // Route::post('picking/{pickList}/confirm', [WMSPickingController::class, 'confirm'])->name('picking.confirm');
    Route::get('picking/{pickList}/pdf', [WMSPickingController::class, 'generatePickListPdf'])->name('picking.pdf');
    Route::post('picking/item/{pickListItem}/confirm', [WMSPickingController::class, 'confirmItem'])->name('picking.item.confirm');
    Route::post('picking/{pickList}/complete', [WMSPickingController::class, 'completePicking'])->name('picking.complete');    
    Route::get('reports', [WMSReportController::class, 'index'])->name('reports.index');
    Route::get('reports/inventory', [WMSReportController::class, 'inventoryDashboard'])->name('reports.inventory');
    Route::resource('warehouses', WMSWarehouseController::class);
    Route::get('receiving/{purchaseOrder}', [WMSReceivingController::class, 'showReceivingForm'])->name('receiving.show');
    Route::post('receiving/start-pallet', [WMSReceivingController::class, 'startPallet'])->name('receiving.startPallet');
    Route::post('receiving/pallets/{pallet}/add-item', [WMSReceivingController::class, 'addItemToPallet'])->name('receiving.addItem');
    Route::post('/receiving/pallets/{pallet}/finish', [WMSReceivingController::class, 'finishPallet'])->name('wms.receiving.finishPallet');
    Route::put('/receiving/pallet-items/{palletItem}', [WMSReceivingController::class, 'updatePalletItem'])->name('wms.receiving.updateItem');
    Route::delete('/receiving/pallet-items/{palletItem}', [WMSReceivingController::class, 'destroyPalletItem'])->name('wms.receiving.destroyItem');    
    Route::get('lpns', [WMSLpnController::class, 'index'])->name('lpns.index');
    Route::post('lpns/generate', [WMSLpnController::class, 'generate'])->name('lpns.generate');
    Route::get('lpns/print', [WMSLpnController::class, 'printPdf'])->name('lpns.print');
    Route::resource('qualities', WMSQualityController::class);
    Route::post('lpns/reprint', [WMSLpnController::class, 'reprintPdf'])->name('lpns.reprint');
    Route::post('lpns/print-from-csv', [WMSLpnController::class, 'printFromCsv'])->name('lpns.print-from-csv');
    Route::get('lpns/template', [WMSLpnController::class, 'downloadTemplate'])->name('lpns.template');
    Route::get('reports/stock-movements', [WMSReportController::class, 'showStockMovements'])->name('reports.stock-movements');
    Route::get('reports/stock-movements/export', [WMSReportController::class, 'exportStockMovements'])->name('reports.stock-movements.export');
    Route::get('reports/inventory-aging', [WMSReportController::class, 'showAgingReport'])->name('reports.inventory-aging');
    Route::get('reports/inventory-aging/export', [WMSReportController::class, 'exportAgingReport'])->name('reports.inventory-aging.export');
    Route::get('reports/non-available-inventory', [WMSReportController::class, 'showNonAvailableReport'])->name('reports.non-available-inventory');
    Route::get('reports/non-available-inventory/export', [WMSReportController::class, 'exportNonAvailableReport'])->name('reports.non-available-inventory.export');
    Route::get('reports/abc-analysis', [WMSReportController::class, 'showAbcAnalysis'])->name('reports.abc-analysis');
    Route::get('reports/abc-analysis/export', [WMSReportController::class, 'exportAbcAnalysis'])->name('reports.abc-analysis.export');  
    Route::get('reports/slotting-heatmap', [WMSReportController::class, 'showSlottingHeatmap'])->name('reports.slotting-heatmap');
    Route::get('/api/search-products', [WMSProductController::class, 'apiSearchProducts'])->name('api.search-products');

});

Route::middleware(['auth', 'ff.access'])->prefix('ff')->name('ff.')->group(function () {
    
    Route::get('/dashboard', [FfDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/catalog/download-template', [FfProductController::class, 'downloadTemplate'])->name('catalog.downloadTemplate');
    Route::resource('catalog', FfProductController::class);
    Route::post('/catalog/import', [FfProductController::class, 'import'])->name('catalog.import');
    Route::get('/inventory', [FfInventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/move', [FfInventoryController::class, 'storeMovement'])->name('inventory.storeMovement');
    Route::post('/inventory/import', [FfInventoryController::class, 'importMovements'])->name('inventory.import');
    Route::get('/inventory/movements-template', [FfInventoryController::class, 'downloadMovementTemplate'])->name('inventory.movementsTemplate');    
    Route::get('/inventory/log', [FfInventoryController::class, 'logIndex'])->name('inventory.log');
    Route::get('/inventory/export-csv', [FfInventoryController::class, 'exportCsv'])->name('inventory.exportCsv');
    Route::get('/inventory/log/export-csv', [FfInventoryController::class, 'exportLogCsv'])->name('inventory.log.exportCsv');
    Route::get('/sales', [FfSalesController::class, 'index'])->name('sales.index');
    Route::post('/sales/cart/update', [FfSalesController::class, 'updateCartItem'])->name('sales.cart.update');
    Route::get('/sales/reservations', [FfSalesController::class, 'getReservations'])->name('sales.reservations');
    Route::post('/sales/checkout', [FfSalesController::class, 'checkout'])->name('sales.checkout');    
    Route::post('/friends-and-family/sales/print-list', [FfSalesController::class, 'printList'])->name('sales.printList');
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [FfReportController::class, 'index'])->name('index');
        Route::get('/transactions', [FfReportController::class, 'transactions'])->name('transactions');
        Route::post('/transactions/{movement}/reprint', [FfReportController::class, 'reprintReceipt'])->name('reprintReceipt');
        Route::get('/inventory-analysis', [FfReportController::class, 'inventoryAnalysis'])->name('inventoryAnalysis');
        Route::get('/stock-availability', [FfReportController::class, 'stockAvailability'])->name('stockAvailability');
        Route::get('/catalog-analysis', [FfReportController::class, 'catalogAnalysis'])->name('catalogAnalysis');
        Route::get('/seller-performance', [FfReportController::class, 'sellerPerformance'])->name('sellerPerformance');
        Route::get('/api/recent-movements', [FfReportController::class, 'apiGetRecentMovements'])->name('api.recentMovements');
        Route::get('/api/sale-details/{folio}', [FfReportController::class, 'apiGetSaleDetails'])->name('api.saleDetails');
    });    
});

Route::get('/project-files/{file}/download', [ProjectFileController::class, 'download'])
     ->middleware('auth')
     ->name('projects.files.download');

Route::middleware('auth')->group(function () {
    Route::get('/api/email-recipients', [App\Http\Controllers\Api\SearchController::class, 'getEmailRecipients'])->name('api.email-recipients');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/assistant/chat', [AssistantController::class, 'chat'])->name('assistant.chat');
    Route::post('/assistant/reset', [App\Http\Controllers\AssistantController::class, 'resetChat'])->name('assistant.reset');



});



require __DIR__.'/auth.php';