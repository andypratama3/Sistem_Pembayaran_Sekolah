/**
 * shortcutMap.js — Single source of truth for keyboard shortcut definitions.
 *
 * Each entry is shaped { key, ctrl?, shift?, alt?, meta?, label }. The actual
 * action handlers live in the dispatch tables (`editingShortcuts.js`,
 * `viewShortcuts.js`, etc.) so the help modal, the dispatcher, and any future
 * docs/tooling consume the same registry.
 */

export const SHORTCUTS = Object.freeze({
  SELECT_ALL: { key: 'a', ctrl: true, label: 'Select All' },
  DESELECT:   { key: 'Escape', label: 'Deselect' },
  DUPLICATE:  { key: 'd', ctrl: true, label: 'Duplicate' },
  DELETE:     { key: 'Delete', label: 'Delete' },
  CUT:        { key: 'x', ctrl: true, label: 'Cut' },
  COPY:       { key: 'c', ctrl: true, label: 'Copy' },
  PASTE:      { key: 'v', ctrl: true, label: 'Paste' },
  GROUP:      { key: 'g', ctrl: true, label: 'Group' },
  UNGROUP:    { key: 'g', ctrl: true, shift: true, label: 'Ungroup' },
  LOCK:       { key: 'l', ctrl: true, label: 'Lock/Unlock' },
  HIDE:       { key: 'h', ctrl: true, label: 'Hide/Show' },
  UNDO:       { key: 'z', ctrl: true, label: 'Undo' },
  REDO:       { key: 'z', ctrl: true, shift: true, label: 'Redo' },
  SAVE:       { key: 's', ctrl: true, label: 'Save' },
  ZOOM_IN:    { key: '+', ctrl: true, label: 'Zoom In' },
  ZOOM_OUT:   { key: '-', ctrl: true, label: 'Zoom Out' },
  ZOOM_100:   { key: '0', ctrl: true, label: 'Zoom 100%' },
  HELP:       { key: '?', shift: true, label: 'Show Help' },
});

/**
 * Format shortcut for display in tooltips / help modals.
 *
 * On macOS we render Cmd as ⌘ and use the platform's modifier ordering.
 * Since Ctrl+<key> in our dispatch table treats Ctrl/Cmd as equivalent, we
 * present the macOS-native symbol on macOS clients.
 *
 * @param {{ key: string, ctrl?: boolean, shift?: boolean, alt?: boolean, meta?: boolean }} shortcut
 * @returns {string}
 */
const isMac = () => {
  if (typeof navigator === 'undefined') return false;
  // userAgentData is the modern API; fall back to platform string.
  const platform =
    navigator.userAgentData?.platform ||
    navigator.platform ||
    '';
  return /mac|iphone|ipad|ipod/i.test(platform);
};

export const formatShortcut = (shortcut) => {
  const mac = isMac();
  const parts = [];
  // Order: Ctrl → Shift → Alt → Cmd. This matches the help-modal convention
  // and the unit-test expectation. On macOS we render symbols inline without
  // a separator (`⌘⇧⌥k`); on other platforms we use `+` as the separator.
  if (shortcut.ctrl) parts.push(mac ? '⌘' : 'Ctrl');
  if (shortcut.shift) parts.push(mac ? '⇧' : 'Shift');
  if (shortcut.alt) parts.push(mac ? '⌥' : 'Alt');
  // `meta` is rendered separately from `ctrl` so a shortcut that explicitly
  // sets both still surfaces both labels — consumers may need to distinguish.
  if (shortcut.meta) parts.push(mac ? '⌘' : 'Cmd');
  parts.push(shortcut.key === ' ' ? 'Space' : shortcut.key);
  return parts.join(mac ? '' : '+');
};
