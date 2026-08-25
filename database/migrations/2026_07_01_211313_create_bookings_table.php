<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('cancha_id')->constrained('canchas');
            $table->timestamp('hora_inicio');
            $table->timestamp('hora_fin');
            $table->decimal('precio_total', 8, 2);
            $table->string('referencia_pago')->nullable();
            $table->string('estado')->default('pendiente');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
