<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
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
     * Display a listing of the users within the area admin's area.
     * Muestra una lista de los usuarios dentro del área del administrador de área.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $areaId = auth()->user()->area_id;
        $currentArea = auth()->user()->area;
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
            'currentArea' => $currentArea,
            'filters' => $request->only(['search', 'role'])
        ]);
    }

    /**
     * Show the form for creating a new user within the area admin's area.
     * Muestra el formulario para crear un nuevo usuario dentro del área del administrador.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $currentArea = auth()->user()->area;
        $positions = OrganigramPosition::orderBy('name')->get();

        return view('area_admin.users.create', compact('currentArea', 'positions'));
    }

    /**
     * Store a newly created user in storage for the area admin's area.
     * Almacena un nuevo usuario en la base de datos para el área del administrador.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
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
            'area_id' => auth()->user()->area_id,
            'is_area_admin' => $request->boolean('is_area_admin'), // CORREGIDO
            'is_client' => false,
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
     * Show the form for editing the specified user within the area admin's area.
     * Muestra el formulario para editar el usuario especificado dentro del área del administrador.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(User $user)
    {
        if (auth()->user()->area_id !== $user->area_id) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $currentArea = auth()->user()->area;
        $positions = OrganigramPosition::orderBy('name')->get();

        return view('area_admin.users.edit', compact('user', 'currentArea', 'positions'));
    }

    /**
     * Update the specified user in storage for the area admin's area.
     * Actualiza el usuario especificado en la base de datos para el área del administrador.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        if (auth()->user()->area_id !== $user->area_id) {
            abort(403, 'No tienes permiso para actualizar este usuario.');
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
        $user->is_area_admin = $request->boolean('is_area_admin'); // CORREGIDO

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Manejo de la eliminación de la foto
        if ($request->has('remove_profile_photo') && $request->boolean('remove_profile_photo')) {
            if ($user->profile_photo_path) {
                // CAMBIO: Eliminar de S3
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }
        // Manejo de la subida de una nueva foto
        elseif ($request->hasFile('profile_photo')) {
            // Eliminar la foto antigua si existe
            if ($user->profile_photo_path) {
                // CAMBIO: Eliminar de S3
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            // CAMBIO: Almacenar en S3
            $path = $request->file('profile_photo')->store('profile-photos', 's3');
            $user->profile_photo_path = $path;
        }

        $user->save();

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage within the area admin's area.
     * Elimina el usuario especificado de la base de datos dentro del área del administrador.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Asegurarse de que el usuario a eliminar pertenece al área del administrador
        if (Auth::user()->area_id !== $user->area_id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No tienes permiso para eliminar este usuario.');
        }

        // Opcional: Impedir que un administrador de área se elimine a sí mismo
        if (Auth::id() === $user->id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No puedes eliminar tu propia cuenta desde aquí.');
        }

        // Eliminar la foto de perfil del almacenamiento si existe
        if ($user->profile_photo_path) {
            // CAMBIO: Eliminar de S3
            Storage::disk('s3')->delete($user->profile_photo_path);
        }

        $user->delete();

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}