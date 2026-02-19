import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import VehicleSuggestion from '@/Pages/Admin/Settings/VehicleSuggestion.vue';
import { useForm, usePage } from '@inertiajs/vue3';

// Mock d'Inertia
vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    usePage: vi.fn(),
    useForm: vi.fn(),
}));

// Mock de Ziggy
global.route = vi.fn((name) => name);

describe('VehicleSuggestion Settings Page', () => {
    const mockEnergies = ['electrique', 'hybride', 'essence', 'diesel'];
    const mockSetting = {
        petit_trajet_seuil_km: 150,
        priorite_petit_trajet: ['electrique', 'hybride'],
        priorite_long_trajet: ['diesel'],
    };

    beforeEach(() => {
        vi.clearAllMocks();
        // Mock par défaut de usePage (pas de flash message)
        usePage.mockReturnValue({
            props: { flash: { success: null } }
        });
    });

    it('initialise le formulaire avec les valeurs des props et le padding des tableaux', () => {
        useForm.mockReturnValue({
            petit_trajet_seuil_km: 150,
            priorite_petit_trajet: ['electrique', 'hybride', '', ''],
            priorite_long_trajet: ['diesel', '', '', ''],
            errors: {},
            processing: false
        });

        mount(VehicleSuggestion, {
            props: {
                setting: mockSetting,
                energies: mockEnergies,
                can: { edit: true }
            }
        });

        // Vérifie que useForm a été appelé avec les données paddées (longueur 4)
        expect(useForm).toHaveBeenCalledWith(expect.objectContaining({
            petit_trajet_seuil_km: 150,
            priorite_petit_trajet: ['electrique', 'hybride', '', ''],
            priorite_long_trajet: ['diesel', '', '', '']
        }));
    });

    it('affiche le message de succès si présent dans les flash props', () => {
        usePage.mockReturnValue({
            props: { flash: { success: 'Paramètres mis à jour !' } }
        });

        const wrapper = mount(VehicleSuggestion, {
            props: { setting: mockSetting, energies: mockEnergies, can: { edit: true } }
        });

        expect(wrapper.find('.bg-green-50').text()).toBe('Paramètres mis à jour !');
    });

    it('désactive les champs si l\'utilisateur n\'a pas le droit d\'éditer', () => {
        useForm.mockReturnValue({
            petit_trajet_seuil_km: 100,
            priorite_petit_trajet: ['', '', '', ''],
            priorite_long_trajet: ['', '', '', ''],
            errors: {},
            processing: false
        });

        const wrapper = mount(VehicleSuggestion, {
            props: {
                setting: mockSetting,
                energies: mockEnergies,
                can: { edit: false } // DROIT REFUSÉ
            }
        });

        expect(wrapper.find('#seuil').element.disabled).toBe(true);
        expect(wrapper.find('select').element.disabled).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(false);
    });

    it('appelle form.transform et form.put lors de la soumission', async () => {
        const putMock = vi.fn();
        const transformMock = vi.fn().mockReturnThis(); // transform() doit retourner 'this'

        useForm.mockReturnValue({
            petit_trajet_seuil_km: 100,
            priorite_petit_trajet: ['electrique', '', '', ''],
            priorite_long_trajet: ['diesel', '', '', ''],
            errors: {},
            processing: false,
            transform: transformMock,
            put: putMock
        });

        const wrapper = mount(VehicleSuggestion, {
            props: { setting: mockSetting, energies: mockEnergies, can: { edit: true } }
        });

        await wrapper.find('form').trigger('submit.prevent');

        expect(transformMock).toHaveBeenCalled();
        expect(putMock).toHaveBeenCalledWith('admin.settings.vehicleSuggestion.update');
    });

    it('affiche les erreurs de validation spécifiques', () => {
        useForm.mockReturnValue({
            petit_trajet_seuil_km: 100,
            priorite_petit_trajet: ['', '', '', ''],
            priorite_long_trajet: ['', '', '', ''],
            errors: { petit_trajet_seuil_km: 'Le seuil est trop élevé' },
            processing: false
        });

        const wrapper = mount(VehicleSuggestion, {
            props: { setting: mockSetting, energies: mockEnergies, can: { edit: true } }
        });

        expect(wrapper.text()).toContain('Le seuil est trop élevé');
    });

    it('vérifie que les options des selects sont bien traduites', () => {
        useForm.mockReturnValue({
            priorite_petit_trajet: ['electrique', '', '', ''],
            priorite_long_trajet: ['', '', '', ''],
            errors: {},
            processing: false
        });

        const wrapper = mount(VehicleSuggestion, {
            props: { setting: mockSetting, energies: ['electrique'], can: { edit: true } }
        });

        const option = wrapper.find('option[value="electrique"]');
        expect(option.text()).toBe('Électrique');
    });
});
