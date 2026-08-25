<?php

use App\Livewire\Admin\CourtManager;
use App\Livewire\Admin\LocationManager;
use App\Livewire\Admin\MenuStructureManager;
use App\Livewire\Admin\RoleMenuManager;
use App\Livewire\Admin\TusneCatalogManager;
use App\Livewire\UserManagement;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

Route::middleware(['auth'])->get('/dashboard', function () {
    $usuario = auth()->user();
    $primerMenu = $usuario?->menus()->first();

    if ($primerMenu) {
        return redirect()->to($primerMenu->url());
    }

    return redirect('/portal/users');
})->name('dashboard');

Route::get('/prueba', function () {
    return view('prueba');
})->middleware(['auth'])->name('prueba');

Route::middleware(['auth'])->prefix('portal')->group(function () {
    // El permiso es la ruta/link del menú. El nombre (nombre) se puede cambiar libremente.
    Route::get('users', UserManagement::class)
        ->middleware('permission:/portal/users')
        ->name('users');

    Route::get('/tusne-catalog', TusneCatalogManager::class)
        ->middleware('permission:/portal/tusne-catalog')
        ->name('tusne.index');

    Route::get('/locations', LocationManager::class)
        ->middleware('permission:/portal/locations')
        ->name('locations.index');

    Route::get('/courts', CourtManager::class)
        ->middleware('permission:/portal/courts')
        ->name('courts.index');

    Route::get('/roles-menus', RoleMenuManager::class)
        ->middleware('permission:/portal/roles-menus')
        ->name('roles-menus.index');

    Route::get('/menus', MenuStructureManager::class)
        ->middleware('permission:/portal/menus')
        ->name('menus.index');
});
