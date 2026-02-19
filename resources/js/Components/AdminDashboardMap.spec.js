import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import AdminDashboardMap from '@/Components/AdminDashboardMap.vue';
import L from 'leaflet';

// Configuration des mocks
const mockMap = {
    setView: vi.fn().mockReturnThis(),
    fitBounds: vi.fn().mockReturnThis(),
    remove: vi.fn(),
    addLayer: vi.fn().mockReturnThis(),
    removeLayer: vi.fn().mockReturnThis()
};

const mockTileLayer = { addTo: vi.fn().mockReturnThis() };

const mockClusterGroup = {
    addLayer: vi.fn(),
    clearLayers: vi.fn(),
    getAllChildMarkers: vi.fn(() => []),
    getChildCount: vi.fn(() => 0),
};

// Mock de Leaflet
vi.mock('leaflet', () => ({
    default: {
        map: vi.fn(() => mockMap),
        tileLayer: vi.fn(() => mockTileLayer),
        markerClusterGroup: vi.fn(() => mockClusterGroup),
        marker: vi.fn(() => ({
            addTo: vi.fn().mockReturnThis(),
            bindPopup: vi.fn().mockReturnThis(),
            options: {}
        })),
        divIcon: vi.fn(() => ({})),
        point: vi.fn((x, y) => ({ x, y })),
        Icon: { Default: { prototype: {}, mergeOptions: vi.fn() } },
    },
}));

// Mock de markercluster pour éviter les erreurs d'import CSS/JS
vi.mock('leaflet.markercluster', () => ({}));

describe('AdminDashboardMap', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
    });

    it('affiche le titre et la légende', () => {
        const wrapper = mount(AdminDashboardMap, {
            props: { reservations: [] },
        });
        expect(wrapper.text()).toContain('Carte des trajets');
        expect(wrapper.text()).toContain('Départ');
        expect(wrapper.text()).toContain('Arrivée');
    });

    it('gère l\'ouverture et la fermeture du plein écran', async () => {
        const wrapper = mount(AdminDashboardMap, {
            props: { reservations: [] },
            global: {
                stubs: {
                    // On ne stub PAS teleport pour qu'il rende vraiment son contenu
                    Teleport: false
                }
            }
        });

        // 1. Ouvrir
        const btn = wrapper.find('button[title="Agrandir la carte"]');
        await btn.trigger('click');

        expect(wrapper.vm.isFullScreen).toBe(true);
        await nextTick();
        await nextTick(); // Parfois deux ticks sont nécessaires pour le portail

        // On cherche dans le body global
        expect(document.body.innerHTML).toContain('Vue détaillée des trajets');

        // 2. Fermer
        const closeBtn = document.body.querySelector('button');
        if (closeBtn) {
            closeBtn.click();
            await nextTick();
            expect(wrapper.vm.isFullScreen).toBe(false);
        }
    });

    it('initialise la carte avec MarkerCluster', () => {
        mount(AdminDashboardMap, {
            props: { reservations: [] },
        });

        // Utilisation directe de l'import mocké
        expect(L.markerClusterGroup).toHaveBeenCalled();
        expect(mockMap.addLayer).toHaveBeenCalled();
    });

    it('ajuste les bornes quand des coordonnées sont présentes', async () => {
        mount(AdminDashboardMap, {
            props: {
                reservations: [
                    {
                        id: 1,
                        depart_latitude: 48.8,
                        depart_longitude: 2.3,
                        destination_latitude: 45.7,
                        destination_longitude: 4.8,
                    },
                ],
            },
        });

        expect(mockMap.fitBounds).toHaveBeenCalled();
    });
});
