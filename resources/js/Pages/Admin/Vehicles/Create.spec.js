import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import VehiclesCreate from '@/Pages/Admin/Vehicles/Create.vue';

describe('Admin/Vehicles/Create', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  it('renders title and form', () => {
    const wrapper = mount(VehiclesCreate, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Ajouter un nouveau véhicule');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#modele').exists()).toBe(true);
    expect(wrapper.find('input#immatriculation').exists()).toBe(true);
    expect(wrapper.find('input#km_initial').exists()).toBe(true);
    expect(wrapper.find('input#emplacement').exists()).toBe(true);
    expect(wrapper.find('input#nbr_places').exists()).toBe(true);
    expect(wrapper.find('input#nbr_cles').exists()).toBe(true);
    expect(wrapper.find('select#energie').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Ajouter');
  });

  it('has energie options', () => {
    const wrapper = mount(VehiclesCreate, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Essence');
    expect(wrapper.text()).toContain('Diesel');
    expect(wrapper.text()).toContain('Hybride');
    expect(wrapper.text()).toContain('Électrique');
  });
});
