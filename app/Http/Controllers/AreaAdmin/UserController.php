<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area; // <-- Importar Area
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\OrganigramPosition;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeNewUser;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Helper para obtener el área de gestión activa y verificar el permiso.
     * Esta función privada se usa en todos los métodos para saber qué área gestionar.
     */
    private function getActiveArea()
    {
        $user = Auth::user();
        
        // 1. Obtiene el ID del área de la sesión, o usa la principal del usuario como defecto
        $activeAreaId = session('current_admin_area_id', $user->area_id);
        $activeArea = Area::find($activeAreaId);

        // 2. Si el área de la sesión no se encuentra (rara vez), vuelve a la principal por seguridad
        if (!$activeArea) {
            $activeArea = $user->area;
            $activeAreaId = $user->area_id;
            session(['current_admin_area_id' => $activeAreaId, 'current_admin_area_name' => $activeArea->name]);
        }

        // 3. Verifica que el admin tenga permiso para gestionar esta área (sea su principal o una secundaria)
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();
        if (!$user->is_area_admin || !$manageableAreaIds->contains($activeAreaId)) {
            abort(403, 'No tienes permiso para gestionar esta área.');
        }
        
        return $activeArea;
    }

    /**
     * Muestra una lista de los usuarios dentro del área activa.
     */
    public function index(Request $request)
    {
        // Obtiene el área seleccionada actualmente desde la sesión
        $currentArea = $this->getActiveArea();
        $areaId = $currentArea->id;
        
        // La consulta ahora usa el $areaId de la sesión
        $query = User::where('area_id', $areaId)->orderBy('name');

        // Filtro de búsqueda por texto
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('position', 'like', $searchTerm)
                  ->orWhere('phone_number', 'like', $searchTerm);
            });
        }

        // Filtro por Rol (dentro del área)
        if ($request->filled('role')) {
            if ($request->role == 'admin') {
                $query->where('is_area_admin', true);
            } elseif ($request->role == 'normal') {
                $query->where('is_area_admin', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();

        return view('area_admin.users.index', [
            'users' => $users,
            'currentArea' => $currentArea, // Pasa el área activa a la vista
            'filters' => $request->only(['search', 'role'])
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario dentro del área activa.
     */
    public function create()
    {
        $currentArea = $this->getActiveArea(); // Obtiene el área activa
        $positions = OrganigramPosition::orderBy('name')->get();

        return view('area_admin.users.create', compact('currentArea', 'positions'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos para el área activa.
     */
    public function store(Request $request)
    {
        $currentArea = $this->getActiveArea(); // Obtiene el área activa
        
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|digits:10',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $userData = [
            'name' => $request->name,
            'position' => $request->position,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_id' => $currentArea->id, // Asigna el usuario al área activa
            'is_area_admin' => $request->boolean('is_area_admin'),
            'is_client' => false, // Los Admins de Área no pueden crear clientes
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 's3');
            $userData['profile_photo_path'] = $path;
        }

        $user = User::create($userData);

        // Enviar correo de bienvenida
        try {
            Mail::to($user->email)->send(new WelcomeNewUser($user, $request->password));
        } catch (\Exception $e) {
            Log::error("Error al enviar correo de bienvenida desde AreaAdmin a {$user->email}: " . $e->getMessage());
        }

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario creado y notificado exitosamente.');
    }

    /**
     * Muestra el formulario para editar el usuario especificado.
     */
    public function edit(User $user)
    {
        $currentArea = $this->getActiveArea(); // Obtiene el área activa

        // Verifica que el usuario que se edita pertenezca al área que se está gestionando
        if ($currentArea->id !== $user->area_id) {
            abort(403, 'Este usuario no pertenece al área que estás gestionando actualmente.');
        }

        $positions = OrganigramPosition::orderBy('name')->get();

        return view('area_admin.users.edit', compact('user', 'currentArea', 'positions'));
    }

    /**
     * Actualiza el usuario especificado en la base de datos.
     */
    public function update(Request $request, User $user)
    {
        $currentArea = $this->getActiveArea(); // Obtiene el área activa

        // Verifica que el usuario que se actualiza pertenezca al área que se está gestionando
        if ($currentArea->id !== $user->area_id) {
            abort(403, 'Este usuario no pertenece al área que estás gestionando actualmente.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|digits:10',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $user->name = $request->name;
        $user->position = $request->position;
        $user->phone_number = $request->phone_number;
        $user->email = $request->email;
        $user->is_area_admin = $request->boolean('is_area_admin');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Manejo de la eliminación de la foto
        if ($request->has('remove_profile_photo') && $request->boolean('remove_profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }
        // Manejo de la subida de una nueva foto
        elseif ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 's3');
            $user->profile_photo_path = $path;
        }

        $user->save();

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina el usuario especificado de la base de datos.
     */
    public function destroy(User $user)
    {
        $currentArea = $this->getActiveArea(); // Obtiene el área activa
        
        // Asegurarse de que el usuario a eliminar pertenece al área activa
        if ($currentArea->id !== $user->area_id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No tienes permiso para eliminar este usuario.');
        }

        // Impedir que un administrador de área se elimine a sí mismo
        if (Auth::id() === $user->id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No puedes eliminar tu propia cuenta desde aquí.');
        }

        // Eliminar la foto de perfil del almacenamiento si existe
        if ($user->profile_photo_path) {
            Storage::disk('s3')->delete($user->profile_photo_path);
        }

        $user->delete();

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}