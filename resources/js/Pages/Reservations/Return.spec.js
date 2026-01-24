import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReservationsReturn from '@/Pages/Reservations/Return.vue';

describe('Reservations/Return', () => {
  const stubLayout = { template: '<div><slot name="header" /><slot /></div>' };

  const reservation = {
    id: 1,
    depart: 'Lyon',
    destination: 'Paris',
    vehicle: { modele: 'Clio', immatriculation: 'AB-123', km_initial: 10000 },
  };

  it('renders title and reservation info', () => {
    const wrapper = mount(ReservationsReturn, {
      props: { reservation },
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Retour du véhicule');
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('AB-123');
    expect(wrapper.text()).toContain('10000');
    expect(wrapper.text()).toContain('Lyon');
    expect(wrapper.text()).toContain('Paris');
  });

  it('renders return form with all fields', () => {
    const wrapper = mount(ReservationsReturn, {
      props: { reservation },
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#km_final').exists()).toBe(true);
    expect(wrapper.find('input#emplacement_retour').exists()).toBe(true);
    expect(wrapper.find('select#etat_vehicule').exists()).toBe(true);
    expect(wrapper.find('textarea#notes_retour').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Enregistrer le retour');
  });

  it('has etat options', () => {
    const wrapper = mount(ReservationsReturn, {
      props: { reservation },
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Excellent');
    expect(wrapper.text()).toContain('Bon');
    expect(wrapper.text()).toContain('Moyen');
    expect(wrapper.text()).toContain('Mauvais');
  });
});
