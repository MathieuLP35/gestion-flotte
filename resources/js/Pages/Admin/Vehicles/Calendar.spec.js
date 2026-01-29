import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import VehiclesCalendar from '@/Pages/Admin/Vehicles/Calendar.vue';
import { Calendar } from '@fullcalendar/core';

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

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders title and vehicle info', () => {
    const wrapper = mount(VehiclesCalendar, {
      props: { vehicle, reservations: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Calendrier de disponibilités');
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('AB-123');
    expect(wrapper.text()).toContain('5000');
    expect(wrapper.text()).toContain('Retour aux véhicules');
  });

  it('renders legend and calendar container', () => {
    const wrapper = mount(VehiclesCalendar, {
      props: { vehicle, reservations: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Légende des statuts');
    expect(wrapper.find('#calendar').exists()).toBe(true);
  });

  it('crée des événements FullCalendar à partir des réservations', () => {
    const reservations = [
      {
        id: 10,
        depart: 'Paris',
        destination: 'Lyon',
        date_debut: '2025-02-01T10:00:00',
        date_fin: '2025-02-01T18:00:00',
        statut: 'validé',
        driver: { name: 'Jean' },
        passengers: [],
        covoiturage: true,
      },
    ];

    mount(VehiclesCalendar, {
      props: { vehicle, reservations },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });

    expect(Calendar).toHaveBeenCalledTimes(1);
    const config = Calendar.mock.calls[0][1];
    expect(config.events).toHaveLength(1);
    expect(config.events[0]).toMatchObject({
      id: 10,
      title: 'Paris → Lyon',
      start: '2025-02-01T10:00:00',
      end: '2025-02-01T18:00:00',
    });
  });
});
