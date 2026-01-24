import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DomainsIndex from '@/Pages/Admin/Domains/Index.vue';

describe('Admin/Domains/Index', () => {
  const stubLayout = { template: '<div><slot /></div>' };

  beforeEach(() => {
    vi.clearAllMocks();
    global.confirm = vi.fn();
  });

  it('renders title and table', () => {
    const wrapper = mount(DomainsIndex, {
      props: { domains: [], can: { create: false, delete: false, edit: false } },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Gestion des domaines');
    expect(wrapper.find('table').exists()).toBe(true);
  });

  it('renders form when can.create', () => {
    const wrapper = mount(DomainsIndex, {
      props: { domains: [], can: { create: true, delete: false, edit: false } },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input[type="text"]').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Ajouter');
  });

  it('renders domains in table', () => {
    const wrapper = mount(DomainsIndex, {
      props: {
        domains: [{ id: 1, name: 'gmail.com' }, { id: 2, name: 'outlook.com' }],
        can: { create: false, delete: false, edit: false },
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('gmail.com');
    expect(wrapper.text()).toContain('outlook.com');
  });

  it('shows Supprimer when can.delete', () => {
    const wrapper = mount(DomainsIndex, {
      props: {
        domains: [{ id: 1, name: 'gmail.com' }],
        can: { create: false, delete: true, edit: false },
      },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Supprimer');
  });

  it('shows Aucun domaine when empty', () => {
    const wrapper = mount(DomainsIndex, {
      props: { domains: [], can: { create: false, delete: false, edit: false } },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Aucun domaine trouvé');
  });
});
