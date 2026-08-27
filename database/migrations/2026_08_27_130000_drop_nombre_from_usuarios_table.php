<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'nombre')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('nombre');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('usuarios', 'nombre')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('nombre', 150)->nullable()->after('clave');
            });
        }
    }
};
