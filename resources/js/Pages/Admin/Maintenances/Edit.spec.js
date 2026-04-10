import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import MaintenancesEdit from '@/Pages/Admin/Maintenances/Edit.vue';
import { usePage } from '@inertiajs/vue3';

describe('Admin/Maintenances/Edit', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  it('renders title and form', () => {
    vi.mocked(usePage).mockReturnValue({
      props: {
        maintenance: { id: 1, vehicle_id: 1, kilometrage: 10000, date: '2024-01-15', type: 'revision' },
        vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123' }],
      },
    });

    const wrapper = mount(MaintenancesEdit, {
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain("Modifier l'intervention (Maintenance)");
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('select#vehicle').exists()).toBe(true);
    expect(wrapper.find('input#kilometrage').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Mettre à jour');
  });
});
