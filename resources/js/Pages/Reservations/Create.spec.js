import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ReservationsCreate from '@/Pages/Reservations/Create.vue';

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

const pageProps = {
  props: {
    auth: { user: { name: 'User', email: 'u@u.com' }, roles: [], permissions: [] },
  },
};

describe('Reservations/Create', () => {
  it('renders AuthenticatedLayout and title Nouvelle réservation', () => {
    const wrapper = mount(ReservationsCreate, {
      props: { vehicles: [] },
      global: { mocks: { $page: pageProps } },
    });

    expect(wrapper.findComponent({ name: 'AuthenticatedLayout' }).exists()).toBe(true);
    expect(wrapper.text()).toContain('Nouvelle réservation');
  });

  it('renders Option 1 (Réserver un véhicule) and Option 2 (Covoiturage)', () => {
    const wrapper = mount(ReservationsCreate, {
      props: { vehicles: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Option 1 : Réserver un véhicule');
    expect(wrapper.text()).toContain('Option 2 : Rejoindre un covoiturage');
  });

  it('renders form fields for Option 1: Départ, Destination, dates, véhicule', () => {
    const wrapper = mount(ReservationsCreate, {
      props: { vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123', energie: 'essence', nbr_places: 5 }] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Départ');
    expect(wrapper.text()).toContain('Destination');
    expect(wrapper.find('input#departure').exists()).toBe(true);
    expect(wrapper.find('input#destination').exists()).toBe(true);
    expect(wrapper.text()).toContain('Véhicule');
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.find('select#vehicle').exists()).toBe(true);
    expect(wrapper.find('input#date_debut').exists()).toBe(true);
    expect(wrapper.find('input#date_fin').exists()).toBe(true);
  });

  it('renders vehicle select when vehicles is empty', () => {
    const wrapper = mount(ReservationsCreate, {
      props: { vehicles: [] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Option 1');
    expect(wrapper.find('select#vehicle').exists()).toBe(true);
    expect(wrapper.text()).toContain('Sélectionnez un véhicule');
  });

  it('renders submit button Réserver ce véhicule', () => {
    const wrapper = mount(ReservationsCreate, {
      props: { vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123', energie: 'essence', nbr_places: 5 }] },
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.text()).toContain('Réserver ce véhicule');
  });

  it("affiche une erreur si aucun lieu de départ n'est sélectionné", async () => {
    const wrapper = mount(ReservationsCreate, {
      props: { vehicles: [] },
      global: { mocks: { $page: pageProps } },
    });

    wrapper.vm.form.departure = 'Rennes';
    wrapper.vm.form.destination = 'Nantes';
    wrapper.vm.form.date_debut = '2025-02-01T10:00';
    wrapper.vm.form.date_fin = '2025-02-01T18:00';

    await wrapper.find('form').trigger('submit.prevent');
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.form.errors.departure).toBeTruthy();
  });

  it("affiche une erreur si aucune destination n'est sélectionnée", async () => {
    const wrapper = mount(ReservationsCreate, {
      props: { vehicles: [] },
      global: { mocks: { $page: pageProps } },
    });

    wrapper.vm.form.departure = 'Rennes';
    wrapper.vm.form.destination = 'Nantes';
    wrapper.vm.form.departureSelected = { lat: 1, lng: 2, label: 'Rennes' };
    wrapper.vm.form.date_debut = '2025-02-01T10:00';
    wrapper.vm.form.date_fin = '2025-02-01T18:00';

    await wrapper.find('form').trigger('submit.prevent');
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.form.errors.destination).toBeTruthy();
  });
});
