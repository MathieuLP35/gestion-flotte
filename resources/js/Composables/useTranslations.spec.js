import { describe, it, expect, vi, beforeEach } from 'vitest';
import useTranslations from '@/Composables/useTranslations';

describe('useTranslations', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    global.fetch = vi.fn();
  });

  it('returns t, load and translations', () => {
    const { t, load, translations } = useTranslations();
    expect(typeof t).toBe('function');
    expect(typeof load).toBe('function');
    expect(translations).toBeDefined();
  });

  it('t returns key when translation is missing', () => {
    const { t } = useTranslations();
    expect(t('permissions.roles.view')).toBe('permissions.roles.view');
  });

  it('load fetches and merges translations into cache', async () => {
    global.fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ 'permissions.roles.view': 'Voir les rôles' }),
    });

    const { load, t } = useTranslations();
    await load(['permissions.roles.view']);

    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('permissions.roles.view'),
      expect.any(Object)
    );
    expect(t('permissions.roles.view')).toBe('Voir les rôles');
  });

  it('load uses default URL when no keys provided', async () => {
    global.fetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({}) });

    const { load } = useTranslations();
    await load();

    expect(global.fetch).toHaveBeenCalledWith('/api/translations', expect.any(Object));
  });

  it('load does not update cache when response is not ok', async () => {
    global.fetch.mockResolvedValue({ ok: false });

    const { load, t } = useTranslations();
    await load(['foo']);
    expect(t('foo')).toBe('foo');
  });

  it('load catches fetch error and keeps cache', async () => {
    global.fetch.mockRejectedValue(new Error('Network error'));

    const { load, t } = useTranslations();
    await load(['foo']);
    expect(t('foo')).toBe('foo');
  });
});
