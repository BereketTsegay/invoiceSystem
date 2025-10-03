<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('role.view') ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        // Users can view roles if they have permission or are super-admin
        // But prevent viewing super-admin role unless super-admin
        if ($role->name === 'super-admin' && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasPermissionTo('role.view') ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only super-admin and admin can create roles
        return $user->hasPermissionTo('role.create') &&
               ($user->hasRole('super-admin') || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        // Prevent modifying super-admin role unless super-admin
        if ($role->name === 'super-admin' && !$user->hasRole('super-admin')) {
            return false;
        }

        // Prevent users from modifying roles with higher level than their own
        $userMaxRoleLevel = $user->roles->max('level');
        $roleLevel = $role->level;

        if ($roleLevel >= $userMaxRoleLevel && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasPermissionTo('role.edit') &&
               ($user->hasRole('super-admin') || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        // Prevent deleting super-admin role
        if ($role->name === 'super-admin') {
            return false;
        }

        // Prevent deleting default roles
        if ($role->is_default) {
            return false;
        }

        // Prevent users from deleting roles with higher level than their own
        $userMaxRoleLevel = $user->roles->max('level');
        $roleLevel = $role->level;

        if ($roleLevel >= $userMaxRoleLevel && !$user->hasRole('super-admin')) {
            return false;
        }

        // Check if role has users assigned (prevent deletion of roles in use)
        if ($role->users()->exists()) {
            return false;
        }

        return $user->hasPermissionTo('role.delete') &&
               ($user->hasRole('super-admin') || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can assign the role to users.
     */
    public function assign(User $user, Role $role): bool
    {
        // Prevent assigning super-admin role unless super-admin
        if ($role->name === 'super-admin' && !$user->hasRole('super-admin')) {
            return false;
        }

        // Prevent users from assigning roles with higher level than their own
        $userMaxRoleLevel = $user->roles->max('level');
        $roleLevel = $role->level;

        if ($roleLevel > $userMaxRoleLevel && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasPermissionTo('user.edit') ||
               $user->hasPermissionTo('user.create') ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can manage role permissions.
     */
    public function managePermissions(User $user, Role $role): bool
    {
        // Prevent managing super-admin role permissions unless super-admin
        if ($role->name === 'super-admin' && !$user->hasRole('super-admin')) {
            return false;
        }

        // Prevent users from managing permissions for roles with higher level
        $userMaxRoleLevel = $user->roles->max('level');
        $roleLevel = $role->level;

        if ($roleLevel >= $userMaxRoleLevel && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasPermissionTo('role.edit') &&
               ($user->hasRole('super-admin') || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can sync role permissions.
     */
    public function syncPermissions(User $user, Role $role): bool
    {
        return $this->managePermissions($user, $role);
    }

    /**
     * Determine whether the user can revoke the role from users.
     */
    public function revoke(User $user, Role $role, User $targetUser): bool
    {
        // Prevent revoking super-admin role from yourself
        if ($role->name === 'super-admin' && $user->id === $targetUser->id) {
            return false;
        }

        // Prevent revoking your own admin role if you're the only admin
        if ($role->name === 'admin' && $user->id === $targetUser->id) {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                return false;
            }
        }

        // Users cannot revoke roles from users with higher role levels
        $userMaxRoleLevel = $user->roles->max('level');
        $targetUserMaxRoleLevel = $targetUser->roles->max('level');

        if ($targetUserMaxRoleLevel >= $userMaxRoleLevel && $user->id !== $targetUser->id) {
            return false;
        }

        return $this->assign($user, $role);
    }

    /**
     * Determine whether the user can view the role's user assignments.
     */
    public function viewUsers(User $user, Role $role): bool
    {
        // Prevent viewing super-admin role users unless super-admin
        if ($role->name === 'super-admin' && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasPermissionTo('role.view') ||
               $user->hasPermissionTo('user.view') ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can export roles.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo('role.view') ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can import roles.
     */
    public function import(User $user): bool
    {
        return $user->hasPermissionTo('role.create') &&
               ($user->hasRole('super-admin') || $user->hasRole('admin'));
    }
}
