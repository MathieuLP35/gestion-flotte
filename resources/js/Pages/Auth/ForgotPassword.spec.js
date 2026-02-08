import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ForgotPassword from '@/Pages/Auth/ForgotPassword.vue';

describe('ForgotPassword', () => {
  it('renders form with email and submit button', () => {
    const wrapper = mount(ForgotPassword);

    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.text()).toContain('Email Password Reset Link');
    expect(wrapper.text()).toContain('Forgot your password? No problem.');
  });

  it('uses GuestLayout', () => {
    const wrapper = mount(ForgotPassword);
    expect(wrapper.findComponent({ name: 'GuestLayout' }).exists()).toBe(true);
  });

  it('shows status message when status prop is set', () => {
    const wrapper = mount(ForgotPassword, {
      props: { status: 'We have emailed your password reset link.' },
    });
    expect(wrapper.text()).toContain('We have emailed your password reset link.');
  });
});
