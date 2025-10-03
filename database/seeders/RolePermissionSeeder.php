<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permission groups
        $permissionGroups = [
            'invoice' => [
                'create' => 'Create new invoices',
                'edit' => 'Edit existing invoices',
                'view' => 'View invoices',
                'delete' => 'Delete invoices',
                'send' => 'Send invoices to clients',
                'approve' => 'Approve invoices',
            ],
            'client' => [
                'create' => 'Create new clients',
                'edit' => 'Edit existing clients',
                'view' => 'View clients',
                'delete' => 'Delete clients',
            ],
            'report' => [
                'view' => 'View reports',
                'export' => 'Export reports',
            ],
            'user' => [
                'view' => 'View users',
                'create' => 'Create users',
                'edit' => 'Edit users',
                'delete' => 'Delete users',
                'impersonate' => 'Impersonate users',
            ],
            'role' => [
                'view' => 'View roles',
                'create' => 'Create roles',
                'edit' => 'Edit roles',
                'delete' => 'Delete roles',
            ],
            'system' => [
                'settings' => 'Manage system settings',
                'backup' => 'Perform system backups',
                'audit' => 'View audit logs',
            ],
        ];

        // Create permissions
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permission => $description) {
                Permission::create([
                    'name' => "{$group}.{$permission}",
                    'group' => $group,
                    'description' => $description,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create roles with hierarchy
        $roles = [
            'super-admin' => [
                'level' => 100,
                'description' => 'Full system access and administration',
                'is_default' => false,
            ],
            'admin' => [
                'level' => 90,
                'description' => 'Administrative access with user management',
                'is_default' => false,
            ],
            'manager' => [
                'level' => 80,
                'description' => 'Management level access for business operations',
                'is_default' => false,
            ],
            'accountant' => [
                'level' => 70,
                'description' => 'Financial and reporting access',
                'is_default' => false,
            ],
            'user' => [
                'level' => 50,
                'description' => 'Standard user with basic access',
                'is_default' => true,
            ],
        ];

        foreach ($roles as $name => $attributes) {
            Role::create([
                'name' => $name,
                'description' => $attributes['description'],
                'level' => $attributes['level'],
                'is_default' => $attributes['is_default'],
                'guard_name' => 'web',
            ]);
        }

                // Add client-specific permissions
        $clientPermissions = [
            'client.export' => 'Export clients',
            'client.import' => 'Import clients',
            'client.bulk_delete' => 'Bulk delete clients',
            'client.view_contacts' => 'View client contacts',
            'client.manage_contacts' => 'Manage client contacts',
            'client.view_notes' => 'View client notes',
            'client.manage_notes' => 'Manage client notes',
        ];

        foreach ($clientPermissions as $permission => $description) {
            Permission::create([
                'name' => $permission,
                'group' => 'client',
                'description' => $description,
                'guard_name' => 'web',
            ]);
        }


        // Assign permissions to roles
        $superAdmin = Role::findByName('super-admin');
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::findByName('admin');
        $admin->givePermissionTo([
            'invoice.create', 'invoice.edit', 'invoice.view', 'invoice.delete', 'invoice.send', 'invoice.approve',
            'client.create', 'client.edit', 'client.view', 'client.delete',
            'report.view', 'report.export',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'system.settings',
            'client.export', 'client.import', 'client.bulk_delete',
            'client.view_contacts', 'client.manage_contacts',
            'client.view_notes', 'client.manage_notes',
        ]);

        $manager = Role::findByName('manager');
        $manager->givePermissionTo([
            'invoice.create', 'invoice.edit', 'invoice.view', 'invoice.send',
            'client.create', 'client.edit', 'client.view',
            'report.view',
            'user.view',
            'client.export',
            'client.view_contacts', 'client.manage_contacts',
            'client.view_notes', 'client.manage_notes',
        ]);

        $accountant = Role::findByName('accountant');
        $accountant->givePermissionTo([
            'invoice.view', 'invoice.approve',
            'client.view',
            'report.view', 'report.export',
        ]);

        $user = Role::findByName('user');
        $user->givePermissionTo([
            'invoice.view',
            'client.view',
        ]);

        // Create default users
        $superAdminUser = User::create([
            'name' => 'Bereket Tsegay',
            'email' => 'berie@bntech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'invited_at' => now(),
            'timezone' => 'UTC',
        ]);
        $superAdminUser->assignRole($superAdmin);

        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@bntech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'invited_by' => $superAdminUser->id,
            'invited_at' => now(),
            'timezone' => 'America/New_York',
        ]);
        $adminUser->assignRole($admin);

        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@bntech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'invited_by' => $superAdminUser->id,
            'invited_at' => now(),
            'timezone' => 'America/Chicago',
        ]);
        $managerUser->assignRole($manager);

        $accountantUser = User::create([
            'name' => 'Accountant User',
            'email' => 'accountant@bntech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'invited_by' => $adminUser->id,
            'invited_at' => now(),
            'timezone' => 'America/Los_Angeles',
        ]);
        $accountantUser->assignRole($accountant);

        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@bntech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'invited_by' => $managerUser->id,
            'invited_at' => now(),
            'timezone' => 'Europe/London',
        ]);
        $regularUser->assignRole($user);

        // Create some pending invitations
        $pendingUsers = [
            [
                'name' => 'Pending Manager',
                'email' => 'pending-manager@bntech.com',
                'role' => 'manager',
            ],
            [
                'name' => 'Pending Accountant',
                'email' => 'pending-accountant@bntech.com',
                'role' => 'accountant',
            ],
        ];

        foreach ($pendingUsers as $pendingUser) {
            $user = User::create([
                'name' => $pendingUser['name'],
                'email' => $pendingUser['email'],
                'password' => Hash::make(Str::random(12)),
                'email_verified_at' => null,
                'invited_by' => $superAdminUser->id,
                'invited_at' => now(),
                'invitation_sent_at' => now(),
            ]);
            $user->assignRole($pendingUser['role']);
        }
    }
}
