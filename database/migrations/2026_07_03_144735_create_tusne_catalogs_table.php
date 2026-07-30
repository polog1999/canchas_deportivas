<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tusne_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('tusne_group'); // Código de grupo en Oracle (Ej: '04')
            $table->string('tusne_code');  // Código del servicio en Oracle (Ej: '0125')
            $table->string('local_description'); // Nombre limpio para la Web / Panel
            
            // Banderas lógicas del paquete cerrado que cobra este TUSNE:
            $table->boolean('includes_dressing_rooms')->default(false); // ¿Incluye camerinos?
            $table->boolean('includes_stands')->default(false);         // ¿Incluye tribuna?
            $table->boolean('includes_goals_f11')->default(false);      // ¿Incluye arcos/división fútbol 11?
            $table->boolean('has_gate_revenue')->default(false);        // ¿Es con taquilla?
            
            // Filtros de ayuda para tu buscador backend
            $table->string('time_modifier')->default('none'); // 'day', 'night', 'none'
            $table->string('client_type')->default('general'); // 'vecino', 'no_vecino', 'general'
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índice único para evitar duplicar la misma combinación de Oracle
            $table->unique(['tusne_group', 'tusne_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tusne_catalogs');
    }
};
