import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Index from '@/Pages/Admin/Agences/Index.vue'; // Ajuste le chemin si nécessaire
import { router, usePage } from '@inertiajs/vue3';

// Mock d'Inertia
vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: { template: '<a><slot /></a>' },
    router: { delete: vi.fn() },
    usePage: vi.fn(),
}));

// Mock de Ziggy (route())
global.route = vi.fn((name) => name);

describe('Agences Index Page', () => {
    const mockAgences = [
        { id: 1, nom: 'Agence Paris', adresse: 'Rue de Rivoli', vehicles_count: 5, users_count: 2 },
        { id: 2, nom: 'Agence Lyon', adresse: null, vehicles_count: 0, users_count: 0 },
    ];

    beforeEach(() => {
        vi.clearAllMocks();
        // Configuration par défaut de usePage
        usePage.mockReturnValue({
            props: { flash: { success: null, error: null } }
        });
    });

    it('affiche la liste des agences correctement', () => {
        const wrapper = mount(Index, {
            props: { agences: mockAgences }
        });

        expect(wrapper.text()).toContain('Agence Paris');
        expect(wrapper.text()).toContain('Agence Lyon');
        expect(wrapper.text()).toContain('Rue de Rivoli');
        expect(wrapper.text()).toContain('5'); // vehicles_count
    });

    it('affiche un message quand la liste est vide', () => {
        const wrapper = mount(Index, {
            props: { agences: [] }
        });
        expect(wrapper.text()).toContain('Aucune agence.');
    });

    it('affiche les messages flash de succès', () => {
        usePage.mockReturnValue({
            props: { flash: { success: 'Agence créée avec succès !' } }
        });

        const wrapper = mount(Index, {
            props: { agences: [] }
        });

        expect(wrapper.find('.bg-green-50').text()).toBe('Agence créée avec succès !');
    });

    it('affiche les messages flash d\'erreur', () => {
        usePage.mockReturnValue({
            props: { flash: { error: 'Erreur lors de la suppression.' } }
        });

        const wrapper = mount(Index, {
            props: { agences: [] }
        });

        expect(wrapper.find('.bg-red-50').text()).toBe('Erreur lors de la suppression.');
    });

    it('désactive le bouton supprimer si l\'agence n\'est pas vide', () => {
        const wrapper = mount(Index, {
            props: { agences: [mockAgences[0]] } // Agence Paris (5 véhicules)
        });

        const deleteBtn = wrapper.find('button');
        expect(deleteBtn.element.disabled).toBe(true);
        expect(deleteBtn.attributes('title')).toContain('Agence avec véhicules');
    });

    it('appelle router.delete quand on confirme la suppression d\'une agence vide', async () => {
        // Mock du window.confirm
        const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);

        const wrapper = mount(Index, {
            props: { agences: [mockAgences[1]] } // Agence Lyon (vide)
        });

        const deleteBtn = wrapper.find('button');
        await deleteBtn.trigger('click');

        expect(confirmSpy).toHaveBeenCalled();
        expect(router.delete).toHaveBeenCalledWith('admin.agences.destroy', expect.any(Object));
    });

    it('n\'appelle pas router.delete si l\'utilisateur annule la confirmation', async () => {
        vi.spyOn(window, 'confirm').mockReturnValue(false);

        const wrapper = mount(Index, {
            props: { agences: [mockAgences[1]] }
        });

        await wrapper.find('button').trigger('click');
        expect(router.delete).not.toHaveBeenCalled();
    });
});
