import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import AgencesIndex from '@/Pages/Admin/Agences/Index.vue';

describe('Admin/Agences/Index', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  beforeEach(() => {
    vi.clearAllMocks();
    global.confirm = vi.fn();
  });

  it('renders title and table', () => {
    const wrapper = mount(AgencesIndex, {
      props: { agences: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Gestion des agences');
    expect(wrapper.find('table').exists()).toBe(true);
  });

  it('renders link to create agence', () => {
    const wrapper = mount(AgencesIndex, {
      props: { agences: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Créer une agence');
    expect(wrapper.find('a[href*="agences/create"]').exists()).toBe(true);
  });

  it('renders agences in table', () => {
    const wrapper = mount(AgencesIndex, {
      props: {
        agences: [
          { id: 1, nom: 'Agence Paris', adresse: '10 rue de Paris', vehicles_count: 2, users_count: 3 },
          { id: 2, nom: 'Agence Lyon', adresse: null, vehicles_count: 0, users_count: 0 },
        ],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Agence Paris');
    expect(wrapper.text()).toContain('10 rue de Paris');
    expect(wrapper.text()).toContain('Agence Lyon');
    expect(wrapper.text()).toContain('Modifier');
    expect(wrapper.text()).toContain('Supprimer');
  });

  it('shows Aucune agence when empty', () => {
    const wrapper = mount(AgencesIndex, {
      props: { agences: [] },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Aucune agence');
  });

  it('disables Supprimer when agence has vehicles or users', () => {
    const wrapper = mount(AgencesIndex, {
      props: {
        agences: [{ id: 1, nom: 'Agence A', adresse: '', vehicles_count: 1, users_count: 0 }],
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    const deleteBtn = wrapper.find('button');
    expect(deleteBtn.attributes('disabled')).toBeDefined();
  });
});
