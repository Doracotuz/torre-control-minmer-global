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
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private function getActiveArea()
    {
        $user = Auth::user();
        
        $activeAreaId = session('current_admin_area_id', $user->area_id);
        $activeArea = Area::find($activeAreaId);

        if (!$activeArea) {
            $activeArea = $user->area;
            $activeAreaId = $user->area_id;
            session(['current_admin_area_id' => $activeAreaId, 'current_admin_area_name' => $activeArea->name]);
        }

        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();
        if (!$user->is_area_admin || !$manageableAreaIds->contains($activeAreaId)) {
            abort(403, 'No tienes permiso para gestionar esta área.');
        }
        
        return $activeArea;
    }

    public function index(Request $request)
    {
        $currentArea = $this->getActiveArea();
        $areaId = $currentArea->id;
        
        $query = User::where('area_id', $areaId)->orderBy('name');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('position', 'like', $searchTerm)
                  ->orWhere('phone_number', 'like', $searchTerm);
            });
        }

        if ($request->filled('role')) {
            if ($request->role == 'admin') {
                $query->where('is_area_admin', true);
            } elseif ($request->role == 'normal') {
                $query->where('is_area_admin', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();

        $allAreaUsers = User::where('area_id', $areaId)
                            ->select('id', 'name')
                            ->orderBy('name')
                            ->get();        

        return view('area_admin.users.index', [
            'users' => $users,
            'currentArea' => $currentArea,
            'filters' => $request->only(['search', 'role']),
            'allAreaUsers' => $allAreaUsers,
        ]);
    }

    public function create()
    {
        $currentArea = $this->getActiveArea();
        $positions = OrganigramPosition::orderBy('name')->get();

        return view('area_admin.users.create', compact('currentArea', 'positions'));
    }

    public function store(Request $request)
    {
        $currentArea = $this->getActiveArea();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|digits:10',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $user = new User();
        $user->fill([
            'name' => $request->name,
            'position' => $request->position,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'area_id' => $currentArea->id,
        ]);

        $user->password = Hash::make($request->password);
        $user->is_area_admin = $request->boolean('is_area_admin'); 
        $user->is_client = false;

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 's3');
            $user->profile_photo_path = $path;
        }

        $user->save();

        try {
            Mail::to($user->email)->send(new WelcomeNewUser($user, $request->password));
        } catch (\Exception $e) {
            Log::error("Error al enviar correo de bienvenida desde AreaAdmin a {$user->email}: " . $e->getMessage());
        }

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario creado y notificado exitosamente.');
    }

    public function edit(User $user)
    {
        $currentArea = $this->getActiveArea();

        if ($currentArea->id !== $user->area_id) {
            abort(403, 'Este usuario no pertenece al área que estás gestionando actualmente.');
        }

        $positions = OrganigramPosition::orderBy('name')->get();

        return view('area_admin.users.edit', compact('user', 'currentArea', 'positions'));
    }

    public function update(Request $request, User $user)
    {
        $currentArea = $this->getActiveArea();

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

        if ($request->has('remove_profile_photo') && $request->boolean('remove_profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }
        elseif ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('s3')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 's3');
            $user->profile_photo_path = $path;
        }

        $user->save();

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $currentArea = $this->getActiveArea();

        if ($currentArea->id !== $user->area_id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No tienes permiso para eliminar este usuario.');
        }

        if (Auth::id() === $user->id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No puedes eliminar tu propia cuenta desde aquí.');
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

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario y todo su contenido han sido eliminados exitosamente.');
    }

    public function transferAndDestroy(Request $request, User $userToDelete)
    {
        $request->validate([
            'new_owner_id' => ['required', 'exists:users,id', Rule::notIn([$userToDelete->id])],
        ]);

        $currentArea = $this->getActiveArea();
        $newOwner = User::find($request->new_owner_id);

        if ($currentArea->id !== $userToDelete->area_id) {
            abort(403, 'Este usuario no pertenece al área que estás gestionando.');
        }
        if (Auth::id() === $userToDelete->id) {
            return redirect()->route('area_admin.users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }
        if ($newOwner->area_id !== $currentArea->id) {
            return redirect()->route('area_admin.users.index')->with('error', 'El nuevo propietario debe pertenecer a la misma área.');
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

        return redirect()->route('area_admin.users.index')->with('success', "Contenido de {$userToDelete->name} transferido a {$newOwner->name} y usuario eliminado.");
    }

}