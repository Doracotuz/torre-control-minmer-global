<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::latest()->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $availablePermissions = User::availableFfPermissions();
        return view('admin.roles.create', compact('availablePermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        Role::create([
            'name' => $request->name,
            'permissions' => $request->permissions,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $availablePermissions = User::availableFfPermissions();
        return view('admin.roles.edit', compact('role', 'availablePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'name' => $request->name,
            'permissions' => $request->permissions,
        ]);
        
        // Propagate changes to users assigned to this role (Optional but clean)
        // If we want users to always reflect the role, we should update them.
        // Or, since we copy permissions to ff_granular_permissions, we should update those too.
        // Let's update all users with this role_id to have the new permissions.
        User::where('role_id', $role->id)->update([
            'ff_role_name' => $request->name,
            'ff_granular_permissions' => $request->permissions, // Casts handle array->json
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting if assigned to users? Or just nullify?
        // Migration set nullOnDelete, so users will lose the role_id but keep permissions in ff_granular_permissions (unless we clear them).
        // Let's just delete the role.
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}
