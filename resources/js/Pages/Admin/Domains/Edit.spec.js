import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import Edit from '@/Pages/Admin/Domains/Edit.vue';
import { useForm } from '@inertiajs/vue3';

// Mock d'Inertia
vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: {
        template: '<a :href="href"><slot /></a>',
        props: ['href']
    },
    useForm: vi.fn(),
}));

// Mock de Ziggy
global.route = vi.fn((name) => name);

describe('Domains Edit Page', () => {
    const mockDomain = {
        id: 1,
        name: 'gmail.com',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('initialise le formulaire avec les données du domaine', () => {
        useForm.mockReturnValue({
            name: 'gmail.com',
            errors: {},
            processing: false
        });

        mount(Edit, {
            props: { domain: mockDomain }
        });

        expect(useForm).toHaveBeenCalledWith({
            name: 'gmail.com',
        });
    });

    it('soumet le formulaire avec la méthode PUT', async () => {
        const putMock = vi.fn();
        useForm.mockReturnValue({
            name: 'gmail.com',
            errors: {},
            processing: false,
            put: putMock
        });

        const wrapper = mount(Edit, {
            props: { domain: mockDomain }
        });

        await wrapper.find('form').trigger('submit.prevent');

        expect(putMock).toHaveBeenCalled();
    });

    it('affiche les erreurs de validation du champ nom', () => {
        useForm.mockReturnValue({
            name: 'gmail',
            errors: { name: 'Le format du domaine est invalide' },
            processing: false
        });

        const wrapper = mount(Edit, {
            props: { domain: mockDomain }
        });

        expect(wrapper.text()).toContain('Le format du domaine est invalide');
    });

    it('désactive le bouton enregistrer pendant le traitement', async () => {
        const form = reactive({
            name: 'gmail.com',
            errors: {},
            processing: true,
            put: vi.fn(),
        });
        useForm.mockReturnValue(form);

        const wrapper = mount(Edit, {
            props: { domain: mockDomain }
        });

        const submitBtn = wrapper.find('button[type="submit"]');

        expect(submitBtn.element.disabled).toBe(true);
        expect(submitBtn.attributes('class')).toContain('opacity-50');
    });

    it('possède un lien pour revenir à la liste des domaines', () => {
        useForm.mockReturnValue({ name: '', errors: {}, processing: false });

        const wrapper = mount(Edit, {
            props: { domain: mockDomain }
        });

        const cancelLink = wrapper.find('a');

        expect(cancelLink.attributes('href')).toBe('/r/admin/domains/index');
    });
});
