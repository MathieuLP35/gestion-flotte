import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import VehicleSuggestion from '@/Pages/Admin/Settings/VehicleSuggestion.vue';

describe('Admin/Settings/VehicleSuggestion', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  const defaultProps = {
    setting: {
      petit_trajet_seuil_km: 100,
      priorite_petit_trajet: ['electrique', 'hybride'],
      priorite_long_trajet: ['hybride', 'electrique'],
    },
    energies: ['electrique', 'hybride', 'essence', 'diesel'],
    can: { edit: true },
  };

  it('renders title and description', () => {
    const wrapper = mount(VehicleSuggestion, {
      props: defaultProps,
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Paramètres de suggestion de véhicule');
    expect(wrapper.text()).toContain('Définit comment un véhicule est proposé');
  });

  it('renders seuil input with setting value', () => {
    const wrapper = mount(VehicleSuggestion, {
      props: defaultProps,
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('input#seuil').exists()).toBe(true);
    expect(wrapper.find('input#seuil').element.value).toBe('100');
  });

  it('renders priorité petit trajet and long trajet labels', () => {
    const wrapper = mount(VehicleSuggestion, {
      props: defaultProps,
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Priorité pour les petits trajets');
    expect(wrapper.text()).toContain('Priorité pour les trajets longs');
  });

  it('renders Enregistrer button when can.edit', () => {
    const wrapper = mount(VehicleSuggestion, {
      props: defaultProps,
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Enregistrer');
  });

  it('hides submit button when can.edit is false', () => {
    const wrapper = mount(VehicleSuggestion, {
      props: { ...defaultProps, can: { edit: false } },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('button[type="submit"]').exists()).toBe(false);
  });

  it('disables inputs when can.edit is false', () => {
    const wrapper = mount(VehicleSuggestion, {
      props: { ...defaultProps, can: { edit: false } },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('input#seuil').attributes('disabled')).toBeDefined();
  });
});
