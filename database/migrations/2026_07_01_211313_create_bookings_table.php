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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Relación con tu tabla de usuarios/vecinos
            $table->foreignId('court_id')->constrained();

            $table->dateTime('start_time');
            $table->dateTime('end_time');

            $table->decimal('total_price', 8, 2);
            $table->string('payment_reference')->nullable(); // Para el ID de transacción de Niubiz
            $table->string('status')->default('pending');

            $table->timestamps();

            // Índice para optimizar la búsqueda de disponibilidad
            $table->index(['facility_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
