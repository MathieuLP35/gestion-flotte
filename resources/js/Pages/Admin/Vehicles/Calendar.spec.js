import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import VehiclesCalendar from '@/Pages/Admin/Vehicles/Calendar.vue';

vi.mock('@fullcalendar/core', () => ({
  Calendar: vi.fn().mockImplementation(function () {
    this.render = vi.fn();
    return this;
  }),
}));
vi.mock('@fullcalendar/daygrid', () => ({ default: {} }));
vi.mock('@fullcalendar/timegrid', () => ({ default: {} }));
vi.mock('@fullcalendar/interaction', () => ({ default: {} }));

describe('Admin/Vehicles/Calendar', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  const vehicle = {
    id: 1,
    modele: 'Clio',
    immatriculation: 'AB-123',
    km_initial: 5000,
    emplacement: 'Parking A',
    nbr_places: 5,
    en_maintenance: 0,
  };

  it('renders title and vehicle info', () => {
    const wrapper = mount(VehiclesCalendar, {
      props: { vehicle, reservations: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Calendrier de disponibilités');
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('AB-123');
    expect(wrapper.text()).toContain('5000');
    expect(wrapper.text()).toContain('Retour à la liste');
  });

  it('renders legend and calendar container', () => {
    const wrapper = mount(VehiclesCalendar, {
      props: { vehicle, reservations: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Légende des statuts');
    expect(wrapper.find('#calendar').exists()).toBe(true);
  });
});
