import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Dropdown from '@/Components/Dropdown.vue';

describe('Dropdown', () => {
    it('renders trigger and content slots', () => {
        const wrapper = mount(Dropdown, {
            slots: {
                trigger: '<button>Menu</button>',
                content: '<a href="#">Item</a>',
            },
        });

        expect(wrapper.text()).toContain('Menu');
        expect(wrapper.find('a[href="#"]').exists()).toBe(true);
    });

    it('toggles open when trigger is clicked', async () => {
        const wrapper = mount(Dropdown, {
            slots: {
                trigger: '<button>Open</button>',
                content: '<div>Content</div>',
            },
        });

        await wrapper.find('.relative > div').trigger('click');

        const overlay = wrapper.find('.fixed.inset-0');
        expect(overlay.isVisible()).toBe(true);
    });

    it('closes when overlay is clicked', async () => {
        const wrapper = mount(Dropdown, {
            slots: {
                trigger: '<button>Open</button>',
                content: '<div>Content</div>',
            },
        });

        await wrapper.find('.relative div').trigger('click');
        await wrapper.find('.fixed.inset-0.z-40').trigger('click');

        const overlay = wrapper.find('.fixed.inset-0.z-40');
        expect(overlay.attributes('style')).toContain('display: none');
    });

    it('applies width class for default width 48', () => {
        const wrapper = mount(Dropdown, {
            props: { width: '48' },
            slots: {
                trigger: '<span>x</span>',
                content: '<span>y</span>',
            },
        });

        const contentDiv = wrapper.findAll('.absolute.z-50')[0];
        expect(contentDiv.classes()).toContain('w-48');
    });

    it('applies alignment classes for align right by default', () => {
        const wrapper = mount(Dropdown, {
            slots: {
                trigger: '<span>x</span>',
                content: '<span>y</span>',
            },
        });

        const contentDiv = wrapper.findAll('.absolute.z-50')[0];
        expect(contentDiv.classes()).toContain('end-0');
    });
});
