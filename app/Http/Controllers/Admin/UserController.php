<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area; // Para poder seleccionar áreas en los formularios
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'is_area_admin' => 'boolean', // Nueva validación
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_id' => $request->area_id,
            'is_area_admin' => $request->has('is_area_admin'), // Guarda el valor del checkbox
        ]);

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
            'is_area_admin' => 'boolean', // Nueva validación
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->area_id = $request->area_id;
        $user->is_area_admin = $request->has('is_area_admin'); // Actualiza el valor del checkbox

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

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
        // Opcional: Considerar la lógica para reasignar carpetas/archivos si el usuario es eliminado
        // Actualmente, las carpetas/archivos creados por este usuario se eliminarán si la clave foránea es cascade.
        // Si area_id en users es nullable, no hay problema si el área se elimina antes.

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}