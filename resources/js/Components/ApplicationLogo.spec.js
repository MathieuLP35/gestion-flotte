import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

describe('ApplicationLogo', () => {
    it('renders an image with the correct src and alt', () => {
        const wrapper = mount(ApplicationLogo);

        const img = wrapper.find('img');
        expect(img.attributes('src')).toBe('/images/logo.png');
        expect(img.attributes('alt')).toBe('Logo SparkOtto');
    });
});
