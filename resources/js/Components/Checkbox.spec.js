import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Checkbox from '@/Components/Checkbox.vue';

describe('Checkbox', () => {
    it('renders a checkbox input', () => {
        const wrapper = mount(Checkbox, {
            props: { checked: false },
        });

        expect(wrapper.find('input[type="checkbox"]').exists()).toBe(true);
    });

    it('uses checked prop as model', () => {
        const wrapper = mount(Checkbox, {
            props: { checked: true },
        });

        expect(wrapper.find('input').element.checked).toBe(true);
    });

    it('emits update:checked when toggled', async () => {
        const wrapper = mount(Checkbox, {
            props: { checked: false },
        });

        await wrapper.find('input').setValue(true);

        expect(wrapper.emitted('update:checked')).toBeTruthy();
        expect(wrapper.emitted('update:checked')[0]).toEqual([true]);
    });

    it('binds the value attribute when value prop is provided', () => {
        const wrapper = mount(Checkbox, {
            props: { checked: [], value: 'option-a' },
        });

        expect(wrapper.find('input').attributes('value')).toBe('option-a');
    });

    it('applies indigo focus ring classes', () => {
        const wrapper = mount(Checkbox, {
            props: { checked: false },
        });

        expect(wrapper.find('input').classes()).toContain('focus:ring-indigo-500');
    });
});
