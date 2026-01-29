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
    auth: { user: { name: 'User', email: 'u@u.com' }, roles: [], permissions: [] },
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

  it('renders reservation cards when reservationsAsDriver has data', () => {
    const reservationsAsDriver = [
      {
        id: 1,
        depart: 'Paris',
        destination: 'Lyon',
        date_debut: '2025-02-01',
        date_fin: '2025-02-02',
        statut: 'validé',
        vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence', nbr_places: 5 },
        passengers: [],
      },
    ];
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver, reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Paris');
    expect(wrapper.text()).toContain('Lyon');
    expect(wrapper.text()).toContain('Clio');
    // Lien d’édition (icône sans texte "Modifier") : route() fourni par setup global
    expect(wrapper.find('a[href="/r/reservations/edit/1"]').exists()).toBe(true);
  });

  it('renders reservation cards when reservationsAsPassenger has data', () => {
    const reservationsAsPassenger = [
      {
        id: 1,
        reservation: {
          id: 10,
          destination: 'Nantes',
          date_debut: '2025-02-01',
          driver: { name: 'Marie' },
        },
        statut: 'confirme',
      },
    ];
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Nantes');
    expect(wrapper.text()).toContain('Marie');
    expect(wrapper.text()).toContain('Annuler ma place');
  });

  it('renders search form with departure and destination inputs', () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.find('input#departure').exists()).toBe(true);
    expect(wrapper.find('input#destination').exists()).toBe(true);
    expect(wrapper.find('input#departureDate').exists()).toBe(true);
    expect(wrapper.find('input#arrivalDate').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Rechercher');
  });

  it('ne plante pas si on soumet la recherche sans lieux sélectionnés', async () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });

    wrapper.vm.form.departure = 'Rennes';
    wrapper.vm.form.destination = 'Nantes';

    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.text()).toContain('Recherche de Covoiturage');
  });
});
