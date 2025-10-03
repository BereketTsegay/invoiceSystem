<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('role.view');
        
        $roles = Role::with('permissions')->latest()->paginate(10);
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $this->authorize('role.create');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($request->permissions);

        return response()->json($role->load('permissions'), 201);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('role.edit');

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role)
    {
        $this->authorize('role.delete');

        // Prevent deletion of super-admin role
        if ($role->name === 'super-admin') {
            return response()->json([
                'message' => 'Cannot delete super-admin role.'
            ], 422);
        }

        $role->delete();

        return response()->json(null, 204);
    }

    public function permissions()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return response()->json($permissions);
    }
}