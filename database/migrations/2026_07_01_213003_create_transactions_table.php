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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');

            // Datos de la respuesta de Niubiz
            $table->string('transaction_id'); // ID único que devuelve Niubiz
            $table->string('authorization_code')->nullable(); // Código de aprobación del banco
            $table->string('card_brand')->nullable(); // Visa, Mastercard
            $table->string('masked_card')->nullable(); // Ej: ****4444

            // Gestión del estado
            $table->decimal('amount', 8, 2);
            $table->string('status'); // 'approved', 'declined', 'error'
            $table->json('raw_response'); // Guarda aquí todo el JSON de Niubiz por si necesitas auditar luego

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
