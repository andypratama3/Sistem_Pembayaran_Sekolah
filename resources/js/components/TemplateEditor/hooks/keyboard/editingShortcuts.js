/**
 * editingShortcuts.js — Dispatch table for canvas editing shortcuts.
 *
 * Each entry is { match: (e) => bool, run: (ctx) => void }.
 * `ctx` is built fresh per keystroke by the orchestrator hook and contains:
 *   { e, contentLayer, transformer, selectedNode, selectedNodes, store, syncStore }
 *
 * Order matters — first match wins. More specific shortcuts (e.g. Ctrl+Shift+G)
 * MUST come before more general ones (Ctrl+G).
 */

import Konva from 'konva';
import { ctrl, ctrlShift, key, oneOf } from './keyMatchers';
import { CanvasSerializer } from '../../canvas/CanvasSerializer';
import { CanvasTable } from '../../canvas/CanvasTable';
import { LayerManager } from '../../canvas/LayerManager';

// Collision-resistant suffix: time-base36 + random-base36. Two paste operations
// in the same millisecond will still produce different IDs.
const uid = () =>
  `${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 9)}`;

/**
 * Snapshot a node into the clipboard. We store the JSON descriptor — NOT the
 * Konva node reference — so paste-after-cut still works after the source
 * node has been destroyed.
 */
const snapshotForClipboard = (node) => {
  const isCanvasTable = node.className === 'CanvasTable';
  return {
    node: isCanvasTable ? node.toTableJSON() : node.toObject(),
    type: node.className,
    className: node.className,
  };
};

/**
 * Build a fresh Konva node from a clipboard snapshot, offset by 20px so the
 * paste lands visibly next to the source.
 */
const cloneFromSnapshot = (snapshot) => {
  if (!snapshot?.node) return null;

  try {
    if (snapshot.className === 'CanvasTable') {
      if (!snapshot.node || typeof snapshot.node !== 'object') {
        console.error('Invalid CanvasTable snapshot:', snapshot);
        return null;
      }
      
      const data = {
        ...snapshot.node,
        id: `tbl_${uid()}`,
        x: (snapshot.node.x ?? 0) + 20,
        y: (snapshot.node.y ?? 0) + 20,
      };
      return CanvasTable.fromJSON(data);
    }

    // Deep-clone to avoid sharing nested attrs with the clipboard entry.
    const descriptor = JSON.parse(JSON.stringify(snapshot.node));
    if (descriptor.attrs) {
      descriptor.attrs.id = `${snapshot.className}_${uid()}`;
      descriptor.attrs.x = (descriptor.attrs.x ?? 0) + 20;
      descriptor.attrs.y = (descriptor.attrs.y ?? 0) + 20;
    }
    
    const node = Konva.Node.create(descriptor);
    if (!node) {
      console.error('Failed to create Konva node from snapshot:', snapshot);
      return null;
    }
    
    return node;
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error('Paste failed:', error, 'snapshot:', snapshot);
    return null;
  }
};

export const editingShortcuts = [
  // — Undo / Redo (must come before single-key fallthroughs) —
  { match: ctrlShift('z'), run: ({ store }) => store.redo() },
  { match: ctrl('y'),      run: ({ store }) => store.redo() },
  { match: ctrl('z'),      run: ({ store }) => store.undo() },

  // — Save —
  {
    match: ctrl('s'),
    run: ({ contentLayer, syncStore }) => syncStore(contentLayer),
  },

  // — Delete / Backspace —
  {
    match: oneOf('Delete', 'Backspace'),
    requireSelection: true,
    run: ({ selectedNode, transformer, contentLayer, syncStore, store }) => {
      if (!selectedNode || !transformer || !contentLayer) {
        console.warn('[Delete] Missing required objects for deletion');
        return;
      }
      
      try {
        transformer.nodes([]);
        selectedNode.destroy();
        contentLayer.batchDraw();
        // Clear store selection BEFORE syncing so subscribers don't receive a
        // stale (destroyed) node reference.
        store.setSelectedObject?.(null);
        syncStore(contentLayer);
      } catch (error) {
        console.error('[Delete] Error during deletion:', error);
      }
    },
  },

  // — Duplicate —
  {
    match: ctrl('d'),
    requireSelection: true,
    run: ({ selectedNode, transformer, contentLayer, syncStore }) => {
      if (!selectedNode || !transformer || !contentLayer) {
        console.warn('[Duplicate] Missing required objects');
        return;
      }
      
      try {
        const clone = CanvasSerializer.cloneNode(selectedNode, 20, 20);
        if (!clone) {
          console.warn('[Duplicate] Failed to clone node');
          return;
        }
        contentLayer.add(clone);
        transformer.nodes([clone]);
        contentLayer.batchDraw();
        syncStore(contentLayer);
      } catch (error) {
        console.error('[Duplicate] Error during duplication:', error);
      }
    },
  },

  // — Copy / Cut / Paste —
  // Clipboard stores a JSON snapshot, so paste works even after the source
  // node has been cut + destroyed.
  {
    match: ctrl('c'),
    requireSelection: true,
    run: ({ selectedNode, store }) => {
      store.setClipboard(snapshotForClipboard(selectedNode));
    },
  },
  {
    match: ctrl('x'),
    requireSelection: true,
    run: ({ selectedNode, transformer, contentLayer, syncStore, store }) => {
      store.setClipboard(snapshotForClipboard(selectedNode));
      transformer.nodes([]);
      selectedNode.destroy();
      contentLayer?.batchDraw();
      // Clear store selection so UI doesn't keep a destroyed reference.
      store.setSelectedObject?.(null);
      syncStore(contentLayer);
    },
  },
  {
    match: ctrl('v'),
    run: ({ contentLayer, transformer, syncStore, store }) => {
      if (!contentLayer || !transformer) {
        console.warn('[Paste] Missing required objects');
        return;
      }
      
      const snapshot = store.clipboard;
      if (!snapshot) return;
      
      try {
        const pasted = cloneFromSnapshot(snapshot);
        if (!pasted) {
          console.warn('[Paste] Failed to clone from snapshot');
          return;
        }
        contentLayer.add(pasted);
        transformer.nodes([pasted]);
        contentLayer.batchDraw();
        syncStore(contentLayer);
      } catch (error) {
        console.error('[Paste] Error during paste:', error);
      }
    },
  },

  // — Selection —
  {
    match: ctrl('a'),
    run: ({ contentLayer, transformer }) => {
      if (!contentLayer || !transformer) {
        console.warn('[SelectAll] Missing required objects');
        return;
      }
      
      try {
        const all = contentLayer.getChildren() || [];
        transformer.nodes([...all]);
        contentLayer.batchDraw();
      } catch (error) {
        console.error('[SelectAll] Error during selection:', error);
      }
    },
  },
  {
    match: key('Escape'),
    run: ({ transformer, contentLayer }) => {
      if (!transformer || !contentLayer) {
        console.warn('[Escape] Missing required objects');
        return;
      }
      
      try {
        transformer.nodes([]);
        contentLayer.batchDraw();
      } catch (error) {
        console.error('[Escape] Error during deselection:', error);
      }
    },
  },

  // — Lock / Hide —
  {
    match: ctrl('l'),
    requireSelection: true,
    run: ({ selectedNode, transformer, contentLayer, syncStore }) => {
      if (!selectedNode || !transformer || !contentLayer) {
        console.warn('[Lock] Missing required objects');
        return;
      }
      
      try {
        LayerManager.toggleLock(selectedNode, transformer);
        syncStore(contentLayer);
      } catch (error) {
        console.error('[Lock] Error toggling lock:', error);
      }
    },
  },
  {
    match: ctrl('h'),
    requireSelection: true,
    run: ({ selectedNode, contentLayer, syncStore }) => {
      if (!selectedNode || !contentLayer) {
        console.warn('[Hide] Missing required objects');
        return;
      }
      
      try {
        LayerManager.toggleVisibility(selectedNode);
        syncStore(contentLayer);
      } catch (error) {
        console.error('[Hide] Error toggling visibility:', error);
      }
    },
  },

  // — Group / Ungroup (Shift variant must come first) —
  {
    match: ctrlShift('g'),
    run: ({ selectedNode, contentLayer, transformer, syncStore }) => {
      if (!selectedNode || !contentLayer || !transformer) {
        console.warn('[Ungroup] Missing required objects');
        return;
      }
      
      try {
        if (selectedNode?.className !== 'Group') {
          console.warn('[Ungroup] Selected node is not a group');
          return;
        }
        const ungrouped = LayerManager.ungroupNodes(selectedNode, contentLayer);
        if (!ungrouped || ungrouped.length === 0) {
          console.warn('[Ungroup] Failed to ungroup nodes');
          return;
        }
        transformer.nodes(ungrouped);
        syncStore(contentLayer);
      } catch (error) {
        console.error('[Ungroup] Error ungrouping:', error);
      }
    },
  },
  {
    match: ctrl('g'),
    run: ({ selectedNodes, contentLayer, transformer, syncStore }) => {
      if (!contentLayer || !transformer) {
        console.warn('[Group] Missing required objects');
        return;
      }
      
      if (!selectedNodes || selectedNodes.length <= 1) {
        console.warn('[Group] Need at least 2 nodes to group');
        return;
      }
      
      try {
        const group = LayerManager.groupNodes(selectedNodes, contentLayer);
        if (!group) {
          console.warn('[Group] Failed to create group');
          return;
        }
        transformer.nodes([group]);
        syncStore(contentLayer);
      } catch (error) {
        console.error('[Group] Error grouping:', error);
      }
    },
  },
];
