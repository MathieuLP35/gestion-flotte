<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleSuggestionSetting extends Model
{
    protected $table = 'vehicle_suggestion_settings';

    protected $fillable = [
        'petit_trajet_seuil_km',
        'priorite_petit_trajet',
        'priorite_long_trajet',
    ];

    protected $casts = [
        'petit_trajet_seuil_km' => 'integer',
        'priorite_petit_trajet' => 'array',
        'priorite_long_trajet' => 'array',
    ];

    /** Valeurs par défaut si la table ou la ligne n'existe pas */
    private const DEFAULTS = [
        'petit_trajet_seuil_km' => 100,
        'priorite_petit_trajet' => ['electrique', 'hybride'],
        'priorite_long_trajet' => ['hybride', 'essence', 'diesel'],
    ];

    /** Types d'énergie reconnus (utilisés pour la validation) */
    public const ENERGIES = ['electrique', 'hybride', 'essence', 'diesel'];

    /**
     * Retourne la configuration. Crée la ligne avec les défauts si absente.
     */
    public static function get(): self
    {
        $row = self::first();
        if ($row) {
            return $row;
        }

        return self::create(self::DEFAULTS);
    }
}
