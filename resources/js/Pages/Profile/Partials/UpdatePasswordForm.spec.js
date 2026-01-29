import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';

const mockForm = {
  current_password: '',
  password: '',
  password_confirmation: '',
  processing: false,
  errors: {},
  put: vi.fn(),
  reset: vi.fn(),
};

vi.mock('@inertiajs/vue3', () => ({
  useForm: (initial) => ({ ...initial, ...mockForm }),
}));

beforeEach(() => {
  window.route = vi.fn((name) => (name === 'password.update' ? '/user/password' : '/'));
  vi.clearAllMocks();
});

describe('Profile/Partials/UpdatePasswordForm', () => {
  it('renders Update Password section', () => {
    const wrapper = mount(UpdatePasswordForm);
    expect(wrapper.text()).toContain('Mettre à jour le mot de passe');
    expect(wrapper.text()).toContain('Assurez-vous que votre compte utilise un mot de passe long');
  });

  it('renders current password, password and confirmation inputs', () => {
    const wrapper = mount(UpdatePasswordForm);
    expect(wrapper.find('input#current_password').exists()).toBe(true);
    expect(wrapper.find('input#password').exists()).toBe(true);
    expect(wrapper.find('input#password_confirmation').exists()).toBe(true);
  });

  it('renders Save button', () => {
    const wrapper = mount(UpdatePasswordForm);
    const saveBtn = wrapper.findAll('button').find((w) => w.text().includes('Sauvegarder'));
    expect(saveBtn).toBeDefined();
    expect(saveBtn?.exists()).toBe(true);
    expect(wrapper.text()).toContain('Sauvegarder');
  });

  it('calls form.put on submit', async () => {
    const wrapper = mount(UpdatePasswordForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(mockForm.put).toHaveBeenCalledWith('/user/password', expect.any(Object));
  });
});
