import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import Create from '@/Pages/Admin/Agences/Create.vue';
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

describe('Agences Create Page', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('initialise le formulaire avec des champs vides', () => {
        useForm.mockReturnValue({ nom: '', adresse: '', errors: {}, processing: false });
        mount(Create);

        expect(useForm).toHaveBeenCalledWith({ nom: '', adresse: '' });
    });

    it('affiche le titre correct', () => {
        useForm.mockReturnValue({ nom: '', adresse: '', errors: {}, processing: false });
        const wrapper = mount(Create);
        expect(wrapper.find('h1').text()).toBe('Créer une agence');
    });

    it('soumet le formulaire via la méthode POST', async () => {
        const postMock = vi.fn();
        useForm.mockReturnValue({ nom: 'New', adresse: 'Add', errors: {}, processing: false, post: postMock });

        const wrapper = mount(Create);
        await wrapper.find('form').trigger('submit.prevent');

        expect(postMock).toHaveBeenCalled();
    });

    it('affiche les erreurs de validation retournées par le serveur', () => {
        useForm.mockReturnValue({
            nom: '', adresse: '',
            errors: { nom: 'Le nom est requis' },
            processing: false
        });

        const wrapper = mount(Create);
        expect(wrapper.text()).toContain('Le nom est requis');
    });

    it('désactive le bouton de création pendant le chargement', async () => {
        const form = reactive({
            nom: '', adresse: '',
            errors: {},
            processing: true,
            post: vi.fn(),
        });
        useForm.mockReturnValue(form);

        const wrapper = mount(Create);
        const submitBtn = wrapper.find('button[type="submit"]');

        expect(submitBtn.element.disabled).toBe(true);
        expect(submitBtn.attributes('class')).toContain('opacity-50');
    });

    it('possède un lien pour revenir à la liste', () => {
        useForm.mockReturnValue({ nom: '', adresse: '', errors: {}, processing: false });
        const wrapper = mount(Create);

        const cancelLink = wrapper.find('a'); // Il n'y a qu'un seul lien <a> "Annuler"

        // On teste la valeur brute reçue par l'erreur précédemment
        expect(cancelLink.attributes('href')).toBe('/r/admin/agences/index');
    });
});
