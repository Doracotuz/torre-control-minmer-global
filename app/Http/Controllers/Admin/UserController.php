<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Asegúrate de que User esté importado
use App\Models\Area; // Asegúrate de que Area esté importado
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // ¡Importa la fachada Storage!

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     * Muestra una lista de todos los usuarios.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::with('area')->orderBy('name')->get(); // Carga la relación 'area'
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $areas = Area::orderBy('name')->get(); // Obtiene todas las áreas para el select
        return view('admin.users.create', compact('areas'));
    }

    /**
     * Store a newly created user in storage.
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'area_id' => 'required|exists:areas,id',
            'is_area_admin' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación para la foto
        ]);

        $data = $request->all(); // Obtiene todos los datos validados
        $data['password'] = Hash::make($request->password);
        $data['is_area_admin'] = $request->has('is_area_admin'); // Guarda el valor del checkbox (true/false)

        // Manejo de la subida de la foto de perfil
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile_photos', 'public');
        } else {
            $data['profile_photo_path'] = null; // Asegura que si no se sube, el campo sea null
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified user.
     * Muestra el formulario para editar el usuario especificado.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $areas = Area::orderBy('name')->get(); // Obtiene todas las áreas para el select
        return view('admin.users.edit', compact('user', 'areas'));
    }

    /**
     * Update the specified user in storage.
     * Actualiza el usuario especificado en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'area_id' => 'required|exists:areas,id',
            'is_area_admin' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación para la foto
        ]);

        $data = $request->except(['_token', '_method', 'password_confirmation']); // Obtiene todos los datos excepto estos

        $data['is_area_admin'] = $request->has('is_area_admin');

        // Actualiza la contraseña si se proporcionó una nueva
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']); // No actualizar la contraseña si está vacía
        }

        // Manejo de la foto de perfil
        if ($request->hasFile('profile_photo')) {
            // Eliminar la foto antigua si existe
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile_photos', 'public');
        } elseif ($request->input('remove_profile_photo')) { // Si se marcó la casilla para eliminar la foto
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $data['profile_photo_path'] = null;
        } else {
            // Si no se subió nueva foto y no se pidió eliminar, mantener la existente
            $data['profile_photo_path'] = $user->profile_photo_path;
        }


        $user->update($data); // Usar $data para actualizar

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage.
     * Elimina el usuario especificado de la base de datos.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Eliminar la foto de perfil si existe
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}