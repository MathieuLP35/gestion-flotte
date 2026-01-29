import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CarpoolingSearch from '@/Pages/Carpooling/Search.vue';

vi.mock('@/Composables/useDate', () => ({
  default: () => ({ formatDate: (d) => d || '' }),
}));

const pageProps = {
  props: {
    auth: { user: { name: 'User', email: 'u@u.com' }, roles: [], permissions: [] },
  },
};

describe('Carpooling/Search', () => {
  it('renders AuthenticatedLayout with title Covoiturages disponibles', () => {
    const wrapper = mount(CarpoolingSearch, {
      props: { carpoolings: [] },
      global: {
        mocks: { $page: pageProps },
        components: { Head: { name: 'Head', render: () => null } },
      },
    });

    expect(wrapper.findComponent({ name: 'AuthenticatedLayout' }).exists()).toBe(true);
    expect(wrapper.text()).toContain('Covoiturages disponibles');
    expect(wrapper.text()).toContain('Trouvez et rejoignez un covoiturage facilement');
  });

  it('shows empty state when carpoolings is empty', () => {
    const wrapper = mount(CarpoolingSearch, {
      props: { carpoolings: [] },
      global: {
        mocks: { $page: pageProps },
        components: { Head: { name: 'Head', render: () => null } },
      },
    });
    expect(wrapper.text()).toContain('Aucun covoiturage disponible');
  });

  it('renders carpooling list when carpoolings has data', () => {
    const carpoolings = [
      {
        id: 1,
        depart: 'Paris',
        destination: 'Lyon',
        date_debut: '2025-02-01T08:00:00',
        date_fin: '2025-02-02T18:00:00',
        vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence' },
      },
    ];
    const wrapper = mount(CarpoolingSearch, {
      props: { carpoolings },
      global: {
        mocks: { $page: pageProps },
        components: { Head: { name: 'Head', render: () => null } },
      },
    });
    expect(wrapper.text()).toContain('Paris');
    expect(wrapper.text()).toContain('Lyon');
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('Rejoindre ce trajet');
  });
});
