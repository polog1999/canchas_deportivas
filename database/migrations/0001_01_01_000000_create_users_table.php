<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_padre')->nullable()->constrained('menus')->nullOnDelete();
            $table->string('nombre', 100);
            $table->string('ruta', 200);
            $table->string('icono', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles');
            $table->string('usuario', 50)->unique();
            $table->string('clave');
            $table->string('correo_electronico', 150)->nullable();
            $table->timestamp('correo_verificado_en')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('token_recordar', 100)->nullable();
            $table->text('secreto_dos_factores')->nullable();
            $table->text('codigos_recuperacion_dos_factores')->nullable();
            $table->timestamp('dos_factores_confirmado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();

            $table->index('rol_id', 'idx_usuarios_rol');
        });

        Schema::create('menus_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->unique(['rol_id', 'menu_id']);
            $table->index('menu_id', 'idx_menus_roles_menu');
            $table->index('rol_id', 'idx_menus_roles_rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus_roles');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('roles');
    }
};
