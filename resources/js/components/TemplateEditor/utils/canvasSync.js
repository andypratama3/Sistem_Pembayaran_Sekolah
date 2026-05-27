/**
 * canvasSync.js — Shared synchronization logic for canvas → store
 *
 * Centralized sync behavior used by:
 *   - useCanvasSync hook (with debouncing)
 *   - Toolbar.jsx (direct calls)
 *   - Other canvas operations (undo/redo, paste, etc)
 *
 * This ensures consistent sync behavior across all canvas modifications.
 */

import { CanvasSerializer } from '../canvas/CanvasSerializer';

/**
 * Perform immediate canvas sync (no debounce)
 * 
 * Used by:
 *   - Toolbar when adding elements
 *   - Direct canvas operations (undo/redo)
 *   - Any operation that should update store immediately
 *
 * @param {Konva.Layer} layer - Content layer to serialize
 * @param {number} pageIndex - Page index to update
 * @param {object} storeActions - Store methods { updatePage, saveState }
 * @throws {Error} If serialization or store update fails
 */
export function syncCanvasToStore(layer, pageIndex, storeActions) {
  if (!layer) {
    console.warn('canvasSync: Layer is null or undefined');
    return false;
  }

  if (typeof pageIndex !== 'number' || pageIndex < 0) {
    console.error('canvasSync: Invalid pageIndex', pageIndex);
    return false;
  }

  const { updatePage, saveState } = storeActions;
  if (!updatePage || !saveState) {
    console.error('canvasSync: Store functions are not available');
    return false;
  }

  try {
    // Serialize layer to schema
    const serialized = CanvasSerializer.serializeLayer(layer);

    // Validate serialized data
    if (!Array.isArray(serialized)) {
      console.error('canvasSync: Serialization did not return an array', serialized);
      return false;
    }

    // Update store
    try {
      updatePage(pageIndex, { objects: serialized });
      saveState();
      return true;
    } catch (storeError) {
      console.error('canvasSync: Error updating store:', storeError);
      return false;
    }
  } catch (serializationError) {
    console.error('canvasSync: Error serializing layer:', serializationError);
    return false;
  }
}

export default syncCanvasToStore;
