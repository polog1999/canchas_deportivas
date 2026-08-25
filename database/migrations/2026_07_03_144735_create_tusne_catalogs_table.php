<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogos_tusne', function (Blueprint $table) {
            $table->id();
            $table->string('grupo_tusne');
            $table->string('codigo_tusne');
            $table->string('descripcion_local');
            $table->boolean('incluye_camerinos')->default(false);
            $table->boolean('incluye_tribunas')->default(false);
            $table->boolean('incluye_arcos_f11')->default(false);
            $table->boolean('tiene_recaudacion_taquilla')->default(false);
            $table->string('modificador_tiempo')->default('ninguno');
            $table->string('tipo_cliente')->default('general');
            $table->boolean('esta_activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos_tusne');
    }
};
