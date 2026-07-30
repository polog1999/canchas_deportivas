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
         Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            // Esta línea crea la relación uno-a-uno con la tabla users
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 3)->nullable();
            $table->string('document_number', 15)->nullable()->unique();
             $table->string('names')->nullable();
            $table->string('last_name_paternal')->nullable();
            $table->string('last_name_maternal')->nullable();
            $table->string('address')->nullable();
            $table->string('ubigeo_department')->nullable();
            $table->string('ubigeo_province')->nullable();
            $table->string('ubigeo_district')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
