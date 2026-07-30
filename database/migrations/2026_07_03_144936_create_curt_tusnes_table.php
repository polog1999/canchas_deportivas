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
        Schema::create('curt_tusnes', function (Blueprint $table) {
            $table->id();

            // Vincula qué códigos TUSNE aplican a qué canchas físicas
            $table->foreignId('court_id')->constrained()->onDelete('cascade');
            $table->foreignId('tusne_catalog_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curt_tusnes');
    }
};
