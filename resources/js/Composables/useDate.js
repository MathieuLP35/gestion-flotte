// resources/js/composables/useDate.js
export default function useDate() {
    const formatDate = (dateString) => {
        const date = new Date(dateString);

        // Format UTC (date)
        const utcFormatter = new Intl.DateTimeFormat('fr-FR', {
            dateStyle: 'medium',
            timeZone: 'UTC',
        });

        // Format local (heure)
        const localFormatter = new Intl.DateTimeFormat('fr-FR', {
            timeStyle: 'short',
            timeZone: 'UTC',
        });

        const datePart = utcFormatter.format(date);
        const timePart = localFormatter.format(date);

        return `${datePart} à ${timePart}`;
    };

    return {
        formatDate,
    };
}