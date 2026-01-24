import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Login from '@/Pages/Auth/Login.vue';

describe('Login', () => {
  it('renders form with email, password and login button', () => {
    const wrapper = mount(Login);

    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.find('input#password').exists()).toBe(true);
    expect(wrapper.find('form').find('button').exists()).toBe(true);
    expect(wrapper.text()).toContain('Log in');
  });

  it('uses GuestLayout', () => {
    const wrapper = mount(Login);
    expect(wrapper.findComponent({ name: 'GuestLayout' }).exists()).toBe(true);
  });

  it('shows Forgot your password link when canResetPassword is true', () => {
    const wrapper = mount(Login, { props: { canResetPassword: true } });
    expect(wrapper.text()).toContain('Forgot your password?');
    expect(wrapper.find('a[href="/r/password/request"]').exists()).toBe(true);
  });

  it('hides Forgot your password link when canResetPassword is false', () => {
    const wrapper = mount(Login, { props: { canResetPassword: false } });
    expect(wrapper.text()).not.toContain('Forgot your password?');
  });

  it('renders Head with title Log in', () => {
    const wrapper = mount(Login);
    expect(wrapper.findComponent({ name: 'Head' }).exists()).toBe(true);
  });
});
