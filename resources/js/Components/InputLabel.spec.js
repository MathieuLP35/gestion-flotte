import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import InputLabel from '@/Components/InputLabel.vue';

describe('InputLabel', () => {
  it('renders value prop when provided', () => {
    const wrapper = mount(InputLabel, {
      props: { value: 'Nom' },
    });
    expect(wrapper.text()).toBe('Nom');
    expect(wrapper.find('span').exists()).toBe(true);
  });

  it('renders slot when value is not provided', () => {
    const wrapper = mount(InputLabel, {
      slots: { default: 'Label from slot' },
    });
    expect(wrapper.text()).toBe('Label from slot');
  });

  it('has label and text-gray-700 classes', () => {
    const wrapper = mount(InputLabel, {
      props: { value: 'Email' },
    });
    expect(wrapper.find('label').classes()).toContain('block');
    expect(wrapper.find('label').classes()).toContain('text-gray-700');
  });
});
