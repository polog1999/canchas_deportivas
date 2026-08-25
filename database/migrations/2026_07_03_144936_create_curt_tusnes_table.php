<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canchas_tusne', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancha_id')->constrained('canchas');
            $table->foreignId('catalogo_tusne_id')->constrained('catalogos_tusne');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canchas_tusne');
    }
};
