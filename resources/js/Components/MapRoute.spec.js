import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import MapRoute from '@/Components/MapRoute.vue';

vi.mock('leaflet', () => {
  const addTo = vi.fn();
  return {
    __esModule: true,
    default: {
      map: vi.fn(() => ({ setView: vi.fn(), addTo })),
      tileLayer: vi.fn(() => ({ addTo: vi.fn() })),
      latLng: vi.fn((a, b) => [a, b]),
      marker: vi.fn(() => ({ addTo: vi.fn().mockReturnThis(), bindPopup: vi.fn().mockReturnThis() })),
      divIcon: vi.fn(() => ({})),
      Routing: {
        control: vi.fn(() => ({ addTo: vi.fn() })),
        osrmv1: vi.fn(() => ({})),
        Formatter: vi.fn(function () {}),
      },
      Icon: { Default: { prototype: { _getIconUrl: undefined }, mergeOptions: vi.fn() } },
    },
  };
});
vi.mock('leaflet-routing-machine', () => ({}));

describe('MapRoute', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders map container and GPS button', () => {
    const wrapper = mount(MapRoute, {
      props: { startCoords: [48.8, 2.3], endCoords: [48.9, 2.4] },
    });
    expect(wrapper.findAll('div').length).toBeGreaterThanOrEqual(1);
    expect(wrapper.find('button').exists()).toBe(true);
    expect(wrapper.find('button').text()).toContain('Ouvrir dans GPS');
  });

  it('opens Google Maps on desktop when clicking Ouvrir dans GPS', async () => {
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null);
    Object.defineProperty(navigator, 'userAgent', { value: 'Mozilla/5.0 Windows NT 10.0', configurable: true });

    const wrapper = mount(MapRoute, {
      props: { startCoords: [48.8, 2.3], endCoords: [48.9, 2.4] },
    });
    await wrapper.find('button').trigger('click');

    expect(openSpy).toHaveBeenCalledWith('https://www.google.com/maps/dir/?api=1&destination=48.9,2.4', '_blank');
    openSpy.mockRestore();
  });

  it('sets window.location.href on iOS when clicking Ouvrir dans GPS', async () => {
    const loc = { href: '' };
    Object.defineProperty(window, 'location', { value: loc, configurable: true });
    Object.defineProperty(navigator, 'userAgent', { value: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)', configurable: true });

    const wrapper = mount(MapRoute, {
      props: { startCoords: [48.8, 2.3], endCoords: [48.9, 2.4] },
    });
    await wrapper.find('button').trigger('click');

    expect(loc.href).toBe('maps://?q=Destination&ll=48.9,2.4');
  });

  it('sets window.location.href on Android when clicking Ouvrir dans GPS', async () => {
    const loc = { href: '' };
    Object.defineProperty(window, 'location', { value: loc, configurable: true });
    Object.defineProperty(navigator, 'userAgent', { value: 'Mozilla/5.0 (Linux; Android 10)', configurable: true });

    const wrapper = mount(MapRoute, {
      props: { startCoords: [48.8, 2.3], endCoords: [48.9, 2.4] },
    });
    await wrapper.find('button').trigger('click');

    expect(loc.href).toMatch(/^geo:0,0\?q=48\.9,2\.4\(Destination\)$/);
  });
});
