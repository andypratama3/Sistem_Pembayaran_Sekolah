/**
 * useKeyboardShortcuts.js — Orchestrator that wires keyboard events to the
 * dispatch tables in `./keyboard/*`.
 *
 * Design:
 *   - One global keydown listener (window-level), attached once on mount.
 *   - The handler reads everything it needs from `useTemplateStore.getState()`
 *     plus the stage registry. Nothing closes over component state, so the
 *     handler never goes stale.
 *   - Dispatch tables are simple arrays of {match, run} entries scanned in
 *     order. First match wins; we exit early.
 *
 * @returns {{ showHelpModal: boolean, setShowHelpModal: (v: boolean) => void }}
 */

import { useEffect, useState } from 'react';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { CanvasSerializer } from '../canvas/CanvasSerializer';
import { editingShortcuts } from './keyboard/editingShortcuts';
import { viewShortcuts } from './keyboard/viewShortcuts';
import { navigationShortcuts } from './keyboard/navigationShortcuts';

// Re-exports for backwards compatibility with existing imports.
export { SHORTCUTS, formatShortcut } from './keyboard/shortcutMap';

const ALL_SHORTCUTS = [
  ...editingShortcuts,
  ...viewShortcuts,
  ...navigationShortcuts,
];

const isTextInput = (target) => {
  if (!target) return false;
  if (target.isContentEditable) return true;
  // Some hosts may set contenteditable as an attribute even when the property
  // isn't reflected; check defensively.
  if (typeof target.getAttribute === 'function') {
    const ce = target.getAttribute('contenteditable');
    if (ce === '' || ce === 'true' || ce === 'plaintext-only') return true;
  }
  return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);
};

// Generate a collision-resistant suffix combining time + randomness.
// Exported for use by editing shortcuts on paste/duplicate.
export const uniqueIdSuffix = () =>
  `${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 9)}`;

const buildSyncStore = (pageIndex) => (layer) => {
  if (!layer) return;
  const serialized = CanvasSerializer.serializeLayer(layer);
  const store = useTemplateStore.getState();
  store.updatePage(pageIndex, { objects: serialized });
  store.saveState();
};

export default function useKeyboardShortcuts() {
  const [showHelpModal, setShowHelpModal] = useState(false);

  useEffect(() => {
    const handler = (e) => {
      // Don't intercept while the user is typing in form controls.
      if (isTextInput(e.target)) return;

      // Help modal — Shift+?, but ignore when other modifiers are held so
      // Ctrl+Shift+? or Cmd+Shift+? don't accidentally open the help modal.
      if (e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey && e.key === '?') {
        e.preventDefault();
        setShowHelpModal(true);
        return;
      }

      const store = useTemplateStore.getState();
      const stageData = stageRegistry.get(store.activePageIndex);
      if (!stageData) return;

      const { contentLayer, transformer } = stageData;
      const selectedNodes = transformer?.nodes() || [];
      const selectedNode = selectedNodes[0] ?? null;

      const ctx = {
        e,
        contentLayer,
        transformer,
        selectedNode,
        selectedNodes,
        store,
        syncStore: buildSyncStore(store.activePageIndex),
      };

      for (const shortcut of ALL_SHORTCUTS) {
        if (!shortcut.match(e)) continue;
        if (shortcut.requireSelection && !selectedNode) continue;
        e.preventDefault();
        try {
          shortcut.run(ctx);
        } catch (error) {
          // eslint-disable-next-line no-console
          console.error('[KeyboardShortcut] Handler threw:', error);
          // Ensure we don't propagate the event even if handler fails
          return;
        }
        return;
      }
    };

    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, []);

  return { showHelpModal, setShowHelpModal };
}
