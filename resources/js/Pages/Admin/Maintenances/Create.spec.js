import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import MaintenancesCreate from '@/Pages/Admin/Maintenances/Create.vue';
import { usePage } from '@inertiajs/vue3';

describe('Admin/Maintenances/Create', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  it('renders title and form', () => {
    vi.mocked(usePage).mockReturnValue({
      props: { vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123' }] },
    });

    const wrapper = mount(MaintenancesCreate, {
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Ajouter un seuil de maintenance');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('select#vehicle').exists()).toBe(true);
    expect(wrapper.find('input#km_alert_threshold').exists()).toBe(true);
    expect(wrapper.find('input#date_dernier_entretien').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Ajouter');
  });

  it('shows vehicle options', () => {
    vi.mocked(usePage).mockReturnValue({
      props: { vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123' }] },
    });

    const wrapper = mount(MaintenancesCreate, {
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('AB-123');
  });
});
