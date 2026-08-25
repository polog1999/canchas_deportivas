<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    protected static ?string $password;

    public function definition(): array
    {
        $rol = Rol::firstOrCreate(
            ['nombre' => 'cliente'],
            ['descripcion' => 'Cliente', 'activo' => true]
        );

        return [
            'rol_id' => $rol->id,
            'usuario' => fake()->unique()->userName(),
            'nombre' => fake()->name(),
            'correo_electronico' => fake()->unique()->safeEmail(),
            'correo_verificado_en' => now(),
            'clave' => static::$password ??= 'password',
            'activo' => true,
            'token_recordar' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'correo_verificado_en' => null,
        ]);
    }
}
