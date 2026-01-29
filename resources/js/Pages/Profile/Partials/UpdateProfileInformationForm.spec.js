import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

const mockForm = {
  name: 'Jean Dupont',
  email: 'jean@example.com',
  patch: vi.fn(),
  errors: {},
  recentlySuccessful: false,
};

vi.mock('@inertiajs/vue3', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    useForm: (initial) => ({ ...initial, ...mockForm }),
    usePage: () => ({
      props: {
        auth: {
          user: {
            name: 'Jean Dupont',
            email: 'jean@example.com',
            email_verified_at: null,
          },
        },
      },
    }),
  };
});

beforeEach(() => {
  window.route = vi.fn((name) => (name === 'profile.update' ? '/profile' : name === 'verification.send' ? '/email/verification-notification' : '/'));
  vi.clearAllMocks();
});

describe('Profile/Partials/UpdateProfileInformationForm', () => {
  it('renders Profile Information section', () => {
    const wrapper = mount(UpdateProfileInformationForm);
    expect(wrapper.text()).toContain('Informations du profil');
    expect(wrapper.text()).toContain('Mettez à jour les informations de votre profil');
  });

  it('renders name and email inputs with user data', () => {
    const wrapper = mount(UpdateProfileInformationForm);
    expect(wrapper.find('input#name').exists()).toBe(true);
    expect(wrapper.find('input#email').exists()).toBe(true);
    expect(wrapper.find('input#name').element.value).toBe('Jean Dupont');
    expect(wrapper.find('input#email').element.value).toBe('jean@example.com');
  });

  it('calls form.patch on submit', async () => {
    const wrapper = mount(UpdateProfileInformationForm);
    await wrapper.find('form').trigger('submit.prevent');
    expect(mockForm.patch).toHaveBeenCalled();
  });

  it('shows Save button', () => {
    const wrapper = mount(UpdateProfileInformationForm);
    const saveBtn = wrapper.findAll('button').find((w) => w.text().includes('Sauvegarder'));
    expect(saveBtn).toBeDefined();
    expect(saveBtn?.text()).toContain('Sauvegarder');
  });

  it('shows verification message when mustVerifyEmail and email not verified', () => {
    const wrapper = mount(UpdateProfileInformationForm, {
      props: { mustVerifyEmail: true },
    });
    expect(wrapper.text()).toContain('Votre adresse courriel n\'est pas vérifiée');
    expect(wrapper.text()).toContain('Cliquez ici pour renvoyer l\'e-mail de vérification');
  });
});
