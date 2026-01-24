import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import GuestLayout from '@/Layouts/GuestLayout.vue';

describe('GuestLayout', () => {
  it('renders the layout with logo, slot and footer', () => {
    const wrapper = mount(GuestLayout, {
      slots: { default: '<p>Formulaire de connexion</p>' },
    });

    expect(wrapper.find('a[href="/"]').exists()).toBe(true);
    expect(wrapper.find('.min-h-screen').exists()).toBe(true);
    expect(wrapper.text()).toContain('Formulaire de connexion');
    expect(wrapper.text()).toContain('© 2025 SparkOtto. Tous droits réservés.');
  });

  it('renders ApplicationLogo (img) inside the home link', () => {
    const wrapper = mount(GuestLayout, { slots: { default: '<span />' } });
    const link = wrapper.find('a[href="/"]');
    expect(link.exists()).toBe(true);
    expect(link.find('img').exists()).toBe(true);
  });

  it('renders the main content container with max-w-md', () => {
    const wrapper = mount(GuestLayout, { slots: { default: '<div>Contenu</div>' } });
    expect(wrapper.find('.bg-white.rounded-xl.shadow-lg').exists()).toBe(true);
    expect(wrapper.text()).toContain('Contenu');
  });
});
