<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class CarpoolingController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'departure' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departureDate' => 'required|date',
            'arrivalDate' => 'nullable|date|after_or_equal:departureDate',
        ]);

        $carpoolings = Reservation::searchCarpoolings(
            $validated['departure'],
            $validated['destination'],
            $validated['departureDate'],
            $validated['arrivalDate'] ?? null
        );

        return inertia('Carpooling/Search', [
            'carpoolings' => $carpoolings,
        ]);
    }
}
