import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const pageProps = {
  props: {
    auth: {
      user: { name: 'Jean Dupont', email: 'jean@example.com' },
      roles: [],
      permissions: [],
    },
  },
};

describe('AuthenticatedLayout', () => {
  it('renders nav with dashboard link and user name', () => {
    const wrapper = mount(AuthenticatedLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '<p>Contenu</p>' },
    });

    expect(wrapper.find('a[href="/r/dashboard"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Dashboard');
    expect(wrapper.text()).toContain('Jean Dupont');
    expect(wrapper.text()).toContain('Contenu');
  });

  it('renders Profile and Log Out in dropdown area', () => {
    const wrapper = mount(AuthenticatedLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '' },
    });
    expect(wrapper.text()).toContain('Profil');
    expect(wrapper.text()).toContain('Déconnexion');
  });

  it('renders optional header slot when provided', () => {
    const wrapper = mount(AuthenticatedLayout, {
      global: { mocks: { $page: pageProps } },
      slots: {
        default: '<p>Body</p>',
        header: '<h1>Titre de la page</h1>',
      },
    });
    expect(wrapper.text()).toContain('Titre de la page');
    expect(wrapper.text()).toContain('Body');
  });

  it('does not render header block when header slot is empty', () => {
    const wrapper = mount(AuthenticatedLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '' },
    });
    expect(wrapper.find('header.bg-white.shadow').exists()).toBe(false);
  });

  it('renders footer with terms and privacy links', () => {
    const wrapper = mount(AuthenticatedLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '' },
    });
    expect(wrapper.find('a[href="/terms"]').exists()).toBe(true);
    expect(wrapper.find('a[href="/privacy"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('© 2025 SparkOtto. Tous droits réservés.');
  });
});
