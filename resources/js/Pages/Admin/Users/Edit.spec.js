import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import UsersEdit from '@/Pages/Admin/Users/Edit.vue';

describe('Admin/Users/Edit', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  it('renders title and form', () => {
    const wrapper = mount(UsersEdit, {
      props: {
        user: { id: 1, name: 'Alice', email: 'alice@test.com', agence_id: 1 },
        agences: [{ id: 1, nom: 'Agence Paris' }, { id: 2, nom: 'Agence Lyon' }],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Modifification de l\'utilisateur');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#name').exists()).toBe(true);
    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.find('select#agence').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Enregistrer');
  });

  it('shows agence options', () => {
    const wrapper = mount(UsersEdit, {
      props: {
        user: { id: 1, name: 'A', email: 'a@a.com', agence_id: null },
        agences: [{ id: 1, nom: 'Agence 1' }],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Aucune agence');
    expect(wrapper.text()).toContain('Agence 1');
  });
});
