import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import SecondaryButton from '@/Components/SecondaryButton.vue';

describe('SecondaryButton', () => {
  it('renders the default slot content', () => {
    const wrapper = mount(SecondaryButton, {
      slots: { default: 'Annuler' },
    });
    expect(wrapper.text()).toBe('Annuler');
    expect(wrapper.find('button').exists()).toBe(true);
  });

  it('renders as type="button" by default', () => {
    const wrapper = mount(SecondaryButton, { slots: { default: 'OK' } });
    expect(wrapper.find('button').attributes('type')).toBe('button');
  });

  it('renders as type="submit" when prop type is submit', () => {
    const wrapper = mount(SecondaryButton, {
      slots: { default: 'OK' },
      props: { type: 'submit' },
    });
    expect(wrapper.find('button').attributes('type')).toBe('submit');
  });

  it('has expected secondary button classes', () => {
    const wrapper = mount(SecondaryButton, { slots: { default: 'OK' } });
    const btn = wrapper.find('button');
    expect(btn.attributes('class')).toContain('border-gray-300');
    expect(btn.attributes('class')).toContain('bg-white');
  });
});
