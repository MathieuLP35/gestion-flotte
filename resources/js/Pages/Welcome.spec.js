import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Welcome from '@/Pages/Welcome.vue';

describe('Welcome', () => {
  const defaultProps = {
    laravelVersion: '11',
    phpVersion: '8.2',
    canLogin: true,
    canRegister: true,
  };

  it('renders PortalLayout with hero and feature sections', () => {
    const wrapper = mount(Welcome, {
      props: defaultProps,
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

  it('renders hero subtitle', () => {
    const wrapper = mount(Welcome, { props: defaultProps });
    expect(wrapper.text()).toContain('La solution tout-en-un pour gérer vos covoiturages');
  });

  it('renders Why SparkOtto section items', () => {
    const wrapper = mount(Welcome, { props: defaultProps });
    expect(wrapper.text()).toContain('Sécurité et contrôle');
    expect(wrapper.text()).toContain('Personnalisation');
    expect(wrapper.text()).toContain('Support et évolution');
    expect(wrapper.text()).toContain('Efficacité');
  });

});
