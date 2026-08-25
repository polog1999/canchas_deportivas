<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Cola en sync (QUEUE_CONNECTION=sync). No se crean tablas.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
