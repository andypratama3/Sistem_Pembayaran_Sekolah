import { describe, it, expect } from 'vitest';
import { ctrl, ctrlShift, key, oneOf, all, any } from '../keyMatchers';

const ev = (k, opts = {}) => ({
  key: k,
  ctrlKey: opts.ctrl ?? false,
  metaKey: opts.meta ?? false,
  shiftKey: opts.shift ?? false,
  altKey: opts.alt ?? false,
});

describe('keyMatchers', () => {
  describe('ctrl()', () => {
    it('matches Ctrl+key without shift', () => {
      expect(ctrl('s')(ev('s', { ctrl: true }))).toBe(true);
    });

    it('matches Cmd+key (macOS) treating meta same as ctrl', () => {
      expect(ctrl('s')(ev('s', { meta: true }))).toBe(true);
    });

    it('rejects when shift is held', () => {
      expect(ctrl('s')(ev('s', { ctrl: true, shift: true }))).toBe(false);
    });

    it('rejects when no modifier is held', () => {
      expect(ctrl('s')(ev('s'))).toBe(false);
    });

    it('is case-insensitive on the key', () => {
      expect(ctrl('S')(ev('s', { ctrl: true }))).toBe(true);
      expect(ctrl('s')(ev('S', { ctrl: true }))).toBe(true);
    });
  });

  describe('ctrlShift()', () => {
    it('requires both ctrl AND shift', () => {
      expect(ctrlShift('z')(ev('z', { ctrl: true, shift: true }))).toBe(true);
      expect(ctrlShift('z')(ev('z', { ctrl: true }))).toBe(false);
      expect(ctrlShift('z')(ev('z', { shift: true }))).toBe(false);
    });
  });

  describe('key()', () => {
    it('matches a bare key without modifiers required', () => {
      expect(key('Escape')(ev('Escape'))).toBe(true);
    });

    it('still matches if modifiers happen to be held (orthogonal)', () => {
      expect(key('Escape')(ev('Escape', { ctrl: true }))).toBe(true);
    });
  });

  describe('oneOf()', () => {
    it('matches if event key is in the list', () => {
      const m = oneOf('ArrowUp', 'ArrowDown');
      expect(m(ev('ArrowUp'))).toBe(true);
      expect(m(ev('ArrowDown'))).toBe(true);
      expect(m(ev('ArrowLeft'))).toBe(false);
    });
  });

  describe('all() / any()', () => {
    it('all() requires every matcher to pass', () => {
      const m = all(ctrl('s'), (e) => e.shiftKey === false);
      expect(m(ev('s', { ctrl: true }))).toBe(true);
      expect(m(ev('s', { ctrl: true, shift: true }))).toBe(false);
    });

    it('any() passes when any matcher succeeds', () => {
      const m = any(ctrl('y'), ctrlShift('z'));
      expect(m(ev('y', { ctrl: true }))).toBe(true);
      expect(m(ev('z', { ctrl: true, shift: true }))).toBe(true);
      expect(m(ev('z'))).toBe(false);
    });
  });
});
