import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AgencesEdit from '@/Pages/Admin/Agences/Edit.vue';

describe('Admin/Agences/Edit', () => {
  const stubLayout = { template: '<div><slot /></div>' };
  const agence = { id: 1, nom: 'Agence Paris', adresse: '10 rue de Paris' };

  it('renders title and form with agence data', () => {
    const wrapper = mount(AgencesEdit, {
      props: { agence },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Modifier l\'agence');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#nom').element.value).toBe('Agence Paris');
    expect(wrapper.find('input#adresse').element.value).toBe('10 rue de Paris');
    expect(wrapper.find('button[type="submit"]').text()).toContain('Enregistrer');
  });

  it('renders Annuler link', () => {
    const wrapper = mount(AgencesEdit, {
      props: { agence },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Annuler');
  });

  it('handles agence with null adresse', () => {
    const wrapper = mount(AgencesEdit, {
      props: { agence: { id: 2, nom: 'Agence Lyon', adresse: null } },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('input#nom').element.value).toBe('Agence Lyon');
    expect(wrapper.find('input#adresse').element.value).toBe('');
  });
});
