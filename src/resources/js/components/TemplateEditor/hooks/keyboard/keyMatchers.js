/**
 * keyMatchers.js — Tiny predicates for matching keyboard events.
 *
 * Each matcher takes a KeyboardEvent and returns true if the event matches.
 * Centralizing this keeps the dispatch table declarative and easy to test.
 */

/** Treat Ctrl and Cmd identically — works for macOS + Windows/Linux. */
const isModifier = (e) => e.ctrlKey || e.metaKey;

/**
 * Build a matcher for `Ctrl+<key>` (no shift).
 *
 * @param {string} key  — case-insensitive single key, e.g. 's'
 */
export const ctrl = (key) => (e) =>
  isModifier(e) && !e.shiftKey && e.key.toLowerCase() === key.toLowerCase();

/** Build a matcher for `Ctrl+Shift+<key>`. */
export const ctrlShift = (key) => (e) =>
  isModifier(e) && e.shiftKey && e.key.toLowerCase() === key.toLowerCase();

/** Match a bare key (no modifiers required). */
export const key = (k) => (e) => e.key === k;

/** Match any of a set of bare keys. */
export const oneOf = (...keys) => (e) => keys.includes(e.key);

/** Compose matchers — true when ALL given matchers return true. */
export const all = (...matchers) => (e) => matchers.every((m) => m(e));

/** Compose matchers — true when ANY given matcher returns true. */
export const any = (...matchers) => (e) => matchers.some((m) => m(e));
