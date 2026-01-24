<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->middleware('guest');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [ReservationController::class, 'dashboard'])->name('dashboard');

    Route::get('reservations/suggest-vehicle', [ReservationController::class, 'suggestVehicle'])
        ->name('reservations.suggestVehicle');
    Route::resource('reservations', ReservationController::class);
    Route::resource('passengers', PassengerController::class);

    Route::get('reservations/{reservation}', [ReservationController::class, 'show'])
        ->name('reservations.show');

    // Routes pour la gestion du retour du véhicule
    Route::get('reservations/{reservation}/return', [ReservationController::class, 'showReturnForm'])
        ->name('reservations.return.form');
    Route::post('reservations/{reservation}/return', [ReservationController::class, 'returnVehicle'])
        ->name('reservations.return');

    // Route pour lancer le trajet
    Route::post('reservations/{reservation}/start', [ReservationController::class, 'startTrip'])
        ->name('reservations.start');

    // Route pour terminer le trajet
    Route::post('reservations/{reservation}/end', [ReservationController::class, 'endTrip'])
        ->name('reservations.end');

    // Route pour VÉRIFIER les trajets disponibles
    Route::post('reservations/check-carpool', [ReservationController::class, 'checkCarpool'])
        ->name('reservations.checkCarpool');

    Route::get('/reservations/{reservation}/messages', [MessageController::class, 'index'])
        ->name('messages.index');

    // Route pour ENVOYER un nouveau message dans une réservation
    Route::post('/reservations/{reservation}/messages', [MessageController::class, 'store'])
        ->name('messages.store');

    // Route pour la recherche de covoiturage
    Route::post('/carpooling/search', [App\Http\Controllers\CarpoolingController::class, 'search'])
        ->name('carpooling.search');
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

    // /admin/vehicles
    // IMPORTANT: Les routes spécifiques doivent être définies AVANT la route resource
    Route::get('vehicles/availability', [VehicleController::class, 'availability'])->name('vehicles.availability');
    Route::get('vehicles/{vehicle}/calendar', [VehicleController::class, 'calendar'])->name('vehicles.calendar');
    Route::resource('vehicles', VehicleController::class);

    // /admin/maintenances
    Route::resource('maintenances', MaintenanceController::class);

    // /admin/keys
    Route::resource('keys', App\Http\Controllers\Admin\KeyController::class);

    // /admin/permissions
    // Route::resource('permissions', PermissionController::class);

    // /admin/domains
    Route::resource('domains', App\Http\Controllers\Admin\AllowedDomainController::class);

    // /admin/settings/vehicle-suggestion
    Route::get('settings/vehicle-suggestion', [App\Http\Controllers\Admin\VehicleSuggestionSettingController::class, 'edit'])->name('settings.vehicleSuggestion.edit');
    Route::put('settings/vehicle-suggestion', [App\Http\Controllers\Admin\VehicleSuggestionSettingController::class, 'update'])->name('settings.vehicleSuggestion.update');
});

require __DIR__.'/auth.php';
