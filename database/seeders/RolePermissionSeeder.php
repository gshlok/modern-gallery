<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'upload images',
            'edit own images',
            'delete own images',
            'moderate content',
            'manage users',
            'manage settings',
            'view private content',
            'bulk operations',
            'generate ai images',
            'access analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Visitor role - basic permissions
        $visitor = Role::create(['name' => 'visitor']);
        $visitor->givePermissionTo([
            'upload images',
            'edit own images',
            'delete own images',
        ]);

        // Editor role - content management permissions
        $editor = Role::create(['name' => 'editor']);
        $editor->givePermissionTo([
            'upload images',
            'edit own images',
            'delete own images',
            'moderate content',
            'view private content',
            'bulk operations',
            'generate ai images',
        ]);

        // Admin role - all permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());
    }
}
