import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import PrimaryButton from '@/Components/PrimaryButton.vue';

describe('PrimaryButton', () => {
    it('renders the default slot content', () => {
        const wrapper = mount(PrimaryButton, {
            slots: { default: 'Valider' },
        });

        expect(wrapper.text()).toBe('Valider');
        expect(wrapper.find('button').exists()).toBe(true);
    });

    it('renders as a button element with expected classes', () => {
        const wrapper = mount(PrimaryButton, {
            slots: { default: 'OK' },
        });

        const btn = wrapper.find('button');
        expect(btn.attributes('class')).toContain('bg-gray-800');
        expect(btn.attributes('class')).toContain('text-white');
    });
});
