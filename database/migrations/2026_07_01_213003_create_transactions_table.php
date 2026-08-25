<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas');
            $table->string('transaccion_id');
            $table->string('codigo_autorizacion')->nullable();
            $table->string('marca_tarjeta')->nullable();
            $table->string('tarjeta_enmascarada')->nullable();
            $table->decimal('monto', 8, 2);
            $table->string('estado');
            $table->json('respuesta_bruta');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacciones');
    }
};
