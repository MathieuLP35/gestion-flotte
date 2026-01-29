<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleSuggestionSetting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VehicleSuggestionSettingController extends Controller
{
    use AuthorizesRequests;

    public function edit()
    {
        $this->authorize('vehicle_suggestion.view');

        $s = VehicleSuggestionSetting::get();

        return Inertia::render('Admin/Settings/VehicleSuggestion', [
            'setting' => [
                'petit_trajet_seuil_km' => $s->petit_trajet_seuil_km,
                'priorite_petit_trajet' => $s->priorite_petit_trajet,
                'priorite_long_trajet' => $s->priorite_long_trajet,
            ],
            'energies' => VehicleSuggestionSetting::ENERGIES,
            'can' => [
                'edit' => Gate::allows('vehicle_suggestion.edit'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('vehicle_suggestion.edit');

        $request->merge([
            'priorite_petit_trajet' => array_values(array_filter($request->priorite_petit_trajet ?? [])),
            'priorite_long_trajet' => array_values(array_filter($request->priorite_long_trajet ?? [])),
        ]);
        $valid = $request->validate([
            'petit_trajet_seuil_km' => 'required|integer|min:1|max:2000',
            'priorite_petit_trajet' => 'required|array|min:1',
            'priorite_petit_trajet.*' => 'string|in:'.implode(',', VehicleSuggestionSetting::ENERGIES),
            'priorite_long_trajet' => 'required|array|min:1',
            'priorite_long_trajet.*' => 'string|in:'.implode(',', VehicleSuggestionSetting::ENERGIES),
        ]);

        $s = VehicleSuggestionSetting::get();
        $s->update($valid);

        return back()->with('success', 'Paramètres de suggestion de véhicule enregistrés.');
    }
}
