import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import UsersEdit from '@/Pages/Admin/Users/Edit.vue';

describe('Admin/Users/Edit', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  it('renders title and form', () => {
    const wrapper = mount(UsersEdit, {
      props: {
        user: { id: 1, name: 'Alice', email: 'alice@test.com', agence_id: 1, roles: [] },
        agences: [{ id: 1, nom: 'Agence Paris' }, { id: 2, nom: 'Agence Lyon' }],
        roles: [{ id: 1, name: 'Super Admin' }, { id: 2, name: 'Editeur' }],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Modification de l\'utilisateur');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#name').exists()).toBe(true);
    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.find('select#agence').exists()).toBe(true);
    expect(wrapper.find('select#role').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Enregistrer');
  });

  it('shows agence options', () => {
    const wrapper = mount(UsersEdit, {
      props: {
        user: { id: 1, name: 'A', email: 'a@a.com', agence_id: null, roles: [] },
        agences: [{ id: 1, nom: 'Agence 1' }],
        roles: [],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Aucune agence');
    expect(wrapper.text()).toContain('Agence 1');
  });

  it('shows role options and selects current role', () => {
    const wrapper = mount(UsersEdit, {
      props: {
        user: { id: 1, name: 'A', email: 'a@a.com', agence_id: null, roles: [{ id: 2, name: 'Editeur' }] },
        agences: [],
        roles: [{ id: 1, name: 'Super Admin' }, { id: 2, name: 'Editeur' }],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Aucun rôle');
    expect(wrapper.text()).toContain('Super Admin');
    expect(wrapper.text()).toContain('Editeur');
    expect(wrapper.find('select#role').element.value).toBe('2');
  });
});
