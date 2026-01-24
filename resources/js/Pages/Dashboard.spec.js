import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Dashboard from '@/Pages/Dashboard.vue';

vi.mock('@/Composables/useGeocoding', () => ({
  default: () => ({
    suggestionsDeparture: [],
    suggestionsDestination: [],
    isLoadingDeparture: false,
    isLoadingDestination: false,
    fetchSuggestions: vi.fn(),
  }),
}));
vi.mock('@/Composables/useDate', () => ({
  default: () => ({ formatDate: (d) => d || '' }),
}));
vi.mock('@/Composables/useReservation', () => ({
  default: () => ({ cancelPassenger: vi.fn(), deleteReservation: vi.fn() }),
}));

const pageProps = {
  props: {
    auth: { user: { name: 'User', email: 'u@u.com' }, roles: [] },
  },
};

describe('Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders AuthenticatedLayout', () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.findComponent({ name: 'AuthenticatedLayout' }).exists()).toBe(true);
  });

  it('renders Head with title Mes Trajets', () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.findComponent({ name: 'Head' }).exists()).toBe(true);
  });

  it('renders Recherche de Covoiturage and Nouveau trajet link', () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Recherche de Covoiturage');
    expect(wrapper.text()).toContain('Nouveau trajet');
    expect(wrapper.find('a[href="/r/reservations/create"]').exists()).toBe(true);
  });

  it('renders empty state for Mes Trajets (Conducteur) when no reservations', () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Mes Trajets (Conducteur)');
    expect(wrapper.text()).toContain("Vous n'avez encore aucun trajet planifié en tant que conducteur.");
  });

  it('renders empty state for Mes Trajets (Passager) when no reservations', () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Mes Trajets (Passager)');
    expect(wrapper.text()).toContain("Vous n'êtes encore passager d'aucun trajet.");
  });
});
