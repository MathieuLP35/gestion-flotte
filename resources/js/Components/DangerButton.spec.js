import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DangerButton from '@/Components/DangerButton.vue';

describe('DangerButton', () => {
    it('renders the slot content', () => {
        const wrapper = mount(DangerButton, {
            slots: { default: 'Supprimer' },
        });

        expect(wrapper.text()).toBe('Supprimer');
        expect(wrapper.find('button').exists()).toBe(true);
    });

    it('applies red/danger styling', () => {
        const wrapper = mount(DangerButton, {
            slots: { default: 'OK' },
        });

        const btn = wrapper.find('button');
        expect(btn.classes()).toContain('bg-red-600');
        expect(btn.classes()).toContain('text-white');
    });
});
