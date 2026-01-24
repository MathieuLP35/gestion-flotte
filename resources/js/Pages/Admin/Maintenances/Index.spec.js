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

  it('renders title and link to create', () => {
    vi.mocked(usePage).mockReturnValue({ props: { maintenances: [] } });

    const wrapper = mount(MaintenancesIndex, {
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Seuils de maintenance');
    expect(wrapper.text()).toContain('Ajouter seuil');
  });

  it('renders maintenances in table', () => {
    vi.mocked(usePage).mockReturnValue({
      props: {
        maintenances: [
          { id: 1, km_alert_threshold: 15000, date_dernier_entretien: '2024-01-10', vehicle: { modele: 'Clio', immatriculation: 'AB-123' } },
        ],
      },
    });

    const wrapper = mount(MaintenancesIndex, {
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('AB-123');
    expect(wrapper.text()).toContain('15000');
    expect(wrapper.text()).toContain('Modifier');
    expect(wrapper.text()).toContain('Supprimer');
  });

  it('shows Aucun seuil when empty', () => {
    vi.mocked(usePage).mockReturnValue({ props: { maintenances: [] } });

    const wrapper = mount(MaintenancesIndex, {
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Aucun seuil de maintenance trouvé');
  });
});
