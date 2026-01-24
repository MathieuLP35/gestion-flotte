import { describe, it, expect } from 'vitest';
import useDate from '@/Composables/useDate';

describe('useDate', () => {
  it('returns an object with formatDate function', () => {
    const { formatDate } = useDate();
    expect(typeof formatDate).toBe('function');
  });

  it('formats a date string with date and time in fr-FR', () => {
    const { formatDate } = useDate();
    const result = formatDate('2024-06-15T14:30:00.000Z');
    expect(result).toMatch(/^\d{1,2}\s+\w+\s+\d{4}\s+à\s+\d{1,2}:\d{2}$/);
    expect(result).toContain(' à ');
  });

  it('handles ISO date strings', () => {
    const { formatDate } = useDate();
    const result = formatDate('2025-01-01T12:00:00.000Z');
    expect(result).toBeDefined();
    expect(typeof result).toBe('string');
  });
});
