import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import InputError from '@/Components/InputError.vue';

describe('InputError', () => {
    it('shows the message when message prop is provided', () => {
        const wrapper = mount(InputError, {
            props: { message: 'Champ requis' },
        });

        expect(wrapper.text()).toBe('Champ requis');
        expect(wrapper.find('p').isVisible()).toBe(true);
    });

    it('hides the message when message is empty', () => {
        const wrapper = mount(InputError, {
            props: { message: '' },
        });

        expect(wrapper.find('p').exists()).toBe(true);
        expect(wrapper.find('div').isVisible()).toBe(false);
    });

    it('applies red error styling to the paragraph', () => {
        const wrapper = mount(InputError, {
            props: { message: 'Erreur' },
        });

        expect(wrapper.find('p').classes()).toContain('text-red-600');
    });
});
