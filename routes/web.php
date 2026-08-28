<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrarReservaController;
use App\Http\Controllers\ReservarController;
use App\Http\Controllers\ReservarTurnoController;
use App\Livewire\Admin\CourtManager;
use App\Livewire\Admin\DeporteManager;
use App\Livewire\Admin\LocationManager;
use App\Livewire\Admin\MenuStructureManager;
use App\Livewire\Admin\MisPagosManager;
use App\Livewire\Admin\RoleMenuManager;
use App\Livewire\Admin\SliderManager;
use App\Livewire\Admin\TusneCatalogManager;
use App\Livewire\Admin\VerReservasManager;
use App\Livewire\UserManagement;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

Route::get('/reservar', [ReservarController::class, 'index'])->name('reservar');
Route::get('/reservar/deporte', [ReservarController::class, 'deporte'])->name('reservar.deporte');
Route::get('/reservar/turno', ReservarTurnoController::class)->name('reservar.turno');
Route::get('/reservar/ocupacion', [ReservarController::class, 'ocupacion'])->name('reservar.ocupacion');
Route::get('/reservar/confirmar', [ReservarController::class, 'confirmar'])->name('reservar.confirmar');
Route::get('/reservar/pago', [ReservarController::class, 'pago'])->name('reservar.pago');
Route::post('/reservar/registrar', RegistrarReservaController::class)->name('reservar.registrar');
Route::post('/reservar/pago/verificar/{purchaseNumber}', \App\Http\Controllers\VerificarPagoNiubizController::class)
    ->name('reservar.pago.verificar');
Route::get('/reservar/buscar-documento', [ReservarController::class, 'buscarDocumento'])->name('reservar.buscar-documento');
Route::post('/reservar/verificar-acceso', [ReservarController::class, 'verificarAcceso'])->name('reservar.verificar-acceso');

Route::middleware(['auth'])->get('/dashboard', DashboardController::class)->name('dashboard');

Route::view('/prueba', 'prueba')->middleware(['auth'])->name('prueba');

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

    Route::get('/deportes', DeporteManager::class)
        ->middleware('permission:/portal/deportes')
        ->name('deportes.index');

    Route::get('/mis-pagos', MisPagosManager::class)
        ->middleware('permission:/portal/mis-pagos')
        ->name('mis-pagos.index');

    Route::get('/ver-reservas', VerReservasManager::class)
        ->middleware('permission:/portal/ver-reservas')
        ->name('ver-reservas.index');

    Route::get('/slider', SliderManager::class)
        ->middleware('permission:/portal/slider')
        ->name('slider.index');

    Route::get('/roles-menus', RoleMenuManager::class)
        ->middleware('permission:/portal/roles-menus')
        ->name('roles-menus.index');

    Route::get('/menus', MenuStructureManager::class)
        ->middleware('permission:/portal/menus')
        ->name('menus.index');
});
