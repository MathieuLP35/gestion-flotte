import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';

vi.mock('@inertiajs/vue3', () => ({
  useForm: (initial) => ({
    ...initial,
    processing: false,
    errors: {},
    delete: vi.fn(),
    clearErrors: vi.fn(),
    reset: vi.fn(),
  }),
}));

beforeEach(() => {
  window.route = vi.fn((name) => (name === 'profile.destroy' ? '/profile' : '/'));
  // jsdom n’implémente pas showModal/close sur <dialog>
  if (typeof HTMLDialogElement !== 'undefined' && HTMLDialogElement.prototype) {
    if (typeof HTMLDialogElement.prototype.showModal !== 'function') {
      HTMLDialogElement.prototype.showModal = vi.fn();
    }
    if (typeof HTMLDialogElement.prototype.close !== 'function') {
      HTMLDialogElement.prototype.close = vi.fn();
    }
  }
});

describe('Profile/Partials/DeleteUserForm', () => {
  it('renders Delete Account section', () => {
    const wrapper = mount(DeleteUserForm);
    expect(wrapper.text()).toContain('Delete Account');
    expect(wrapper.text()).toContain('Once your account is deleted');
  });

  it('shows DangerButton to open confirmation', () => {
    const wrapper = mount(DeleteUserForm);
    const btn = wrapper.findComponent({ name: 'DangerButton' });
    expect(btn.exists()).toBe(true);
    expect(btn.text()).toContain('Delete Account');
  });

  it('opens modal when confirmUserDeletion is clicked', async () => {
    const wrapper = mount(DeleteUserForm, {
      global: { stubs: { Modal: { template: '<div v-if="show"><slot /></div>', props: ['show'] } } },
    });
    await wrapper.findComponent({ name: 'DangerButton' }).trigger('click');
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Are you sure you want to delete your account?');
    expect(wrapper.find('input#password').exists()).toBe(true);
  });

  it('renders Cancel and Delete Account in modal', async () => {
    const wrapper = mount(DeleteUserForm);
    await wrapper.findComponent({ name: 'DangerButton' }).trigger('click');
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Cancel');
    expect(wrapper.text()).toContain('Password');
  });
});
