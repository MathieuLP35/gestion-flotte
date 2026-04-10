import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const adminPermissions = [
  'admin.view', 'agences.view', 'roles.view', 'vehicles.view',
  'users.view', 'allowed_domains.view', 'vehicle_suggestion.view',
];

const pageProps = {
  props: { auth: { user: { name: 'Admin Test', email: 'admin@example.com' }, roles: [], permissions: adminPermissions } },
};

describe('AdminLayout', () => {
  beforeEach(() => {
    vi.mocked(usePage).mockReturnValue({
      props: { auth: { user: { name: 'Admin Test', email: 'admin@example.com' }, roles: [], permissions: adminPermissions } },
      url: '',
    });
  });

  it('renders nav with admin dashboard, agences, roles, vehicles, users, domains when user has all permissions', () => {
    const wrapper = mount(AdminLayout, {
      slots: { default: '<p>Admin content</p>' },
    });

    expect(wrapper.find('a[href="/r/admin/dashboard"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Tableau de bord');
    expect(wrapper.text()).toContain('Agences');
    expect(wrapper.text()).toContain('Rôles');
    expect(wrapper.text()).toContain('Véhicules');
    expect(wrapper.text()).toContain('Gestion');
    expect(wrapper.text()).toContain('Utilisateurs');
    expect(wrapper.text()).toContain('Domaines');
  });

  it('hides menu items when user lacks permissions', () => {
    vi.mocked(usePage).mockReturnValue({
      props: { auth: { user: { name: 'Guest', email: 'g@x.com' }, roles: [], permissions: ['admin.view'] } },
      url: '',
    });
    const wrapper = mount(AdminLayout, { slots: { default: '' } });
    expect(wrapper.text()).toContain('Tableau de bord');
    expect(wrapper.text()).not.toContain('Agences');
    expect(wrapper.text()).not.toContain('Rôles');
    expect(wrapper.text()).not.toContain('Utilisateurs');
    expect(wrapper.text()).not.toContain('Domaines');
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
    expect(wrapper.text()).toContain('© 2025 SPARKOTTO FLEET MANAGEMENT');
  });
});
