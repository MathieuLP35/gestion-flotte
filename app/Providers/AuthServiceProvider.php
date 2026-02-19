<?php

namespace App\Providers;

use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\PassengerPolicy;
use App\Policies\ReservationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Reservation::class => ReservationPolicy::class,
        Passenger::class => PassengerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability): ?true {
            // Si l'utilisateur a ce rôle, il a tous les droits.
            if ($user->hasRole('Super Admin')) {
                return true;
            }

            // Si non, on retourne null pour laisser les
            // autres Policies (ReservationPolicy...) faire leur travail.
            return null;
        });
    }
}
