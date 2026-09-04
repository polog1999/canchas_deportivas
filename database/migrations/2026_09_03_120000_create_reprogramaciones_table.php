<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reprogramaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('reserva_id');
            $table->unsignedInteger('cancha_anterior_id');
            $table->unsignedInteger('cancha_nueva_id');
            $table->timestamp('hora_inicio_anterior');
            $table->timestamp('hora_fin_anterior');
            $table->timestamp('hora_inicio_nueva');
            $table->timestamp('hora_fin_nueva');
            $table->decimal('monto_validado', 8, 2);
            $table->unsignedInteger('catalogo_tusne_id')->nullable();
            $table->string('motivo')->nullable();
            $table->unsignedInteger('autorizado_por');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();

            $table->foreign('reserva_id')->references('id')->on('reservas');
            $table->foreign('cancha_anterior_id')->references('id')->on('canchas');
            $table->foreign('cancha_nueva_id')->references('id')->on('canchas');
            $table->foreign('catalogo_tusne_id')->references('id')->on('catalogos_tusne');
            $table->foreign('autorizado_por')->references('id')->on('usuarios');

            $table->index('reserva_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reprogramaciones');
    }
};
