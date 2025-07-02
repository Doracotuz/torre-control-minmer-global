<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users within the area admin's area.
     * Muestra una lista de los usuarios dentro del área del administrador de área.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $areaId = Auth::user()->area_id;
        $users = User::where('area_id', $areaId)->orderBy('name')->get();
        $currentArea = Auth::user()->area; // Para mostrar el nombre del área en la vista

        return view('area_admin.users.index', compact('users', 'currentArea'));
    }

    /**
     * Show the form for creating a new user within the area admin's area.
     * Muestra el formulario para crear un nuevo usuario dentro del área del administrador.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $currentArea = Auth::user()->area; // El área del administrador

        return view('area_admin.users.create', compact('currentArea'));
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
        $areaId = Auth::user()->area_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            // is_area_admin no se permite modificar aquí, solo el super admin lo hace
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_id' => $areaId, // Asigna automáticamente al área del admin
            'is_area_admin' => false, // Por defecto, los nuevos usuarios no son administradores de área
        ]);

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario creado exitosamente en tu área.');
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
        // Asegurarse de que el usuario a editar pertenece al área del administrador
        if (Auth::user()->area_id !== $user->area_id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No tienes permiso para editar este usuario.');
        }

        $currentArea = Auth::user()->area; // El área del administrador
        return view('area_admin.users.edit', compact('user', 'currentArea'));
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
        // Asegurarse de que el usuario a actualizar pertenece al área del administrador
        if (Auth::user()->area_id !== $user->area_id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No tienes permiso para actualizar este usuario.');
        }

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
            // is_area_admin no se permite modificar aquí
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        // area_id no se permite cambiar aquí, siempre es el área del admin
        // is_area_admin no se permite cambiar aquí

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
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

        $user->delete();

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
