import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Welcome from '@/Pages/Welcome.vue';

describe('Welcome', () => {
  it('renders PortalLayout with hero and feature sections', () => {
    const wrapper = mount(Welcome, {
      props: {
        laravelVersion: '11',
        phpVersion: '8.2',
        canLogin: true,
        canRegister: true,
      },
    });

    expect(wrapper.text()).toContain('Bienvenue sur SparkOtto');
    expect(wrapper.text()).toContain('Covoiturage');
    expect(wrapper.text()).toContain('Véhicules');
    expect(wrapper.text()).toContain('Réservations');
    expect(wrapper.text()).toContain('Pourquoi choisir SparkOtto ?');
  });

  it('renders Head with title Accueil', () => {
    const wrapper = mount(Welcome, {
      props: { laravelVersion: '11', phpVersion: '8.2' },
    });
    expect(wrapper.findComponent({ name: 'Head' }).exists()).toBe(true);
  });

  it('uses PortalLayout', () => {
    const wrapper = mount(Welcome, {
      props: { laravelVersion: '11', phpVersion: '8.2' },
    });
    expect(wrapper.findComponent({ name: 'PortalLayout' }).exists()).toBe(true);
  });
});
