import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import RolesIndex from '@/Pages/Admin/Roles/Index.vue';
import { usePage } from '@inertiajs/vue3';

describe('Admin/Roles/Index', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    global.confirm = vi.fn();
  });

  it('renders title and table', () => {
    vi.mocked(usePage).mockReturnValue({
      props: { auth: { permissions: [] }, translations: {} },
    });

    const wrapper = mount(RolesIndex, {
      props: { roles: [] },
      global: { stubs: { Head: true, Link: true } },
    });
    expect(wrapper.text()).toContain('Gestion des Rôles');
    expect(wrapper.find('table').exists()).toBe(true);
  });

  it('renders roles and permissions', () => {
    vi.mocked(usePage).mockReturnValue({
      props: { auth: { permissions: ['roles.edit', 'roles.delete'] }, translations: { 'permissions.vehicles.manage': 'Gérer véhicules' } },
    });

    const wrapper = mount(RolesIndex, {
      props: {
        roles: [
          { id: 1, name: 'Admin', permissions: [{ id: 1, name: 'vehicles.manage' }] },
          { id: 2, name: 'Super Admin', permissions: [] },
        ],
      },
      global: { stubs: { Head: true, Link: true } },
    });
    expect(wrapper.text()).toContain('Admin');
    expect(wrapper.text()).toContain('Super Admin');
  });

  it('shows Aucun rôle when empty', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { permissions: [] }, translations: {} } });

    const wrapper = mount(RolesIndex, {
      props: { roles: [] },
      global: { stubs: { Head: true, Link: true } },
    });
    expect(wrapper.text()).toContain('Aucun rôle trouvé');
  });
});
