import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable centralisant les permissions admin (menu, accès rapides, etc.).
 * Utilise les permissions partagées via Inertia (page.props.auth.permissions).
 *
 * @returns {Object} canViewDashboard, canViewAgences, canViewRoles, canViewVehicles,
 *   canViewVehicleSuggestion, canViewUsers, canViewDomains, canViewAllAgences, showVehiclesMenu
 */
export default function useAdminPermissions() {
    const page = usePage();
    const perms = computed(() => page.props.auth?.permissions ?? []);

    const canViewDashboard = computed(() => perms.value.includes('admin.view'));
    const canViewAgences = computed(() => perms.value.includes('agences.view'));
    const canViewRoles = computed(() => perms.value.includes('roles.view'));
    const canViewVehicles = computed(() => perms.value.includes('vehicles.view'));
    const canViewVehicleSuggestion = computed(() => perms.value.includes('vehicle_suggestion.view'));
    const canViewUsers = computed(() => perms.value.includes('users.view'));
    const canViewDomains = computed(() => perms.value.includes('allowed_domains.view'));
    const canViewAllAgences = computed(() => perms.value.includes('agences.view_all'));
    const showVehiclesMenu = computed(() => canViewVehicles.value || canViewVehicleSuggestion.value);

    return {
        perms,
        canViewDashboard,
        canViewAgences,
        canViewRoles,
        canViewVehicles,
        canViewVehicleSuggestion,
        canViewUsers,
        canViewDomains,
        canViewAllAgences,
        showVehiclesMenu,
    };
}
