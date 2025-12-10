<?php

namespace App\Http\Controllers\Admin; // <-- Notez le namespace !

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // On renvoie vers une page Vue dans un dossier Admin
        return inertia('Admin/Dashboard', [
            'stats' => [
                'users_count' => User::count(),
                'vehicles_count' => Vehicle::count(),
                'reservations_count' => Reservation::count(),
            ],
        ]); 
    }
}