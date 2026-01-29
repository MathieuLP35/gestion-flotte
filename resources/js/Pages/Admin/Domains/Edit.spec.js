import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DomainsEdit from '@/Pages/Admin/Domains/Edit.vue';

describe('Admin/Domains/Edit', () => {
  const stubLayout = { template: '<div><slot /></div>' };
  const domain = { id: 1, name: 'gmail.com' };

  it('renders title and form with domain data', () => {
    const wrapper = mount(DomainsEdit, {
      props: { domain },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Modifier le domaine');
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('input#name').element.value).toBe('gmail.com');
    expect(wrapper.find('button[type="submit"]').text()).toContain('Enregistrer');
  });

  it('renders Annuler link', () => {
    const wrapper = mount(DomainsEdit, {
      props: { domain },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.text()).toContain('Annuler');
    expect(wrapper.find('a[href*="domains"]').exists()).toBe(true);
  });

  it('shows placeholder for domain input', () => {
    const wrapper = mount(DomainsEdit, {
      props: { domain },
      global: { stubs: { AdminLayout: stubLayout, Head: true } },
    });
    expect(wrapper.find('input#name').attributes('placeholder')).toBe('gmail.com');
  });
});
