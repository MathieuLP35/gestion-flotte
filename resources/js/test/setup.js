import { vi } from 'vitest';
import { config } from '@vue/test-utils';

// --- Ziggy: route() — exposé via global.mocks pour que les templates Vue (route(...)) le trouvent ---
const routeFn = (name, ...params) => {
  if (name == null) return { current: () => false };
  const base = '/r/' + String(name).replace(/\./g, '/');
  return params.length ? `${base}/${params.join('/')}` : base;
};
if (!config.global) config.global = {};
config.global.mocks = { ...(config.global.mocks || {}), route: routeFn };
vi.stubGlobal('route', routeFn);

// --- Données par défaut pour usePage (pages Admin, etc.) ---
const defaultPageProps = {
  auth: {
    user: { id: 1, name: 'Test', email: 'test@test.com' },
    roles: [],
    permissions: [],
  },
  flash: {},
  vehicles: [{ id: 1, modele: 'Clio', immatriculation: 'AB-123' }],
  stats: { users_count: 0, vehicles_count: 0, reservations_count: 0 },
  maintenances: [],
  vehicleKey: { id: 1, emplacement_clef: 'Box', vehicle: { modele: 'Clio', immatriculation: 'AB-123' } },
  maintenance: { id: 1, vehicle_id: 1, km_alert_threshold: 10000, date_dernier_entretien: '2024-01-15' },
  roles: [],
  users: [],
  user: { id: 1, name: 'U', email: 'u@u.com', agence_id: null },
  agences: [{ id: 1, nom: 'Agence 1' }],
  vehicle: {
    id: 1,
    modele: 'Clio',
    immatriculation: 'AB-123',
    km_initial: 0,
    emplacement: 'X',
    nbr_places: 5,
    energie: 'essence',
    en_maintenance: 0,
    keys: [],
    maintenances: [],
  },
  translations: {},
  reservation: {
    id: 1,
    destination: 'Paris',
    depart: 'Lyon',
    vehicle_id: 1,
    date_debut: '2024-01-01T10:00:00',
    date_fin: '2024-01-01T18:00:00',
    depart_latitude: 48.8,
    depart_longitude: 2.3,
    destination_latitude: 48.9,
    destination_longitude: 2.4,
    driver: { name: 'D', id: 1 },
    vehicle: { modele: 'Clio', immatriculation: 'AB-123', energie: 'essence', km_initial: 1000 },
    passengers: [],
    messages: [],
  },
};

// --- $page (Inertia) pour les templates qui utilisent $page.props (ex. AdminLayout) ---
config.global.mocks = { ...config.global.mocks, $page: { props: defaultPageProps } };

// --- Inertia ---
vi.mock('@inertiajs/vue3', () => ({
  Link: {
    name: 'Link',
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
  Head: {
    name: 'Head',
    render: () => null,
  },
  usePage: vi.fn(() => ({ props: defaultPageProps })),
  useForm: (init = {}) => ({
    ...init,
    errors: {},
    processing: false,
    wasSuccessful: false,
    recentlySuccessful: false,
    clearErrors: vi.fn().mockReturnThis(),
    setError: vi.fn().mockReturnThis(),
    post: vi.fn(),
    get: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
    reset: vi.fn(),
  }),
  router: {
    post: vi.fn(),
    get: vi.fn(),
    visit: vi.fn(),
    delete: vi.fn(),
    put: vi.fn(),
  },
}));

// --- Axios (Reservations Edit/Show) ---
vi.mock('axios', () => ({
  default: {
    get: vi.fn().mockResolvedValue({ data: [] }),
    post: vi.fn().mockResolvedValue({ data: { id: 1, body: '', user: { id: 1 }, created_at: '' } }),
  },
}));

// --- Echo / Reverb (Reservations Edit/Show) ---
if (typeof window !== 'undefined') {
  window.Echo = {
    private: vi.fn(() => ({ listen: vi.fn(), leave: vi.fn() })),
  };
}
