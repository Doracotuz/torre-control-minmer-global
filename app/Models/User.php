<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Notifications\CustomResetPasswordNotification;
use App\Models\ffInventoryMovement;
use App\Models\ffCartItem;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'position',
        'phone_number',        
        'email',
        'password',
        'profile_photo_path',
        'area_id',
        'is_active',
        'ff_role_name',
        'ff_granular_permissions',
        'role_id',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_area_admin' => 'boolean',
            'is_client' => 'boolean',
            'is_active' => 'boolean',
            'visible_modules' => 'array',
            'ff_visible_tiles' => 'array',
            'ff_granular_permissions' => 'array',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function fileLinks(): HasMany
    {
        return $this->hasMany(FileLink::class);
    }

    public function accessibleFolders(): BelongsToMany
    {
        return $this->belongsToMany(Folder::class, 'folder_user');
    }

    public function isClient(): bool
    {
        return $this->is_client;
    }

    /**
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_area_admin && $this->area?->name === 'Administración';
    }

    public function organigramMember()
    {
        return $this->hasOne(OrganigramMember::class);
    }

    public function accessibleAreas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'user_accessible_areas');
    }

    public function cartItems()
    {
        return $this->hasMany(ffCartItem::class);
    }
    
    public function movements(): HasMany
    {
        return $this->hasMany(ffInventoryMovement::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public static function availableModules(): array
    {
        return [
            'dashboard' => 'Dashboard General',
            'files' => 'Gestión de Archivos / Carpetas',
            'orders' => 'Pedidos',
            'client_dashboard' => 'Dashboard Cliente (Tablero)',
            'organigram' => 'Organigrama',
            'tracking' => 'Tracking',
            'rfq' => 'RFQ (Moët Hennessy)',
            'carbon' => 'Huella de Carbono',
            'certifications' => 'Certificaciones',
            'assistance' => 'Asistencia',
            'visits' => 'Gestión de Visitas',
            'routes' => 'Gestión de Rutas',
            'tickets' => 'Tickets de Soporte',
            'projects' => 'Proyectos',
            'wms' => 'WMS',
            'customer_service' => 'Customer Service',
            'area_admin' => 'Panel Admin de Área',
            'electronic_label' => 'Marbete Electrónico',
        ];
    }
    
    public function hasModuleAccess(string $moduleKey): bool
    {
        if (is_null($this->visible_modules)) {
            return false; 
        }
        
        return in_array($moduleKey, $this->visible_modules);
    }

    public static function availableFfTiles(): array
    {
        return [
            'orders'      => 'Pedidos',
            'inventory'   => 'Inventario',
            'catalog'     => 'Catálogo',
            'reports'     => 'Reportes',
            'admin'       => 'Administración',
        ];
    }

    public function canSeeFfTile(string $tileKey): bool
    {
        $permissionMap = [
            'orders'    => 'orders.view',
            'inventory' => 'inventory.view',
            'catalog'   => 'catalog.view',
            'reports'   => 'reports.view',
            'admin'     => 'admin.view',
        ];

        if (array_key_exists($tileKey, $permissionMap)) {
            return $this->hasFfPermission($permissionMap[$tileKey]);
        }

        return false;
    }

    public static function availableFfPermissions(): array
    {
        $grouped = self::getGroupedPermissions();
        $flat = [];
        foreach ($grouped as $module => $subgroups) {
            foreach ($subgroups as $groupName => $perms) {
                $flat["$module: $groupName"] = $perms;
            }
        }
        return $flat;
    }

    public static function getGroupedPermissions(): array
    {
        return [
            'Friends & Family' => [
                'Catálogo' => [
                    'catalog.view' => 'Ver Catálogo',
                    'catalog.create' => 'Crear Nuevo Producto',
                    'catalog.edit' => 'Editar Producto',
                    'catalog.delete' => 'Eliminar Producto',
                    'catalog.import' => 'Importar Productos',
                    'catalog.export' => 'Exportar Catálogo & Inventario',
                    'catalog.technical_sheet' => 'Generar Ficha Técnica (PDF)',
                ],
                'Inventario' => [
                    'inventory.view' => 'Ver Inventario',
                    'inventory.move' => 'Realizar Movimientos (Entrada/Salida)',
                    'inventory.import' => 'Importar Movimientos',
                    'inventory.log' => 'Ver Bitácora de Movimientos',
                    'inventory.backorders.operational' => 'Gestionar Backorders (Surtido/Operativo)',
                    'inventory.backorders.financial' => 'Ver Reporte de Pasivos (Financiero)',
                ],
                'Ventas' => [
                    'sales.view' => 'Ver Módulo de Ventas',
                    'sales.checkout' => 'Realizar Venta / Checkout',
                    'sales.reservations' => 'Ver Reservas',
                    'sales.cancel' => 'Cancelar Pedidos',
                    'sales.import' => 'Importar Pedidos',
                    'sales.loans' => 'Gestionar Préstamos y Devoluciones',
                ],
                'Pedidos' => [
                    'orders.view' => 'Ver Pedidos',
                    'orders.details' => 'Ver Detalle de Pedido',
                    'orders.evidence' => 'Cargar/Ver Evidencias',
                    'orders.report' => 'Descargar Reporte de Pedido',
                ],
                'Reportes' => [
                    'reports.view' => 'Ver Módulo de Reportes',
                    'reports.transactions' => 'Reporte de Transacciones',
                    'reports.inventory_analysis' => 'Análisis de Inventario',
                    'reports.stock_availability' => 'Disponibilidad de Stock',
                    'reports.catalog_analysis' => 'Análisis de Catálogo',
                    'reports.seller_performance' => 'Performance de Vendedores',
                    'reports.client_analysis' => 'Análisis de Clientes',
                ],
                'Administración' => [
                    'admin.view' => 'Ver Panel de Administración',
                    'admin.clients' => 'Gestionar Clientes',
                    'admin.branches' => 'Gestionar Sucursales',
                    'admin.channels' => 'Gestionar Canales de Venta',
                    'admin.warehouses' => 'Gestionar Almacenes',
                    'admin.qualities' => 'Gestionar Calidades',
                ],
            ],
            'WMS' => [
                'Dashboard & BI' => [
                    'wms.dashboard' => 'Visualizar Dashboard Principal',
                    'wms.reports' => 'Acceso a Reportes Inteligentes (BI)',
                ],
                'Operaciones de Entrada (Inbound)' => [
                    'wms.purchase_orders.view' => 'Ver Órdenes de Compra',
                    'wms.purchase_orders.create' => 'Crear Órdenes de Compra',
                    'wms.purchase_orders.edit' => 'Editar Órdenes de Compra',
                    'wms.purchase_orders.delete' => 'Eliminar Órdenes de Compra',
                    'wms.receiving' => 'Ejecutar Recepción (Descarga, Validación, Cierre)',
                    'wms.quality' => 'Control de Calidad (Estados y Bloqueos)',
                ],
                'Gestión de Inventario' => [
                    'wms.inventory' => 'Consultar Inventario (Matrix, Ubicaciones)',
                    'wms.inventory_move' => 'Movimientos Internos (Transferencias, Splits)',
                    'wms.inventory_adjust' => 'Ajustes de Inventario (Conteos, Mermas)',
                    'wms.lpns' => 'Gestión de LPNs (Etiquetas de Pallet)',
                    'wms.physical_counts' => 'Conteos Cíclicos y Auditorías',
                ],
                'Operaciones de Salida (Outbound)' => [
                    'wms.sales_orders.view' => 'Ver Órdenes de Venta',
                    'wms.sales_orders.create' => 'Crear Órdenes de Venta',
                    'wms.sales_orders.edit' => 'Editar Órdenes de Venta',
                    'wms.sales_orders.delete' => 'Eliminar Órdenes de Venta',
                    'wms.picking' => 'Ejecutar Picking (Generación de Listas, Surtido)',
                    'wms.dispatch' => 'Despacho y Embarques',
                ],
                'Maestros y Configuración' => [
                    'wms.products.view' => 'Ver Productos WMS',
                    'wms.products.create' => 'Crear Productos WMS',
                    'wms.products.edit' => 'Editar Productos WMS',
                    'wms.products.delete' => 'Eliminar Productos WMS',
                    'wms.brands' => 'Gestionar Marcas',
                    'wms.product_types' => 'Gestionar Tipos de Producto',
                    'wms.warehouses' => 'Configuración de Almacenes',
                    'wms.locations.view' => 'Ver Ubicaciones',
                    'wms.locations.manage' => 'Gestionar Ubicaciones (Crear/Editar/Eliminar)',
                    'wms.locations.print' => 'Imprimir Etiquetas de Ubicación',
                ],
            ],
        ];
    }

    public function hasFfPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_null($this->ff_granular_permissions)) {
            return false;
        }

        return in_array($permission, $this->ff_granular_permissions);
    }
}