import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import RolesCreate from '@/Pages/Admin/Roles/Create.vue';

describe('Admin/Roles/Create', () => {
  it('renders title and form', () => {
    vi.mocked(usePage).mockReturnValue({ props: { translations: {} } });

    const wrapper = mount(RolesCreate, {
      props: { permissions: ['vehicles.manage', 'users.manage'] },
      global: { stubs: { Head: true, AdminLayout: { template: '<div><slot /></div>' } } },
    });
    expect(wrapper.text()).toContain('Créer un nouveau Rôle');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#name').exists()).toBe(true);
    expect(wrapper.findAll('input[type="checkbox"]').length).toBeGreaterThanOrEqual(2);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Enregistrer');
  });
});
