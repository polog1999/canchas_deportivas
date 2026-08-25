<?php

namespace Database\Seeders; // <--- ¡Asegúrate de que esta línea exista!

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caché de Spatie (Muy importante para evitar conflictos)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear los Permisos (Acciones específicas de tu app de Canchas)
        // Permission::create(['name' => 'ver canchas']);
        // Permission::create(['name' => 'crear canchas']);
        // Permission::create(['name' => 'editar canchas']);
        // Permission::create(['name' => 'eliminar canchas']);
        // Permission::create(['name' => 'ver reportes pagos']);
        Permission::firstOrCreate(['name' => 'usuarios::crear']);
        Permission::firstOrCreate(['name' => 'usuarios::editar']);
        Permission::firstOrCreate(['name' => 'usuarios::ver']);
        Permission::firstOrCreate(['name' => 'usuarios::eliminar']);
        Permission::firstOrCreate(['name' => 'tusnes::ver']);
        Permission::firstOrCreate(['name' => 'sedes::ver']);
        Permission::firstOrCreate(['name' => 'canchas::ver']);
        

        $superAdminRole = Role::firstOrCreate(['name' => UserRole::SUPERADMIN]);
        $adminRole = Role::firstOrCreate(['name' => UserRole::ADMIN]);
        $clienteRole = Role::firstOrCreate(['name' => UserRole::CLIENTE]);
        // 4. Asignar Permisos a los Roles
        // El ADMIN puede hacer de todo con las canchas, pero no ver reportes de dinero
        $superAdminRole->givePermissionTo(['usuarios::crear', 'usuarios::editar', 'usuarios::ver','usuarios::eliminar', 'tusnes::ver', 'sedes::ver', 'canchas::ver']);

        // El CLIENTE solo puede ver las canchas
        // $cliente->givePermissionTo(['ver canchas']);

        // El SUPERADMIN obtiene absolutamente todos los permisos automáticamente
        // (Spatie permite darle todo usando la sincronización)
        // $superAdmin->givePermissionTo(Permission::all());



        $admin = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
            'name' => 'Test User',
            
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($superAdminRole); // El usuario ahora es administrador
    }
}
