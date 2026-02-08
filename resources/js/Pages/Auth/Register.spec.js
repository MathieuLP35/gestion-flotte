import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Register from '@/Pages/Auth/Register.vue';

describe('Register', () => {
  it('renders form with name, email, password, password_confirmation and register button', () => {
    const wrapper = mount(Register);

    expect(wrapper.find('input#name').exists()).toBe(true);
    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.find('input#password').exists()).toBe(true);
    expect(wrapper.find('input#password_confirmation').exists()).toBe(true);
    expect(wrapper.text()).toContain("S'inscrire");
  });

  it('uses GuestLayout', () => {
    const wrapper = mount(Register);
    expect(wrapper.findComponent({ name: 'GuestLayout' }).exists()).toBe(true);
  });

  it('shows link to login (Already registered?)', () => {
    const wrapper = mount(Register);
    expect(wrapper.text()).toContain('Déjà inscrit ?');
    expect(wrapper.find('a[href="/r/login"]').exists()).toBe(true);
  });
});
