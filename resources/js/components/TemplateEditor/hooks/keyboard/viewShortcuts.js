/**
 * viewShortcuts.js — Dispatch table for view (zoom) shortcuts.
 *
 * Same shape as editingShortcuts; orchestrator runs both arrays in order.
 */

import { ctrl } from './keyMatchers';

const ZOOM_STEP = 0.1;

export const viewShortcuts = [
  {
    // Ctrl+= / Ctrl++  (=  doesn't require shift on US layout, but `+` does;
    // checking lowercase covers both).
    match: (e) => (e.ctrlKey || e.metaKey) && (e.key === '=' || e.key === '+'),
    run: ({ store }) => store.setZoom((store.zoom ?? 1) + ZOOM_STEP),
  },
  {
    match: ctrl('-'),
    run: ({ store }) => store.setZoom((store.zoom ?? 1) - ZOOM_STEP),
  },
  {
    match: ctrl('0'),
    run: ({ store }) => store.setZoom(1),
  },
];
