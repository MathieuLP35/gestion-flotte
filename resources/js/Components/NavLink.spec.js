import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import NavLink from '@/Components/NavLink.vue';

describe('NavLink', () => {
  it('renders link with href and slot content', () => {
    const wrapper = mount(NavLink, {
      props: { href: '/dashboard', active: false },
      slots: { default: 'Dashboard' },
    });
    expect(wrapper.find('a').attributes('href')).toBe('/dashboard');
    expect(wrapper.text()).toBe('Dashboard');
  });

  it('applies active classes when active is true', () => {
    const wrapper = mount(NavLink, {
      props: { href: '/dashboard', active: true },
      slots: { default: 'Dashboard' },
    });
    expect(wrapper.find('a').classes()).toContain('border-indigo-400');
    expect(wrapper.find('a').classes()).toContain('text-gray-900');
  });

  it('applies inactive classes when active is false', () => {
    const wrapper = mount(NavLink, {
      props: { href: '/dashboard', active: false },
      slots: { default: 'Dashboard' },
    });
    expect(wrapper.find('a').classes()).toContain('border-transparent');
    expect(wrapper.find('a').classes()).toContain('text-gray-500');
  });
});
