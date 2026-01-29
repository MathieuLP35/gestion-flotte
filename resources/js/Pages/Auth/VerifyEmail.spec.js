import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import VerifyEmail from '@/Pages/Auth/VerifyEmail.vue';

describe('VerifyEmail', () => {
  it('renders verification message and Resend button', () => {
    const wrapper = mount(VerifyEmail);

    expect(wrapper.text()).toContain('Merci de vous être inscrit !');
    expect(wrapper.text()).toContain('Renvoyer l\'e-mail de vérification');
    expect(wrapper.text()).toContain('Déconnexion');
  });

  it('uses GuestLayout', () => {
    const wrapper = mount(VerifyEmail);
    expect(wrapper.findComponent({ name: 'GuestLayout' }).exists()).toBe(true);
  });

  it('shows verification-link-sent message when status is verification-link-sent', () => {
    const wrapper = mount(VerifyEmail, {
      props: { status: 'verification-link-sent' },
    });
    expect(wrapper.text()).toContain('Un nouveau lien de vérification vient d\'être envoyé');
  });
});
