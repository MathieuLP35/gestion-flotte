import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import PortalLayout from '@/Layouts/PortalLayout.vue';

describe('PortalLayout', () => {
  it('renders header with logo, login and register links', () => {
    const wrapper = mount(PortalLayout, {
      slots: { default: '<div>Contenu principal</div>' },
    });

    expect(wrapper.find('a[href="/"]').exists()).toBe(true);
    expect(wrapper.find('a[href="/login"]').exists()).toBe(true);
    expect(wrapper.find('a[href="/register"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Se connecter');
    expect(wrapper.text()).toContain("S'inscrire");
  });

  it('renders the default slot in main', () => {
    const wrapper = mount(PortalLayout, {
      slots: { default: '<h1>Bienvenue</h1>' },
    });
    expect(wrapper.text()).toContain('Bienvenue');
  });

  it('renders footer with terms and privacy links', () => {
    const wrapper = mount(PortalLayout, { slots: { default: '' } });
    expect(wrapper.find('a[href="/terms"]').exists()).toBe(true);
    expect(wrapper.find('a[href="/privacy"]').exists()).toBe(true);
    expect(wrapper.text()).toContain("Conditions d'utilisation");
    expect(wrapper.text()).toContain('Politique de confidentialité');
    expect(wrapper.text()).toContain('© 2025 SparkOtto. Tous droits réservés.');
  });
});
