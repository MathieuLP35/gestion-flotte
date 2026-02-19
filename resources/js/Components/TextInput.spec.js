import { describe, it, expect, vi } from 'vitest';
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
        expect(wrapper.emitted('update:modelValue')[0]).toEqual(['nouvelle valeur']);
    });

    it('exposes a focus method', async () => {
        const wrapper = mount(TextInput, {
            props: { modelValue: '' },
            attachTo: document.body
        });

        const inputEl = wrapper.find('input').element;
        const focusSpy = vi.spyOn(inputEl, 'focus');

        // On cherche la méthode focus soit sur vm, soit dans les propriétés exposées
        const focusMethod = wrapper.vm.focus || wrapper.vm.$.exposed?.focus;

        if (typeof focusMethod !== 'function') {
            throw new Error('La méthode focus n’est pas exposée sur le composant');
        }

        await focusMethod();

        expect(focusSpy).toHaveBeenCalled();

        wrapper.unmount();
    });
});
