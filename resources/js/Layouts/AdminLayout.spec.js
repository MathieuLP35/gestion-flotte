import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const pageProps = {
  props: {
    auth: {
      user: { name: 'Admin Test', email: 'admin@example.com' },
      roles: [],
    },
  },
};

describe('AdminLayout', () => {
  it('renders nav with admin dashboard, roles, vehicles, users, domains', () => {
    const wrapper = mount(AdminLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '<p>Admin content</p>' },
    });

    expect(wrapper.find('a[href="/r/admin/dashboard"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Tableau de bord');
    expect(wrapper.text()).toContain('Rôles');
    expect(wrapper.text()).toContain('Véhicules');
    expect(wrapper.text()).toContain('Disponibilités');
    expect(wrapper.text()).toContain('Utilisateurs');
    expect(wrapper.text()).toContain('Domaines');
  });

  it('renders link back to user site (Utilisateur) and user name', () => {
    const wrapper = mount(AdminLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '' },
    });
    expect(wrapper.find('a[href="/r/dashboard"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Utilisateur');
    expect(wrapper.text()).toContain('Admin Test');
  });

  it('renders Profil and Déconnexion in dropdown', () => {
    const wrapper = mount(AdminLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '' },
    });
    expect(wrapper.text()).toContain('Profil');
    expect(wrapper.text()).toContain('Déconnexion');
  });

  it('renders optional header slot when provided', () => {
    const wrapper = mount(AdminLayout, {
      global: { mocks: { $page: pageProps } },
      slots: {
        default: '<div>Main</div>',
        header: '<h2>En-tête admin</h2>',
      },
    });
    expect(wrapper.text()).toContain('En-tête admin');
    expect(wrapper.text()).toContain('Main');
  });

  it('renders footer with terms and privacy', () => {
    const wrapper = mount(AdminLayout, {
      global: { mocks: { $page: pageProps } },
      slots: { default: '' },
    });
    expect(wrapper.find('a[href="/terms"]').exists()).toBe(true);
    expect(wrapper.find('a[href="/privacy"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('© 2025 SparkOtto. Tous droits réservés.');
  });
});
