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
        'is_area_admin',
        'is_client',
        'is_active',
        'visible_modules',
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
        ];
    }
    
    public function hasModuleAccess(string $moduleKey): bool
    {
        if (is_null($this->visible_modules)) {
            return false; 
        }
        
        return in_array($moduleKey, $this->visible_modules);
    }    

}