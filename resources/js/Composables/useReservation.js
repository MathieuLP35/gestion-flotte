// resources/js/composables/useReservation.js
import { router } from '@inertiajs/vue3';

export default function useReservation() {
    const cancelPassenger = (passengerId) => {
        if (confirm("Voulez-vous vraiment annuler votre place sur ce trajet ?")) {
            router.delete(route('passengers.destroy', passengerId), {
                preserveScroll: true,
                onSuccess: () => {
                    // Vous pourriez afficher un "toast" de succès ici
                },
            });
        }
    };

    const deleteReservation = (id) => {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
            router.delete(route('reservations.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    return {
        cancelPassenger,
        deleteReservation,
    };
}