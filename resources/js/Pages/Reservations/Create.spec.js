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
});
