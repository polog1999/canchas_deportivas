<?php

use App\Livewire\Admin\TusneCatalogManager;
use App\Livewire\UserManagement;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Tu ruta de prueba (puedes cambiar 'canchas-prueba' por la URL que quieras)
Route::get('/prueba', function () {
    return view('prueba'); // Apunta a prueba.blade.php
})->middleware(['auth'])->name('prueba');


Route::middleware(['auth', 'permission:usuarios::crear|usuarios::editar|usuarios::eliminar|usuarios::ver'])->prefix('portal')->group(function () {
    Route::get('users', UserManagement::class)->name('users');
      Route::get('/tusne-catalog', TusneCatalogManager::class)->name('tusne.index');
});
