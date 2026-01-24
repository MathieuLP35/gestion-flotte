import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import VehiclesEdit from '@/Pages/Admin/Vehicles/Edit.vue';
import { usePage } from '@inertiajs/vue3';

describe('Admin/Vehicles/Edit', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  const vehicle = {
    id: 1,
    modele: 'Clio',
    immatriculation: 'AB-123',
    km_initial: 10000,
    emplacement: 'Parking A',
    nbr_places: 5,
    energie: 'essence',
    en_maintenance: 0,
    keys: [{ id: 1, emplacement_clef: 'Boîte 1' }],
    maintenances: [{ id: 1, km_alert_threshold: 20000, date_dernier_entretien: '2024-01-10' }],
  };

  it('renders title and vehicle form', () => {
    vi.mocked(usePage).mockReturnValue({ props: { vehicle } });

    const wrapper = mount(VehiclesEdit, {
      global: { stubs: { AdminLayout: stubLayout, Head: true, Link: true } },
    });
    expect(wrapper.text()).toContain('Modifier le véhicule');
    expect(wrapper.find('input#modele').element.value).toBe('Clio');
    expect(wrapper.find('input#immatriculation').element.value).toBe('AB-123');
  });

  it('renders keys and maintenances sections', () => {
    vi.mocked(usePage).mockReturnValue({ props: { vehicle } });

    const wrapper = mount(VehiclesEdit, {
      global: { stubs: { AdminLayout: stubLayout, Head: true, Link: true } },
    });
    expect(wrapper.text()).toContain('Gestion des clés');
    expect(wrapper.text()).toContain('Seuils de maintenance');
    expect(wrapper.text()).toContain('Boîte 1');
  });
});
