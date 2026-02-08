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
});
