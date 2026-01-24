import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import TextInput from '@/Components/TextInput.vue';

describe('TextInput', () => {
    it('renders an input with the given model value', () => {
        const wrapper = mount(TextInput, {
            props: { modelValue: 'valeur initiale' },
        });

        expect(wrapper.find('input').element.value).toBe('valeur initiale');
    });

    it('emits update:modelValue when input value changes', async () => {
        const wrapper = mount(TextInput, {
            props: { modelValue: '' },
        });

        await wrapper.find('input').setValue('nouvelle valeur');

        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')).toHaveLength(1);
        expect(wrapper.emitted('update:modelValue')[0]).toEqual(['nouvelle valeur']);
    });

    it('exposes a focus method', async () => {
        const wrapper = mount(TextInput, {
            props: { modelValue: 'test' },
        });

        const inputEl = wrapper.find('input').element;
        const focusSpy = vi.spyOn(inputEl, 'focus');

        await wrapper.vm.focus();

        expect(focusSpy).toHaveBeenCalled();
    });
});
