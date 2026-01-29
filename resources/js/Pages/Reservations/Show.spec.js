import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import ReservationsShow from '@/Pages/Reservations/Show.vue';

describe('Reservations/Show', () => {
  const stubLayout = { template: '<div><slot name="header" /><slot /></div>' };

  const reservation = {
    id: 1,
    destination: 'Paris',
    depart: 'Lyon',
    date_debut: '2024-01-15T08:00:00',
    date_fin: '2024-01-15T18:00:00',
    statut: 'validé',
    driver: { name: 'Alice' },
    vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence' },
    passengers: [{ id: 1, user: { name: 'Bob' }, statut: 'confirme' }],
    messages: [],
  };

  it('renders title and details', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: { id: 1 } } } });

    const wrapper = mount(ReservationsShow, {
      props: { reservation },
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Trajet pour Paris');
    expect(wrapper.text()).toContain('Alice');
    expect(wrapper.text()).toContain('Clio');
    expect(wrapper.text()).toContain('validé');
  });

  it('renders participants and messagerie', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: { id: 1 } } } });

    const wrapper = mount(ReservationsShow, {
      props: { reservation },
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Participants');
    expect(wrapper.text()).toContain('Messagerie du Trajet');
    expect(wrapper.find('form').exists()).toBe(true);
  });

  it('affiche le message vide quand il ne contient aucun message', async () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: { id: 1 } } } });

    const wrapper = mount(ReservationsShow, {
      props: { reservation: { ...reservation, messages: [] } },
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });

    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Aucun message. Démarrez la conversation !');
  });

  it('affiche les messages de l’utilisateur courant et des autres', async () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: { id: 1, name: 'Current' } } } });

    const reservationWithMessages = {
      ...reservation,
      messages: [
        {
          id: 1,
          body: 'Bonjour',
          created_at: '2024-01-15T10:00:00',
          user: { id: 2, name: 'Autre' },
        },
        {
          id: 2,
          body: 'Salut',
          created_at: '2024-01-15T10:05:00',
          user: { id: 1, name: 'Current' },
        },
      ],
    };

    const wrapper = mount(ReservationsShow, {
      props: { reservation: reservationWithMessages },
      global: { stubs: { AuthenticatedLayout: stubLayout, Head: true } },
    });

    await wrapper.vm.$nextTick();
    const text = wrapper.text();
    expect(text).toContain('Bonjour');
    expect(text).toContain('Salut');
    expect(text).toContain('Autre');
  });
});
