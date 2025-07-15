<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Importar Storage

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
            'profile_photo' => 'nullable|image|max:2048', // Añadida validación para la foto
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_id' => $areaId, // Asigna automáticamente al área del admin
            'is_area_admin' => false, // Por defecto, los nuevos usuarios no son administradores de área
        ];

        // Manejo de la subida de la foto de perfil
        if ($request->hasFile('profile_photo')) { //
            $path = $request->file('profile_photo')->store('profile-photos', 'public'); //
            $userData['profile_photo_path'] = $path; //
        }

        User::create($userData); //

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

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048', // Añadida validación para la foto
        ];

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Manejo de la eliminación de la foto
        if ($request->has('remove_profile_photo') && $request->boolean('remove_profile_photo')) { //
            if ($user->profile_photo_path) { //
                Storage::disk('public')->delete($user->profile_photo_path); //
            }
            $user->profile_photo_path = null; //
        }
        // Manejo de la subida de una nueva foto
        elseif ($request->hasFile('profile_photo')) { //
            // Eliminar la foto antigua si existe
            if ($user->profile_photo_path) { //
                Storage::disk('public')->delete($user->profile_photo_path); //
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public'); //
            $user->profile_photo_path = $path; //
        }

        $user->save(); //

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
        if ($user->profile_photo_path) { //
            Storage::disk('public')->delete($user->profile_photo_path); //
        }

        $user->delete(); //

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}