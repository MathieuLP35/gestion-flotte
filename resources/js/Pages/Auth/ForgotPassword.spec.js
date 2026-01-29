import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ForgotPassword from '@/Pages/Auth/ForgotPassword.vue';

describe('ForgotPassword', () => {
  it('renders form with email and submit button', () => {
    const wrapper = mount(ForgotPassword);

    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.text()).toContain('Réinitialiser le mot de passe');
    expect(wrapper.text()).toContain('Saisissez votre adresse e-mail pour recevoir un lien de réinitialisation');
  });

  it('uses GuestLayout', () => {
    const wrapper = mount(ForgotPassword);
    expect(wrapper.findComponent({ name: 'GuestLayout' }).exists()).toBe(true);
  });

  it('shows status message when status prop is set', () => {
    const wrapper = mount(ForgotPassword, {
      props: { status: 'Un lien de réinitialisation a été envoyé par e-mail.' },
    });
    expect(wrapper.text()).toContain('Un lien de réinitialisation a été envoyé par e-mail.');
  });
});
