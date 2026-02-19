import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import Edit from '@/Pages/Admin/Agences/Edit.vue';
import { useForm } from '@inertiajs/vue3';

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: {
        template: '<a :href="href"><slot /></a>',
        props: ['href']
    },
    useForm: vi.fn(),
}));

global.route = vi.fn((name) => name);

describe('Agences Edit Page', () => {
    const mockAgence = { id: 42, nom: 'Agence Bretagne', adresse: 'Rennes' };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('initialise le formulaire avec les données de l\'agence', () => {
        useForm.mockReturnValue({ nom: 'Agence Bretagne', adresse: 'Rennes', errors: {}, processing: false });
        mount(Edit, { props: { agence: mockAgence } });

        expect(useForm).toHaveBeenCalledWith({
            nom: 'Agence Bretagne',
            adresse: 'Rennes',
        });
    });

    it('gère les adresses nulles en initialisant une chaîne vide', () => {
        useForm.mockReturnValue({ nom: 'Test', adresse: '', errors: {}, processing: false });
        mount(Edit, { props: { agence: { id: 1, nom: 'Test', adresse: null } } });

        expect(useForm).toHaveBeenCalledWith({ nom: 'Test', adresse: '' });
    });

    it('soumet le formulaire avec les bonnes données', async () => {
        const putMock = vi.fn();
        useForm.mockReturnValue({ ...mockAgence, errors: {}, processing: false, put: putMock });

        const wrapper = mount(Edit, { props: { agence: mockAgence } });
        await wrapper.find('form').trigger('submit.prevent');

        expect(putMock).toHaveBeenCalled();
    });

    it('affiche les erreurs de validation', () => {
        useForm.mockReturnValue({
            nom: '', adresse: '',
            errors: { nom: 'Le nom est obligatoire' },
            processing: false
        });

        const wrapper = mount(Edit, { props: { agence: mockAgence } });
        expect(wrapper.text()).toContain('Le nom est obligatoire');
    });

    it('désactive le bouton de validation pendant le traitement', async () => {
        const form = reactive({
            nom: 'Test', adresse: 'Test',
            errors: {},
            processing: true,
            put: vi.fn(),
        });
        useForm.mockReturnValue(form);

        const wrapper = mount(Edit, { props: { agence: mockAgence } });
        const submitBtn = wrapper.find('button[type="submit"]');

        expect(submitBtn.element.disabled).toBe(true);
        expect(submitBtn.attributes('class')).toContain('opacity-50');
    });

    it('possède un lien pour revenir à la liste', () => {
        useForm.mockReturnValue({ nom: '', adresse: '', errors: {}, processing: false });

        const wrapper = mount(Edit, {
            props: { agence: mockAgence }
        });

        const cancelLink = wrapper.find('a');

        expect(cancelLink.attributes('href')).toBe('/r/admin/agences/index');
    });
});
