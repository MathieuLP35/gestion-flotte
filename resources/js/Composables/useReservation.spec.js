import { describe, it, expect, vi, beforeEach } from 'vitest';
import useReservation from '@/Composables/useReservation';
import { router } from '@inertiajs/vue3';

describe('useReservation', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    global.confirm = vi.fn();
  });

  it('returns cancelPassenger and deleteReservation', () => {
    const { cancelPassenger, deleteReservation } = useReservation();
    expect(typeof cancelPassenger).toBe('function');
    expect(typeof deleteReservation).toBe('function');
  });

  it('cancelPassenger calls router.delete when confirm returns true', () => {
    global.confirm.mockReturnValue(true);
    const { cancelPassenger } = useReservation();
    cancelPassenger(42);
    expect(global.confirm).toHaveBeenCalledWith('Voulez-vous vraiment annuler votre place sur ce trajet ?');
    expect(router.delete).toHaveBeenCalledWith(expect.any(String), { preserveScroll: true, onSuccess: expect.any(Function) });
  });

  it('cancelPassenger does not call router.delete when confirm returns false', () => {
    global.confirm.mockReturnValue(false);
    const { cancelPassenger } = useReservation();
    cancelPassenger(42);
    expect(router.delete).not.toHaveBeenCalled();
  });

  it('deleteReservation calls router.delete when confirm returns true', () => {
    global.confirm.mockReturnValue(true);
    const { deleteReservation } = useReservation();
    deleteReservation(10);
    expect(global.confirm).toHaveBeenCalledWith('Êtes-vous sûr de vouloir supprimer cette réservation ?');
    expect(router.delete).toHaveBeenCalledWith(expect.any(String), { preserveScroll: true });
  });

  it('deleteReservation does not call router.delete when confirm returns false', () => {
    global.confirm.mockReturnValue(false);
    const { deleteReservation } = useReservation();
    deleteReservation(10);
    expect(router.delete).not.toHaveBeenCalled();
  });
});
