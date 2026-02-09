<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Area;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\OrganigramPosition;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeNewUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::orderBy('name')->get();
        $query = User::with('area')->orderBy('name');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->orWhere('position', 'like', $searchTerm)
                ->orWhere('phone_number', 'like', $searchTerm);
            });
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('role')) {
            switch ($request->role) {
                case 'admin':
                    $query->where('is_area_admin', true);
                    break;
                case 'client':
                    $query->where('is_client', true);
                    break;
                case 'normal':
                    $query->where('is_area_admin', false)->where('is_client', false);
                    break;
            }
        }

        $users = $query->paginate(15)->withQueryString();

        $allUsers = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'areas' => $areas,
            'filters' => $request->only(['search', 'area_id', 'role']),
            'allUsers' => $allUsers,
        ]);
    }

    public function create()
    {
        $areas = Area::orderBy('name')->get();
        $positions = OrganigramPosition::orderBy('name')->get();
        $availableModules = User::availableModules();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('areas', 'positions', 'availableModules', 'roles'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|digits:10',            
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_area_admin' => 'boolean',
            'is_client' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'visible_modules' => 'nullable|array',
            'visible_modules.*' => 'string',
            'ff_visible_tiles' => 'nullable|array',
            'ff_visible_tiles.*' => 'string',
            'ff_role_name' => 'nullable|string|max:255',
            'ff_granular_permissions' => 'nullable|array',
            'role_id' => 'nullable|exists:roles,id',            
        ];

        if (!$request->has('is_client') || !$request->input('is_client')) {
            $rules['area_id'] = 'required|exists:areas,id';
            $request->request->set('accessible_folder_ids', []);
        } else {
            $rules['area_id'] = 'nullable|exists:areas,id';
            $rules['accessible_folder_ids'] = 'nullable|array';
            $rules['accessible_folder_ids.*'] = 'exists:folders,id';
        }

        $request->validate($rules);

        $user = new User();
        
        $user->fill($request->only([
            'name', 
            'email', 
            'position', 
            'phone_number'
        ]));

        if ($request->has('is_client') && $request->input('is_client') && !$request->filled('area_id')) {
            $user->area_id = null;
        } else {
            $user->area_id = $request->area_id;
        }

        $user->password = Hash::make($request->password);
        $user->is_area_admin = $request->has('is_area_admin');
        $user->is_client = $request->has('is_client');
        $user->visible_modules = $request->input('visible_modules', []);
        $user->ff_visible_tiles = $request->input('ff_visible_tiles', []);
        $user->ff_role_name = $request->input('ff_role_name');
        $user->ff_granular_permissions = $request->input('ff_granular_permissions', []);
        $user->role_id = $request->input('role_id');

        if ($request->hasFile('profile_photo')) {
            $user->profile_photo_path = $request->file('profile_photo')->store('profile_photos', 's3');
        } else {
            $user->profile_photo_path = null;
        }

        $user->save();

        try {
            Mail::to($user->email)->send(new WelcomeNewUser($user, $request->password));
        } catch (\Exception $e) {
            Log::error("Error al enviar correo de bienvenida a {$user->email}: " . $e->getMessage());
        }        

        if ($user->isClient()) {
            $folderIds = explode(',', $request->input('accessible_folder_ids')[0] ?? '');
            $folderIds = array_filter(array_map('intval', $folderIds));

            $user->accessibleFolders()->sync($folderIds);
            $user->accessibleAreas()->detach(); 
        } else {
            $areaIds = $request->input('accessible_area_ids', []);
            $user->accessibleAreas()->sync($areaIds);
            $user->accessibleFolders()->detach();
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        $areas = Area::orderBy('name')->get();
        $positions = OrganigramPosition::orderBy('name')->get();
        $accessibleFolderIds = $user->accessibleFolders->pluck('id')->toArray();
        $userAccessibleAreaIds = $user->accessibleAreas->pluck('id')->toArray();
        $availableModules = User::availableModules();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'areas', 'accessibleFolderIds', 'positions', 'userAccessibleAreaIds', 'availableModules', 'roles')); 
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|digits:10', 
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
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'accessible_area_ids' => 'nullable|array',
            'accessible_area_ids.*' => 'exists:areas,id',
            'visible_modules' => 'nullable|array',
            'visible_modules.*' => 'string',
            'ff_visible_tiles' => 'nullable|array',
            'ff_visible_tiles.*' => 'string',
            'ff_role_name' => 'nullable|string|max:255',
            'ff_granular_permissions' => 'nullable|array',
            'role_id' => 'nullable|exists:roles,id',                     
        ];

        if (!$request->has('is_client') || !$request->input('is_client')) {
            $rules['area_id'] = 'required|exists:areas,id';
            $request->request->set('accessible_folder_ids', []);
        } else {
            $rules['area_id'] = 'nullable|exists:areas,id';
            $rules['accessible_folder_ids'] = 'nullable|array';
            $rules['accessible_folder_ids.*'] = 'exists:folders,id';
        }

        $request->validate($rules);

        $user->fill($request->only([
            'name', 
            'email', 
            'position', 
            'phone_number',
            'is_active'
        ]));

        if ($request->has('is_client') && $request->input('is_client') && !$request->filled('area_id')) {
            $user->area_id = null;
        } else {
            $user->area_id = $request->area_id;
        }

        $user->is_area_admin = $request->has('is_area_admin');
        $user->is_client = $request->has('is_client');
        $user->visible_modules = $request->input('visible_modules', []);
        $user->ff_visible_tiles = $request->input('ff_visible_tiles', []);
        $user->ff_role_name = $request->input('ff_role_name');
        $user->ff_granular_permissions = $request->input('ff_granular_permissions', []);
        $user->role_id = $request->input('role_id');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('s3')->exists($user->profile_photo_path)) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('profile_photo')->store('profile_photos', 's3');
        } elseif ($request->input('remove_profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('s3')->exists($user->profile_photo_path)) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }

        $user->save();

        if ($user->isClient()) {
            $folderIds = explode(',', $request->input('accessible_folder_ids')[0] ?? '');
            $folderIds = array_filter(array_map('intval', $folderIds));
            $user->accessibleFolders()->sync($folderIds);
            
            $user->accessibleAreas()->detach(); 
        } else {
            $areaIds = $request->input('accessible_area_ids', []);
            $user->accessibleAreas()->sync($areaIds);
            $user->accessibleFolders()->detach();
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        DB::transaction(function () use ($user) {
            $user->folders()->each(function ($folder) {
                $folder->delete();
            });

            $user->fileLinks()->each(function ($fileLink) {
                $fileLink->delete();
            });

            $user->accessibleFolders()->detach();
            $user->accessibleAreas()->detach();

            if ($user->organigramMember) {
                $user->organigramMember->delete();
            }

            if ($user->profile_photo_path) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }

            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'Usuario y todo su contenido han sido eliminados exitosamente.');
    }

    public function transferAndDestroy(Request $request, User $userToDelete)
    {
        $request->validate([
            'new_owner_id' => ['required', 'exists:users,id', Rule::notIn([$userToDelete->id])],
        ]);

        $newOwner = User::find($request->new_owner_id);

        if ($userToDelete->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        DB::transaction(function () use ($userToDelete, $newOwner) {
            $userToDelete->folders()->update(['user_id' => $newOwner->id]);
            $userToDelete->fileLinks()->update(['user_id' => $newOwner->id]);

            $userToDelete->accessibleFolders()->detach();
            $userToDelete->accessibleAreas()->detach();

            if ($userToDelete->organigramMember) {
                $userToDelete->organigramMember->delete();
            }

            if ($userToDelete->profile_photo_path) {
                Storage::disk('s3')->delete($userToDelete->profile_photo_path);
            }

            $userToDelete->delete();
        });

        return redirect()->route('admin.users.index')->with('success', "Contenido de {$userToDelete->name} transferido a {$newOwner->name} y usuario eliminado.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);
        $ids = $request->input('ids');

        if (in_array(auth()->id(), $ids)) {
            throw ValidationException::withMessages(['ids' => 'No puedes eliminar tu propia cuenta en una acción masiva.']);
        }

        $users = User::whereIn('id', $ids)->get();

        foreach ($users as $user) {
            if ($user->profile_photo_path) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $user->delete();
        }

        return redirect()->route('admin.users.index')->with('success', count($ids) . ' usuarios eliminados exitosamente.');
    }

    public function bulkResendWelcome(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);
        $ids = $request->input('ids');

        $users = User::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($users as $user) {
            $temporaryPassword = Str::random(10);

            $user->password = Hash::make($temporaryPassword);
            $user->save();

            try {
                Mail::to($user->email)->send(new WelcomeNewUser($user, $temporaryPassword, true));
                $count++;
            } catch (\Exception $e) {
                Log::error("Error al reenviar correo de bienvenida a {$user->email}: " . $e->getMessage());
            }
        }

        if ($count > 0) {
            return redirect()->route('admin.users.index')->with('success', 'Correo de bienvenida reenviado a ' . $count . ' usuarios con una nueva contraseña temporal.');
        }

        return redirect()->route('admin.users.index')->with('error', 'No se pudo enviar el correo a los usuarios seleccionados. Revise los logs.');
    }
}