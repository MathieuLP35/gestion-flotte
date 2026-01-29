import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AgencesCreate from '@/Pages/Admin/Agences/Create.vue';

describe('Admin/Agences/Create', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  it('renders title and form', () => {
    const wrapper = mount(AgencesCreate, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Créer une agence');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#nom').exists()).toBe(true);
    expect(wrapper.find('input#adresse').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Créer');
  });

  it('renders Annuler link to index', () => {
    const wrapper = mount(AgencesCreate, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Annuler');
    expect(wrapper.find('a[href*="agences"]').exists()).toBe(true);
  });

  it('binds nom and adresse to form', async () => {
    const wrapper = mount(AgencesCreate, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    await wrapper.find('input#nom').setValue('Nouvelle Agence');
    await wrapper.find('input#adresse').setValue('1 rue Test');
    expect(wrapper.find('input#nom').element.value).toBe('Nouvelle Agence');
    expect(wrapper.find('input#adresse').element.value).toBe('1 rue Test');
  });
});
