import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Modal from '@/Components/Modal.vue';

describe('Modal', () => {
  beforeEach(() => {
    document.body.style.overflow = '';
    vi.useFakeTimers();
    if (typeof HTMLDialogElement !== 'undefined' && !HTMLDialogElement.prototype.showModal) {
      HTMLDialogElement.prototype.showModal = vi.fn();
      HTMLDialogElement.prototype.close = vi.fn();
    }
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('renders slot content when show is true', async () => {
    const wrapper = mount(Modal, {
      props: { show: true },
      slots: { default: '<p>Contenu du modal</p>' },
    });
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Contenu du modal');
  });

  it('emits close when backdrop is clicked and closeable is true', async () => {
    const wrapper = mount(Modal, {
      props: { show: true, closeable: true },
      slots: { default: '<div>Content</div>' },
    });
    await wrapper.vm.$nextTick();
    // Premier .fixed.inset-0 = zone scroll, second = overlay avec @click
    const overlay = wrapper.findAll('.fixed.inset-0').at(1);
    await overlay.trigger('click');
    expect(wrapper.emitted('close')).toBeDefined();
    expect(wrapper.emitted('close')).toHaveLength(1);
  });

  it('does not emit close when closeable is false', async () => {
    const wrapper = mount(Modal, {
      props: { show: true, closeable: false },
      slots: { default: '<div>Content</div>' },
    });
    await wrapper.vm.$nextTick();
    const overlay = wrapper.findAll('.fixed.inset-0').at(1);
    await overlay.trigger('click');
    expect(wrapper.emitted('close')).toBeUndefined();
  });

  it('applies maxWidth class 2xl by default', async () => {
    const wrapper = mount(Modal, {
      props: { show: true },
      slots: { default: '<div>X</div>' },
    });
    await wrapper.vm.$nextTick();
    const content = wrapper.find('.sm\\:max-w-2xl');
    expect(content.exists()).toBe(true);
  });

  it('applies maxWidth class when prop is lg', async () => {
    const wrapper = mount(Modal, {
      props: { show: true, maxWidth: 'lg' },
      slots: { default: '<div>X</div>' },
    });
    await wrapper.vm.$nextTick();
    const content = wrapper.find('.sm\\:max-w-lg');
    expect(content.exists()).toBe(true);
  });

  it('listens to Escape key and emits close when closeable', async () => {
    const wrapper = mount(Modal, {
      props: { show: true, closeable: true },
      slots: { default: '<div>X</div>' },
    });
    await wrapper.vm.$nextTick();
    await document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    expect(wrapper.emitted('close')).toHaveLength(1);
  });

  it('does not emit close on other keys', async () => {
    const wrapper = mount(Modal, {
      props: { show: true, closeable: true },
      slots: { default: '<div>X</div>' },
    });
    await wrapper.vm.$nextTick();
    await document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
    expect(wrapper.emitted('close')).toBeUndefined();
  });
});
