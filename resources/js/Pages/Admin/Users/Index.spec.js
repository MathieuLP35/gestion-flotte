import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import UsersIndex from '@/Pages/Admin/Users/Index.vue';
import { usePage } from '@inertiajs/vue3';

describe('Admin/Users/Index', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    global.confirm = vi.fn();
  });

  it('renders title and table', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { permissions: [] } } });

    const wrapper = mount(UsersIndex, {
      props: { users: [] },
      global: { stubs: { Head: true, Link: true, AdminLayout: { template: '<div><slot /></div>' } } },
    });
    expect(wrapper.text()).toContain('Gestion des Utilisateurs');
    expect(wrapper.find('table').exists()).toBe(true);
  });

  it('renders users with agence and roles', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { permissions: ['users.edit', 'users.delete'] } } });

    const wrapper = mount(UsersIndex, {
      props: {
        users: [
          { id: 1, name: 'Alice', email: 'alice@test.com', agence: { nom: 'Agence Paris' }, roles: [{ name: 'Admin' }] },
          { id: 2, name: 'Bob', email: 'bob@test.com', agence: null, roles: [] },
        ],
      },
      global: { stubs: { Head: true, Link: true, AdminLayout: { template: '<div><slot /></div>' } } },
    });
    expect(wrapper.text()).toContain('Alice');
    expect(wrapper.text()).toContain('alice@test.com');
    expect(wrapper.text()).toContain('Agence Paris');
    expect(wrapper.text()).toContain('Bob');
    expect(wrapper.text()).toContain('Aucune agence');
  });

  it('shows Aucun utilisateur when empty', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { permissions: [] } } });

    const wrapper = mount(UsersIndex, {
      props: { users: [] },
      global: { stubs: { Head: true, Link: true, AdminLayout: { template: '<div><slot /></div>' } } },
    });
    expect(wrapper.text()).toContain('Aucun utilisateur trouvé');
  });
});
