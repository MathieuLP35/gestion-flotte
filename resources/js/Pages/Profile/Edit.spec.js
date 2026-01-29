import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ProfileEdit from '@/Pages/Profile/Edit.vue';

const pageProps = {
  props: {
    auth: {
      user: { name: 'User', email: 'user@example.com' },
      roles: [],
      permissions: [],
    },
  },
};

describe('Profile/Edit', () => {
  it('renders AuthenticatedLayout with header Profile', () => {
    const wrapper = mount(ProfileEdit, {
      global: { mocks: { $page: pageProps } },
    });

    expect(wrapper.findComponent({ name: 'AuthenticatedLayout' }).exists()).toBe(true);
    expect(wrapper.text()).toContain('Profile');
  });

  it('renders UpdateProfileInformationForm, UpdatePasswordForm and DeleteUserForm', () => {
    const wrapper = mount(ProfileEdit, {
      global: { mocks: { $page: pageProps } },
    });

    expect(wrapper.findComponent({ name: 'UpdateProfileInformationForm' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'UpdatePasswordForm' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'DeleteUserForm' }).exists()).toBe(true);
  });

  it('renders Head with title Profile', () => {
    const wrapper = mount(ProfileEdit, {
      global: { mocks: { $page: pageProps } },
    });
    expect(wrapper.findComponent({ name: 'Head' }).exists()).toBe(true);
  });
});
