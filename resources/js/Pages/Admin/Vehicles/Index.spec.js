import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import AdminVehiclesIndex from '@/Pages/Admin/Vehicles/Index.vue';

// $page.props.auth.user requis par AdminLayout ({{ $page.props.auth.user.name }})
const authProps = { auth: { user: { name: 'Admin', email: 'admin@test.com' } } };

describe('Admin/Vehicles/Index', () => {
  it('renders AdminLayout with title and Ajouter un véhicule link', () => {
    const props = { ...authProps, vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123', km_initial: 0, emplacement: 'X', nbr_places: 5, energie: 'essence', en_maintenance: 0 }] };
    vi.mocked(usePage).mockReturnValue({ props });
    const wrapper = mount(AdminVehiclesIndex, {
      global: { mocks: { $page: { props } } },
    });

    expect(wrapper.findComponent({ name: 'AdminLayout' }).exists()).toBe(true);
    expect(wrapper.text()).toContain('Liste des Véhicules');
    expect(wrapper.find('a[href="/r/admin/vehicles/create"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Ajouter un véhicule');
  });

  it('renders empty state when no vehicles', () => {
    const props = { ...authProps, vehicles: [] };
    vi.mocked(usePage).mockReturnValue({ props });
    const wrapper = mount(AdminVehiclesIndex, {
      global: { mocks: { $page: { props } } },
    });
    expect(wrapper.text()).toContain('Aucun véhicule trouvé.');
  });

  it('renders table headers', () => {
    const props = { ...authProps, vehicles: [{ id: 1, modele: 'C', immatriculation: 'X', km_initial: 0, emplacement: 'P', nbr_places: 5, energie: 'essence', en_maintenance: 0 }] };
    vi.mocked(usePage).mockReturnValue({ props });
    const wrapper = mount(AdminVehiclesIndex, {
      global: { mocks: { $page: { props } } },
    });
    expect(wrapper.text()).toContain('Modèle');
    expect(wrapper.text()).toContain('Immatriculation');
    expect(wrapper.text()).toContain('Places');
    expect(wrapper.text()).toContain('Énergie');
  });
});
