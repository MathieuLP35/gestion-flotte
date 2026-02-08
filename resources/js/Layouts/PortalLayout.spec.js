import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

describe('PortalLayout', () => {
  beforeEach(() => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: null } } });
  });

  afterEach(() => {
    vi.mocked(usePage).mockReset();
  });

  it('renders header with logo, login and register links when guest', () => {
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
