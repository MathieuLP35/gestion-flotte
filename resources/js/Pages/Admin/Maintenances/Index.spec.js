import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import MaintenancesIndex from '@/Pages/Admin/Maintenances/Index.vue';
import { usePage } from '@inertiajs/vue3';

describe('Admin/Maintenances/Index', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  beforeEach(() => {
    vi.clearAllMocks();
    global.confirm = vi.fn();
  });

  it('renders title', () => {
    vi.mocked(usePage).mockReturnValue({ props: { vehicles: [] } });

    const wrapper = mount(MaintenancesIndex, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('État du Parc (Maintenance)');
  });

  it('renders vehicles in table', () => {
    vi.mocked(usePage).mockReturnValue({
      props: {
        vehicles: [
          { id: 1, modele: 'Clio', immatriculation: 'AB-123', agence: 'Nice', kilometrage: 12000, km_until_next: 3000, date_next: '2025-01-01', status: 'warning' },
        ],
      },
    });

    const wrapper = mount(MaintenancesIndex, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Clio (AB-123)');
    expect(wrapper.text()).toContain('Nice');
    expect(wrapper.text()).toContain('Dans 3000 km');
    expect(wrapper.text()).toContain('À prévoir');
    expect(wrapper.text()).toContain("Gérer l'historique");
  });

  it('shows Aucun véhicule when empty', () => {
    vi.mocked(usePage).mockReturnValue({ props: { vehicles: [] } });

    const wrapper = mount(MaintenancesIndex, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Aucun véhicule disponible.');
  });
});
