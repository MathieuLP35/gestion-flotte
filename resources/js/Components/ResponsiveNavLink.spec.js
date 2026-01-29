import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

describe('ResponsiveNavLink', () => {
  it('renders link with href and slot content', () => {
    const wrapper = mount(ResponsiveNavLink, {
      props: { href: '/dashboard', active: false },
      slots: { default: 'Dashboard' },
    });
    expect(wrapper.find('a').attributes('href')).toBe('/dashboard');
    expect(wrapper.text()).toBe('Dashboard');
  });

  it('applies active classes when active is true', () => {
    const wrapper = mount(ResponsiveNavLink, {
      props: { href: '/profile', active: true },
      slots: { default: 'Profil' },
    });
    expect(wrapper.find('a').classes()).toContain('border-indigo-400');
    expect(wrapper.find('a').classes()).toContain('bg-indigo-50');
  });

  it('applies inactive classes when active is false', () => {
    const wrapper = mount(ResponsiveNavLink, {
      props: { href: '/profile', active: false },
      slots: { default: 'Profil' },
    });
    expect(wrapper.find('a').classes()).toContain('border-transparent');
    expect(wrapper.find('a').classes()).toContain('text-gray-600');
  });
});
