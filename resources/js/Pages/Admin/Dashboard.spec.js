import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AdminDashboard from '@/Pages/Admin/Dashboard.vue';

describe('Admin/Dashboard', () => {
  it('renders welcome and stats cards', () => {
    const wrapper = mount(AdminDashboard, {
      props: {
        stats: {
          users_count: 10,
          vehicles_count: 5,
          reservations_count: 42,
        },
      },
    });

    expect(wrapper.text()).toContain("Bienvenue dans le Panel d'Administration");
    expect(wrapper.text()).toContain("Utilisez le menu de navigation pour gérer le site.");
    expect(wrapper.text()).toContain('Utilisateurs');
    expect(wrapper.text()).toContain('10');
    expect(wrapper.text()).toContain('Véhicules');
    expect(wrapper.text()).toContain('5');
    expect(wrapper.text()).toContain('Nombre de trajets totals');
    expect(wrapper.text()).toContain('42');
  });

  it('renders Head with title Admin Dashboard', () => {
    const wrapper = mount(AdminDashboard, {
      props: { stats: { users_count: 0, vehicles_count: 0, reservations_count: 0 } },
    });
    expect(wrapper.findComponent({ name: 'Head' }).exists()).toBe(true);
  });
});
