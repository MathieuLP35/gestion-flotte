import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import VehiclesAvailability from '@/Pages/Admin/Vehicles/Availability.vue';

vi.mock('@fullcalendar/core', () => ({
  Calendar: vi.fn().mockImplementation(function () {
    this.render = vi.fn();
    this.destroy = vi.fn();
    return this;
  }),
}));
vi.mock('@fullcalendar/daygrid', () => ({ default: {} }));
vi.mock('@fullcalendar/timegrid', () => ({ default: {} }));
vi.mock('@fullcalendar/interaction', () => ({ default: {} }));

describe('Admin/Vehicles/Availability', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  beforeEach(() => {
    vi.clearAllMocks();
    global.confirm = vi.fn();
  });

  it('renders title and link to create', () => {
    const wrapper = mount(VehiclesAvailability, {
      props: { vehicles: [], selectedVehicle: null, reservations: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Gestion des Véhicules et Disponibilités');
    expect(wrapper.text()).toContain('Ajouter un véhicule');
  });

  it('shows Sélectionnez un véhicule when none selected', () => {
    const wrapper = mount(VehiclesAvailability, {
      props: { vehicles: [], selectedVehicle: null, reservations: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Sélectionnez un véhicule');
  });

  it('renders vehicle list and search', () => {
    const wrapper = mount(VehiclesAvailability, {
      props: {
        vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123', emplacement: 'Parking', nbr_places: 5, energie: 'essence', en_maintenance: 0 }],
        selectedVehicle: null,
        reservations: [],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('AB-123');
    expect(wrapper.find('input[placeholder*="Rechercher"]').exists()).toBe(true);
  });

  it('shows selected vehicle info when one is selected', () => {
    const v = { id: 1, modele: 'Clio', immatriculation: 'AB-123', km_initial: 0, emplacement: 'X', nbr_places: 5, energie: 'essence', en_maintenance: 0 };
    const wrapper = mount(VehiclesAvailability, {
      props: { vehicles: [v], selectedVehicle: v, reservations: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('Légende des statuts');
  });
});
