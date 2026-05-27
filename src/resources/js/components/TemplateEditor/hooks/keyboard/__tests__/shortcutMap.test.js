import { describe, it, expect } from 'vitest';
import { SHORTCUTS, formatShortcut } from '../shortcutMap';

describe('SHORTCUTS registry', () => {
  it('exposes the documented shortcuts', () => {
    const required = ['SELECT_ALL', 'DESELECT', 'DUPLICATE', 'DELETE', 'COPY', 'PASTE',
      'GROUP', 'UNGROUP', 'LOCK', 'HIDE', 'UNDO', 'REDO', 'SAVE', 'HELP'];
    for (const k of required) {
      expect(SHORTCUTS[k]).toBeDefined();
      expect(SHORTCUTS[k].label).toBeTruthy();
      expect(SHORTCUTS[k].key).toBeTruthy();
    }
  });

  it('is frozen (immutable)', () => {
    expect(() => { SHORTCUTS.NEW_ENTRY = { key: 'x', label: 'x' }; }).toThrow();
  });
});

describe('formatShortcut()', () => {
  it('formats Ctrl+key', () => {
    expect(formatShortcut({ ctrl: true, key: 's' })).toBe('Ctrl+s');
  });

  it('formats Ctrl+Shift+key', () => {
    expect(formatShortcut({ ctrl: true, shift: true, key: 'z' })).toBe('Ctrl+Shift+z');
  });

  it('renders Space for the space key', () => {
    expect(formatShortcut({ key: ' ' })).toBe('Space');
  });

  it('formats bare keys like Escape', () => {
    expect(formatShortcut({ key: 'Escape' })).toBe('Escape');
  });

  it('orders modifiers Ctrl, Shift, Alt, Cmd', () => {
    expect(formatShortcut({ ctrl: true, shift: true, alt: true, meta: true, key: 'k' }))
      .toBe('Ctrl+Shift+Alt+Cmd+k');
  });
});
