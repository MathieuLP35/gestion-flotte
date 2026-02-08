import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ConfirmPassword from '@/Pages/Auth/ConfirmPassword.vue';

describe('ConfirmPassword', () => {
  it('renders form with password and Confirm button', () => {
    const wrapper = mount(ConfirmPassword);

    expect(wrapper.find('input#password').exists()).toBe(true);
    expect(wrapper.text()).toContain('Confirm');
    expect(wrapper.text()).toContain('Veuillez confirmer votre mot de passe');
  });

  it('uses GuestLayout', () => {
    const wrapper = mount(ConfirmPassword);
    expect(wrapper.findComponent({ name: 'GuestLayout' }).exists()).toBe(true);
  });
});
