import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AdminDashboard from '@/Pages/Admin/Dashboard.vue';

const defaultStubs = {
  Head: true,
  Link: true,
  AdminLayout: { template: '<div><slot /></div>' },
  Line: { template: '<div></div>' },
  Doughnut: { template: '<div></div>' },
  Bar: { template: '<div></div>' },
  AdminDashboardMap: { template: '<div></div>' },
};

describe('Admin/Dashboard', () => {
  it('renders dashboard with KPI and stats', () => {
    const wrapper = mount(AdminDashboard, {
      props: {
        agence: null,
        stats: {
          users_count: 10,
          vehicles_count: 5,
          reservations_count: 42,
          agences_count: 2,
          vehicles_in_maintenance_count: 1,
          reservations_en_attente_count: 3,
          reservations_ce_mois: 8,
          reservations_mois_precedent: 6,
          by_status: { 'en attente': 3, validé: 5 },
        },
        recent_reservations: [],
        chart_30j: { labels: ['1 Jan', '2 Jan'], data: [2, 3] },
        chart_12m: { labels: ['Jan 2025'], data: [5] },
        map_reservations: [],
      },
      global: { stubs: defaultStubs },
    });

    expect(wrapper.text()).toContain('Tableau de bord');
    expect(wrapper.text()).toContain('Utilisateurs');
    expect(wrapper.text()).toContain('10');
    expect(wrapper.text()).toContain('Véhicules');
    expect(wrapper.text()).toContain('5');
    expect(wrapper.text()).toContain('42');
    expect(wrapper.text()).toContain('Réservations par statut');
    expect(wrapper.text()).toContain('Réservations sur 30 jours');
    expect(wrapper.text()).toContain('Répartition par statut');
    expect(wrapper.text()).toContain('Réservations sur 12 mois');
    expect(wrapper.text()).toContain('Dernières réservations');
    expect(wrapper.text()).toContain('Accès rapides');
  });

  it('renders Head with title', () => {
    const wrapper = mount(AdminDashboard, {
      props: {
        agence: null,
        stats: { users_count: 0, vehicles_count: 0, reservations_count: 0 },
        recent_reservations: [],
        chart_30j: { labels: [], data: [] },
        chart_12m: { labels: [], data: [] },
        map_reservations: [],
      },
      global: { stubs: defaultStubs },
    });
    expect(wrapper.findComponent({ name: 'Head' }).exists()).toBe(true);
  });
});
