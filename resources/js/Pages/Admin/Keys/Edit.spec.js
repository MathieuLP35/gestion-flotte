import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import KeysEdit from '@/Pages/Admin/Keys/Edit.vue';

describe('Admin/Keys/Edit', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  it('renders title with vehicle info', () => {
    vi.mocked(usePage).mockReturnValue({
      props: {
        vehicleKey: {
          id: 1,
          emplacement_clef: 'Boîte 3',
          vehicle: { modele: 'Clio', immatriculation: 'AB-123' },
        },
      },
    });

    const wrapper = mount(KeysEdit, {
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Édition de la clé');
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('AB-123');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('textarea').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Enregistrer');
  });
});
