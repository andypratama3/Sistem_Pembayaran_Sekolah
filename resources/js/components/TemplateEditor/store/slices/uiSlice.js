/**
 * uiSlice.js — UI state management
 * Handles zoom, grid, selection, and UI preferences
 * 
 * FIXES APPLIED:
 * 1. Added null checks for all setters
 * 2. Added validation for numeric values
 * 3. Added bounds checking for zoom
 * 4. ISSUE #3 FIX: Added note that selectedObject stores Konva nodes
 *    (Future: should migrate to string IDs, but requires PropertiesPanel refactor)
 *    For now, we accept the node reference but document the limitation:
 *    - Breaks time-travel debugging (nodes have live state)
 *    - Breaks serialization (nodes not JSON-serializable)
 *    - Not compatible with Redux DevTools
 */

export const uiSlice = (set, get) => ({
  zoom: 1,
  gridEnabled: false,
  gridSize: 20,
  snappingEnabled: true,
  selectedObjectId: null, // ISSUE #3 FIX: Store node ID (string) instead of node reference
  hasUnsavedChanges: false,
  pageSize: 'a4_portrait',
  pageOrientation: 'portrait',
  isSaving: false,
  autoSaveEnabled: true,
  lastSavedAt: null,

  setZoom: (zoom) => {
    // ISSUE #10 FIX: Added null check and validation
    if (zoom === null || zoom === undefined || typeof zoom !== 'number') {
      console.error('Invalid zoom value');
      return;
    }
    set({ zoom: Math.max(0.1, Math.min(5, zoom)) });
  },

  setGridEnabled: (gridEnabled) => {
    // ISSUE #10 FIX: Added null check
    if (gridEnabled === null || gridEnabled === undefined) {
      return;
    }
    set({ gridEnabled: Boolean(gridEnabled) });
  },

  setGridSize: (gridSize) => {
    // ISSUE #10 FIX: Added null check and validation
    if (gridSize === null || gridSize === undefined || typeof gridSize !== 'number' || gridSize <= 0) {
      console.error('Invalid grid size');
      return;
    }
    set({ gridSize });
  },

  setSnappingEnabled: (snappingEnabled) => {
    // ISSUE #10 FIX: Added null check
    if (snappingEnabled === null || snappingEnabled === undefined) {
      return;
    }
    set({ snappingEnabled: Boolean(snappingEnabled) });
  },

  setSelectedObject: (selectedObject) => {
    // ISSUE #3 FIX: Store object ID (string) instead of node reference
    // If passed a string, use it directly; if passed a node, extract its ID
    if (selectedObject === null || selectedObject === undefined) {
      set({ selectedObjectId: null });
    } else if (typeof selectedObject === 'string') {
      set({ selectedObjectId: selectedObject });
    } else if (typeof selectedObject === 'object' && typeof selectedObject.id === 'function') {
      // Konva node object — extract its ID
      const nodeId = selectedObject.id?.() || null;
      set({ selectedObjectId: nodeId });
    } else {
      console.warn('setSelectedObject: invalid argument type');
    }
  },

  // ISSUE #3 FIX: Helper to resolve node ID back to node instance
  // Requires access to contentLayer to find the node by ID
  getSelectedNodeById: (contentLayer) => {
    const state = get();
    const nodeId = state.selectedObjectId;
    
    if (!nodeId || !contentLayer) return null;
    
    try {
      // Search all children of the content layer for a node with matching ID
      const children = contentLayer.getChildren?.() || [];
      for (const node of children) {
        if (node.id?.() === nodeId) {
          return node;
        }
      }
    } catch (error) {
      console.warn('Error resolving selected node by ID:', error);
    }
    
    return null;
  },

  setHasUnsavedChanges: (hasUnsavedChanges) => {
    // ISSUE #10 FIX: Added null check
    if (hasUnsavedChanges === null || hasUnsavedChanges === undefined) {
      return;
    }
    set({ hasUnsavedChanges: Boolean(hasUnsavedChanges) });
  },

  setPageSize: (pageSize) => {
    // ISSUE #10 FIX: Added null check
    if (!pageSize || typeof pageSize !== 'string') {
      console.error('Invalid page size');
      return;
    }
    set({ pageSize });
  },

  setPageOrientation: (pageOrientation) => {
    // ISSUE #10 FIX: Added null check and validation
    if (!pageOrientation || typeof pageOrientation !== 'string') {
      console.error('Invalid page orientation');
      return;
    }
    set({ pageOrientation });
  },

  setIsSaving: (isSaving) => {
    // ISSUE #10 FIX: Added null check
    if (isSaving === null || isSaving === undefined) {
      return;
    }
    set({ isSaving: Boolean(isSaving) });
  },

  setAutoSaveEnabled: (autoSaveEnabled) => {
    // ISSUE #10 FIX: Added null check
    if (autoSaveEnabled === null || autoSaveEnabled === undefined) {
      return;
    }
    set({ autoSaveEnabled: Boolean(autoSaveEnabled) });
  },

  setLastSavedAt: (lastSavedAt) => {
    // ISSUE #10 FIX: Allow null for clearing, validate Date objects
    if (lastSavedAt === null || lastSavedAt === undefined) {
      set({ lastSavedAt: null });
    } else if (lastSavedAt instanceof Date) {
      set({ lastSavedAt });
    } else {
      console.error('Invalid lastSavedAt value');
    }
  },
});

export default uiSlice;
