import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import RolesEdit from '@/Pages/Admin/Roles/Edit.vue';

describe('Admin/Roles/Edit', () => {
  it('renders title and form', () => {
    vi.mocked(usePage).mockReturnValue({ props: { translations: {} } });

    const wrapper = mount(RolesEdit, {
      props: {
        // Ajout de la clé "data" ici pour correspondre à ton API Resource
        role: { 
          data: { id: 1, name: 'Manager', permissions: ['vehicles.manage'] } 
        },
        permissions: ['vehicles.manage', 'users.manage'],
      },
      global: { 
        stubs: { 
          Head: true, 
          Link: true, 
          AdminLayout: { template: '<div><slot /></div>' } 
        } 
      },
    });

    expect(wrapper.text()).toContain('Modifier le rôle');
    expect(wrapper.find('form').exists()).toBe(true);
    // Maintenant cet expect passera car form.name sera 'Manager'
    expect(wrapper.find('input#name').element.value).toBe('Manager');
  });
});