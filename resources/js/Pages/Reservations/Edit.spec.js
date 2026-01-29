import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { usePage } from '@inertiajs/vue3';
import ReservationsEdit from '@/Pages/Reservations/Edit.vue';

describe('Reservations/Edit', () => {
  const stubLayout = { template: '<div><slot name="header" /><slot /></div>' };

  const reservation = {
    id: 1,
    vehicle_id: 1,
    depart: 'Lyon',
    destination: 'Paris',
    date_debut: '2024-01-15T08:00:00',
    date_fin: '2024-01-15T18:00:00',
    depart_latitude: 45.75,
    depart_longitude: 4.85,
    destination_latitude: 48.86,
    destination_longitude: 2.35,
    vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence' },
    passengers: [{ id: 1, user: { name: 'Alice' }, statut: 'en_attente' }],
  };

  it('renders title and trajet details', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: { id: 1, name: 'Admin' } } } });

    const wrapper = mount(ReservationsEdit, {
      props: { reservation, vehicles: [{ id: 1, modele: 'Clio' }] },
      global: {
        stubs: { AuthenticatedLayout: stubLayout, Head: true, MapRoute: { template: '<div class="map-stub"></div>' } },
      },
    });
    expect(wrapper.text()).toContain('Gérer le trajet');
    expect(wrapper.text()).toContain('Lyon');
    expect(wrapper.text()).toContain('Paris');
    expect(wrapper.text()).toContain('Clio');
  });

  it('renders passagers and messagerie sections', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: { id: 1 } } } });

    const wrapper = mount(ReservationsEdit, {
      props: { reservation, vehicles: [] },
      global: {
        stubs: { AuthenticatedLayout: stubLayout, Head: true, MapRoute: { template: '<div></div>' } },
      },
    });
    expect(wrapper.text()).toContain('Gestion des Passagers');
    expect(wrapper.text()).toContain('Messagerie du Trajet');
    expect(wrapper.text()).toContain('Itinéraire du Trajet');
  });

  it('affiche les bons boutons selon le statut des passagers', () => {
    vi.mocked(usePage).mockReturnValue({ props: { auth: { user: { id: 1 } } } });

    const reservationWithStatuses = {
      ...reservation,
      passengers: [
        { id: 1, user: { name: 'Alice' }, statut: 'en_attente' },
        { id: 2, user: { name: 'Bob' }, statut: 'confirme' },
        { id: 3, user: { name: 'Charlie' }, statut: 'refuse' },
      ],
    };

    const wrapper = mount(ReservationsEdit, {
      props: { reservation: reservationWithStatuses, vehicles: [] },
      global: {
        stubs: { AuthenticatedLayout: stubLayout, Head: true, MapRoute: { template: '<div></div>' } },
      },
    });

    const text = wrapper.text();
    expect(text).toContain('Accepter');
    expect(text).toContain('Refuser');
    expect(text).toContain('Retirer');
    expect(text).toContain('Ré-accepter');
  });
});
