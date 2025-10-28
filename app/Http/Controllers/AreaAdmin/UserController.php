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

        return view('area_admin.users.index', [
            'users' => $users,
            'currentArea' => $currentArea,
            'filters' => $request->only(['search', 'role'])
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

        $userData = [
            'name' => $request->name,
            'position' => $request->position,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_id' => $currentArea->id,
            'is_area_admin' => $request->boolean('is_area_admin'),
            'is_client' => false,
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 's3');
            $userData['profile_photo_path'] = $path;
        }

        $user = User::create($userData);

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
            $path = $request->file('profile_photo')->store('profile-photos', 's3');
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

        if ($user->profile_photo_path) {
            Storage::disk('s3')->delete($user->profile_photo_path);
        }

        $user->delete();

        return redirect()->route('area_admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}