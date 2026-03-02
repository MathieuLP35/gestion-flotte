import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import MobilityReport from '@/Pages/Admin/MobilityReport.vue';

const defaultStubs = {
    Head: true,
    Link: true,
    AdminLayout: { template: '<div><slot /></div>' },
    Doughnut: { template: '<div></div>' },
    Dropdown: { template: '<div><slot name="trigger" /><slot name="content" /></div>' },
    DropdownLink: { template: '<a><slot /></a>' }
};

describe('Admin/MobilityReport', () => {
    it('renders mobility report with default period', () => {
        const wrapper = mount(MobilityReport, {
            props: {
                reports: [
                    {
                        periodLabel: 'Année 2024',
                        stats: {
                            total_distance: 1200,
                            carpool_rate: 45,
                            total_co2_saved: 50.5,
                            carpools_count: 10,
                            delta_distance: 10,
                            delta_co2: 5,
                            delta_carpools: -2
                        },
                        agencesStats: [
                            { name: 'Rennes', co2_saved: 30, carpools_count: 6 }
                        ],
                        vehicleEnergyStats: [
                            { energie: 'electrique', count: 5 }
                        ]
                    }
                ],
            },
            global: { stubs: defaultStubs },
        });

        expect(wrapper.text()).toContain('Année 2024');
        expect(wrapper.text()).toContain('CO2 Global Économisé');
        expect(wrapper.text()).toContain('50.5kg');
        expect(wrapper.text()).toContain('Trajets Covoiturés');
    });

    it('renders mobility report with multiple periods for comparison', () => {
        const wrapper = mount(MobilityReport, {
            props: {
                reports: [
                    {
                        periodLabel: 'Février 2024',
                        stats: { total_distance: 500, carpool_rate: 40, total_co2_saved: 20, carpools_count: 5, delta_distance: 0, delta_co2: 0, delta_carpools: 0 },
                        agencesStats: [],
                        vehicleEnergyStats: []
                    },
                    {
                        periodLabel: 'Mars 2024',
                        stats: { total_distance: 600, carpool_rate: 50, total_co2_saved: 25, carpools_count: 8, delta_distance: 0, delta_co2: 0, delta_carpools: 0 },
                        agencesStats: [],
                        vehicleEnergyStats: []
                    }
                ],
            },
            global: { stubs: defaultStubs },
        });

        expect(wrapper.text()).toContain('Février 2024');
        expect(wrapper.text()).toContain('Mars 2024');
        expect(wrapper.text()).toContain('20kg');
        expect(wrapper.text()).toContain('25kg');
    });

    it('adds and removes comparison periods correctly', async () => {
        const wrapper = mount(MobilityReport, {
            props: {
                reports: [
                    {
                        periodLabel: 'Année 2024',
                        stats: { total_distance: 1200, carpool_rate: 45, total_co2_saved: 50.5, carpools_count: 10, delta_distance: 0, delta_co2: 0, delta_carpools: 0 },
                        agencesStats: [],
                        vehicleEnergyStats: []
                    }
                ]
            },
            global: { stubs: defaultStubs },
        });

        // Contains one period select block initially (year, month = 2 selects)
        expect(wrapper.findAll('select').length).toBe(2);

        // Simulate adding a period
        const addButton = wrapper.findAll('button').find(b => b.text().includes('Ajouter comparaison'));
        await addButton.trigger('click');
        expect(wrapper.findAll('select').length).toBe(4); // now 2 periods (4 selects)

        // Simulate clicking the remove button for the second period
        const removeButton = wrapper.findAll('button').find(b => b.classes().includes('text-red-300'));
        await removeButton.trigger('click');
        expect(wrapper.findAll('select').length).toBe(2); // back to 1 period
    });
});
