import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { router } from '@inertiajs/vue3';
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
const cancelPassengerMock = vi.fn();
const deleteReservationMock = vi.fn();

vi.mock('@/Composables/useReservation', () => ({
  default: () => ({ cancelPassenger: cancelPassengerMock, deleteReservation: deleteReservationMock }),
}));

const pageProps = {
  props: {
    auth: { user: { name: 'User', email: 'u@u.com' }, roles: [], permissions: [] },
  },
};

describe('Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.spyOn(router, 'post').mockImplementation(() => {});
    vi.spyOn(router, 'visit').mockImplementation(() => {});
  });

  afterEach(() => {
    vi.restoreAllMocks();
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

  it('appelle startTrip quand on clique sur le bouton Lancer le trajet', async () => {
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

    const button = wrapper.get('button[title="Lancer le trajet"]');
    await button.trigger('click');

    expect(router.post).toHaveBeenCalled();
  });

  it('appelle endTrip puis redirige via handleReturnVehicle quand le statut est en cours', () => {
    const reservationsAsDriver = [
      {
        id: 1,
        depart: 'Paris',
        destination: 'Lyon',
        date_debut: '2025-02-01',
        date_fin: '2025-02-02',
        statut: 'en cours',
        date_retour: null,
        vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence', nbr_places: 5 },
        passengers: [],
      },
    ];

    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver, reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });

    // Appel direct de la méthode pour pouvoir simuler onSuccess
    wrapper.vm.handleReturnVehicle(reservationsAsDriver[0]);

    expect(router.post).toHaveBeenCalled();
    const call = router.post.mock.calls[0];
    expect(call[2]).toBeTruthy();
    if (call[2] && typeof call[2].onSuccess === 'function') {
      call[2].onSuccess();
      expect(router.visit).toHaveBeenCalled();
    }
  });

  it('redirige directement vers le formulaire de retour quand le statut est déjà à retourner', () => {
    const reservationsAsDriver = [
      {
        id: 2,
        depart: 'Paris',
        destination: 'Lyon',
        date_debut: '2025-02-01',
        date_fin: '2025-02-02',
        statut: 'à retourner',
        date_retour: null,
        vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence', nbr_places: 5 },
        passengers: [],
      },
    ];

    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver, reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });

    wrapper.vm.handleReturnVehicle(reservationsAsDriver[0]);

    expect(router.visit).toHaveBeenCalled();
  });

  it('appelle cancelPassenger quand on clique sur Annuler ma place', async () => {
    const reservationsAsPassenger = [
      {
        id: 1,
        reservation: {
          id: 10,
          depart: 'Paris',
          destination: 'Lyon',
          date_debut: '2025-02-01',
          date_fin: '2025-02-02',
          driver: { name: 'Marie' },
        },
        statut: 'confirme',
      },
    ];

    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger },
      global: { mocks: { $page: pageProps } },
    });

    const button = wrapper.findAll('button').find((b) => b.text().includes('Annuler ma place'));
    expect(button).toBeTruthy();

    await button.trigger('click');

    expect(cancelPassengerMock).toHaveBeenCalledWith(1);
  });

  it('appelle deleteReservation quand on clique sur le bouton de suppression', async () => {
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

    const deleteButton = wrapper.get('button.bg-red-600');
    await deleteButton.trigger('click');

    expect(deleteReservationMock).toHaveBeenCalledWith(1);
  });
});
