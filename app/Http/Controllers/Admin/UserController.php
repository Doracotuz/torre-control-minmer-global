<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $users = User::with('area')->orderBy('name')->get();
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
        $areas = Area::orderBy('name')->get();
        // Para la selección de carpetas, podrías cargar solo las carpetas raíz o todas,
        // dependiendo de si quieres que el Super Admin solo asigne raíces o también subcarpetas directamente.
        // `getFoldersForClientAccess` ya devuelve las carpetas raíz cuando parent_id es null
        // No necesitamos `$folders` aquí, ya que Alpine.js cargará dinámicamente.
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
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_area_admin' => 'boolean',
            'is_client' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        // Reglas condicionales para area_id
        if (!$request->has('is_client') || !$request->input('is_client')) { // Si NO es cliente
            $rules['area_id'] = 'required|exists:areas,id';
            // Asegúrate de que accessible_folder_ids no se valide si no es cliente
            $request->request->set('accessible_folder_ids', []); // Forzar a un array vacío para que la validación 'nullable|array' pase.
        } else { // Si SÍ es cliente
            $rules['area_id'] = 'nullable|exists:areas,id';
            $rules['accessible_folder_ids'] = 'nullable|array';
            $rules['accessible_folder_ids.*'] = 'exists:folders,id';
        }

        $request->validate($rules); //

        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        $data['is_area_admin'] = $request->has('is_area_admin');
        $data['is_client'] = $request->has('is_client'); //

        // Si es cliente y no se seleccionó área, asegúrate de que sea null
        if ($data['is_client'] && !$request->filled('area_id')) {
            $data['area_id'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            // CAMBIO: Almacenar en S3
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile_photos', 's3');
        } else {
            $data['profile_photo_path'] = null;
        }

        $user = User::create($data); //

        if ($user->isClient()) {
            // El hidden input aún envía una cadena. Por eso se mantiene explode.
            // Pero como la validación ya garantiza que es válido si existe, aquí solo se procesa.
            $folderIds = explode(',', $request->input('accessible_folder_ids')[0] ?? '');
            $folderIds = array_filter(array_map('intval', $folderIds));

            $user->accessibleFolders()->sync($folderIds); //
        } else {
            // Asegurarse de que si se desmarca "is_client", se desvinculen las carpetas
            $user->accessibleFolders()->detach(); //
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.'); //
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
        $areas = Area::orderBy('name')->get();
        // Las carpetas no se pasan aquí, ya que se cargarán dinámicamente con la API
        $accessibleFolderIds = $user->accessibleFolders->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'areas', 'accessibleFolderIds'));
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
            'is_area_admin' => 'boolean',
            'is_client' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        // Reglas condicionales para area_id y accessible_folder_ids
        if (!$request->has('is_client') || !$request->input('is_client')) { // Si NO es cliente
            $rules['area_id'] = 'required|exists:areas,id';
            // Asegúrate de que accessible_folder_ids no se valide si no es cliente
            $request->request->set('accessible_folder_ids', []); // Forzar a un array vacío para que la validación 'nullable|array' pase.
        } else { // Si SÍ es cliente
            $rules['area_id'] = 'nullable|exists:areas,id';
            $rules['accessible_folder_ids'] = 'nullable|array';
            $rules['accessible_folder_ids.*'] = 'exists:folders,id';
        }

        $request->validate($rules); //

        $data = $request->except(['_token', '_method', 'password_confirmation']); //

        $data['is_area_admin'] = $request->has('is_area_admin'); //
        $data['is_client'] = $request->has('is_client'); //

        // Si es cliente y no se seleccionó área, asegúrate de que sea null
        if ($data['is_client'] && !$request->filled('area_id')) {
            $data['area_id'] = null;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('s3')->exists($user->profile_photo_path)) { // CAMBIO: Usar S3
                Storage::disk('s3')->delete($user->profile_photo_path); // CAMBIO: Usar S3
            }
            // CAMBIO: Almacenar en S3
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile_photos', 's3');
        } elseif ($request->input('remove_profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('s3')->exists($user->profile_photo_path)) { // CAMBIO: Usar S3
                Storage::disk('s3')->delete($user->profile_photo_path); // CAMBIO: Usar S3
            }
            $data['profile_photo_path'] = null;
        } else {
            $data['profile_photo_path'] = $user->profile_photo_path;
        }

        $user->update($data); //

        if ($user->isClient()) {
            $folderIds = explode(',', $request->input('accessible_folder_ids')[0] ?? '');
            $folderIds = array_filter(array_map('intval', $folderIds));

            $user->accessibleFolders()->sync($folderIds); //
        } else {
            // Si el usuario ya no es cliente, desvincula todas las carpetas.
            $user->accessibleFolders()->detach(); //
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente.'); //
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
        if ($user->profile_photo_path && Storage::disk('s3')->exists($user->profile_photo_path)) { // CAMBIO: Usar S3
            Storage::disk('s3')->delete($user->profile_photo_path);
        }

        $user->accessibleFolders()->detach();

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}