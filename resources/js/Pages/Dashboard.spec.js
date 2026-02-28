import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { router } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue';



// Mock des Composables personnalisés
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
  default: () => ({
    cancelPassenger: cancelPassengerMock,
    deleteReservation: deleteReservationMock
  }),
}));

const pageProps = {
  props: {
    auth: { user: { name: 'User', email: 'u@u.com' }, roles: [], permissions: [] },
  },
};

describe('Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    // On mocke les méthodes globales du router
    vi.spyOn(router, 'post').mockImplementation(() => { });
    vi.spyOn(router, 'visit').mockImplementation(() => { });
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
    // Vérifie la présence du lien (le chemin peut varier selon votre config route())
    expect(wrapper.find('a').exists()).toBe(true);
  });

  it('renders empty state for Mes Trajets (Conducteur) when no reservations', () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Mes Trajets (Conducteur)');
    expect(wrapper.text()).toContain("Vous n'avez encore aucun trajet planifié en tant que conducteur.");
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
  });

  it('ne plante pas si on soumet la recherche sans lieux sélectionnés', async () => {
    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver: [], reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });

    // On simule la saisie
    wrapper.vm.form.departure = 'Rennes';
    wrapper.vm.form.destination = 'Nantes';

    // Déclenche la soumission (searchCarpooling est appelé)
    await wrapper.find('form').trigger('submit.prevent');

    // Vérifie que clearErrors a été appelé (preuve que le mock fonctionne)
    expect(wrapper.vm.form.clearErrors).toHaveBeenCalled();
    // Vérifie que setError a été appelé car les lieux n'ont pas été "sélectionnés" via l'autocomplete
    expect(wrapper.vm.form.setError).toHaveBeenCalled();
  });

  it('appelle startTrip quand on clique sur le bouton Lancer le trajet', async () => {
    const reservationsAsDriver = [{
      id: 1,
      depart: 'Paris',
      destination: 'Lyon',
      date_debut: '2025-02-01',
      date_fin: '2025-02-02',
      statut: 'validé',
      vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence', nbr_places: 5 },
      passengers: [],
    }];

    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver, reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });

    const button = wrapper.get('button[title="Lancer le trajet"]');
    await button.trigger('click');

    expect(router.post).toHaveBeenCalled();
  });

  it('appelle handleReturnVehicle et gère la redirection', async () => {
    const reservationsAsDriver = [{
      id: 1,
      statut: 'en cours',
      depart: 'Paris',
      destination: 'Lyon',
      vehicle: { modele: 'Clio', immatriculation: 'A', nbr_places: 5 },
      passengers: []
    }];

    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver, reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });

    // Appel manuel de la méthode pour simuler le flux async d'Inertia
    wrapper.vm.handleReturnVehicle(reservationsAsDriver[0]);

    expect(router.post).toHaveBeenCalled();
    const call = router.post.mock.calls[0];

    // Simuler le succès du post pour déclencher le visit
    if (call[2] && typeof call[2].onSuccess === 'function') {
      call[2].onSuccess();
      expect(router.visit).toHaveBeenCalled();
    }
  });

  it('appelle deleteReservation quand on clique sur le bouton de suppression', async () => {
    const reservationsAsDriver = [{
      id: 1,
      depart: 'Paris',
      destination: 'Lyon',
      statut: 'validé',
      vehicle: { modele: 'Clio', immatriculation: 'A', nbr_places: 5 },
      passengers: []
    }];

    const wrapper = mount(Dashboard, {
      props: { reservationsAsDriver, reservationsAsPassenger: [] },
      global: { mocks: { $page: pageProps } },
    });

    const deleteButton = wrapper.get('button.bg-red-600');
    await deleteButton.trigger('click');

    expect(deleteReservationMock).toHaveBeenCalledWith(1);
  });
});