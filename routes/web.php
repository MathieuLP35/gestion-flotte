<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\VehicleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('vehicles', VehicleController::class);
    Route::resource('reservations', ReservationController::class);
    Route::resource('maintenances', MaintenanceController::class);
    Route::resource('passengers', PassengerController::class);
    Route::get('/dashboard', [ReservationController::class, 'dashboard'])->name('dashboard');

    Route::get('reservations/{reservation}', [ReservationController::class, 'show'])
        ->name('reservations.show');

    // Route pour VÉRIFIER les trajets disponibles
    Route::post('reservations/check-carpool', [ReservationController::class, 'checkCarpool'])
        ->name('reservations.checkCarpool');

    Route::get('/reservations/{reservation}/messages', [MessageController::class, 'index'])
        ->name('messages.index');

    // Route pour ENVOYER un nouveau message dans une réservation
    Route::post('/reservations/{reservation}/messages', [MessageController::class, 'store'])
        ->name('messages.store');
});


Route::middleware(['auth', 'verified', 'admin']) // <- Notre "videur"
    ->prefix('admin') // <- Toutes les URL commenceront par /admin
    ->name('admin.') // <- Tous les noms de route commenceront par admin.
    ->group(function () {

    // /admin/dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // /admin/users, /admin/users/create, etc.
    Route::resource('users', AdminUserController::class);

    // /admin/roles
    Route::resource('roles', RoleController::class);

    // /admin/permissions
    Route::resource('permissions', PermissionController::class);

});

require __DIR__.'/auth.php';
