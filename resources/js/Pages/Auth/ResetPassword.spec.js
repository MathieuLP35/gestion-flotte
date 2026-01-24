import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ResetPassword from '@/Pages/Auth/ResetPassword.vue';

describe('ResetPassword', () => {
  it('renders form with email, password, password_confirmation and submit button', () => {
    const wrapper = mount(ResetPassword, {
      props: { email: 'user@example.com', token: 'abc123' },
    });

    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.find('input#password').exists()).toBe(true);
    expect(wrapper.find('input#password_confirmation').exists()).toBe(true);
    expect(wrapper.text()).toContain('Reset Password');
  });

  it('uses GuestLayout', () => {
    const wrapper = mount(ResetPassword, {
      props: { email: 'u@u.com', token: 't' },
    });
    expect(wrapper.findComponent({ name: 'GuestLayout' }).exists()).toBe(true);
  });

  it('prefills email from props', async () => {
    const wrapper = mount(ResetPassword, {
      props: { email: 'test@test.com', token: 'tk' },
    });
    expect(wrapper.find('input#email').element.value).toBe('test@test.com');
  });
});
