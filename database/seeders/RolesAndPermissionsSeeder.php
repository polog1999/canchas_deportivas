<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = Rol::firstOrCreate(
            ['nombre' => 'admin'],
            [
                'descripcion' => 'Administrador del sistema',
                'activo' => true,
            ]
        );

        $menus = [
            [
                'nombre' => 'Canchas',
                'ruta' => '/portal/courts',
                'icono' => 'fa-futbol',
                'orden' => 1,
            ],
            [
                'nombre' => 'Sedes',
                'ruta' => '/portal/locations',
                'icono' => 'fa-location-dot',
                'orden' => 2,
            ],
            [
                'nombre' => 'Deportes',
                'ruta' => '/portal/deportes',
                'icono' => 'fa-medal',
                'orden' => 3,
            ],
            [
                'nombre' => 'Mis Pagos',
                'ruta' => '/portal/mis-pagos',
                'icono' => 'fa-receipt',
                'orden' => 4,
            ],
            [
                'nombre' => 'Ver Reservas',
                'ruta' => '/portal/ver-reservas',
                'icono' => 'fa-calendar-check',
                'orden' => 5,
            ],
            [
                'nombre' => 'Slider',
                'ruta' => '/portal/slider',
                'icono' => 'fa-images',
                'orden' => 6,
            ],
            [
                'nombre' => 'Tusne',
                'ruta' => '/portal/tusne-catalog',
                'icono' => 'fa-list',
                'orden' => 7,
            ],
            [
                'nombre' => 'Usuarios',
                'ruta' => '/portal/users',
                'icono' => 'fa-users',
                'orden' => 8,
            ],
            [
                'nombre' => 'Roles y Menús',
                'ruta' => '/portal/roles-menus',
                'icono' => 'fa-shield',
                'orden' => 9,
            ],
            [
                'nombre' => 'Estructura de Menús',
                'ruta' => '/portal/menus',
                'icono' => 'fa-sitemap',
                'orden' => 10,
            ],
        ];

        $menuIds = [];
        foreach ($menus as $menuData) {
            $menu = Menu::updateOrCreate(
                ['nombre' => $menuData['nombre']],
                [
                    'ruta' => $menuData['ruta'],
                    'icono' => $menuData['icono'],
                    'orden' => $menuData['orden'],
                    'activo' => true,
                ]
            );
            $menuIds[] = $menu->id;
        }

        $rolAdmin->menus()->sync($menuIds);

        Usuario::updateOrCreate(
            ['usuario' => 'admin'],
            [
                'rol_id' => $rolAdmin->id,
                'correo_electronico' => 'admin@gmail.com',
                'clave' => 'password',
                'activo' => true,
            ]
        );

        $admin = Usuario::where('usuario', 'admin')->first();
        if ($admin && ! $admin->perfil) {
            $admin->perfil()->create([
                'nombres' => 'Administrador',
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => 'La Molina',
            ]);
        }
    }
}
