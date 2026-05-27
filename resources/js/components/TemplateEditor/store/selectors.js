/**
 * selectors.js — Memoized selectors for Zustand store
 * Prevents unnecessary re-renders by selecting only needed state
 */

// Stable references to avoid creating new arrays/objects on every selector call,
// which would cause unnecessary re-renders for consumers.
const EMPTY_ARRAY = Object.freeze([]);

export const selectActivePage = (s) => s.pages[s.activePageIndex];

export const selectActivePageObjects = (s) =>
  s.pages[s.activePageIndex]?.objects || EMPTY_ARRAY;

export const selectCanUndo = (s) => s.historyIndex > 0;

export const selectCanRedo = (s) =>
  s.historyIndex < s.history.length - 1;

export const selectPageCount = (s) => s.pages.length;

export const selectHasUnsavedChanges = (s) => s.hasUnsavedChanges;

export const selectZoom = (s) => s.zoom;

export const selectGridEnabled = (s) => s.gridEnabled;

export const selectGridSize = (s) => s.gridSize;

export const selectSnappingEnabled = (s) => s.snappingEnabled;

export const selectSelectedObject = (s) => s.selectedObject;

export const selectFields = (s) => s.fields;

export const selectClipboard = (s) => s.clipboard;

export const selectActivePageIndex = (s) => s.activePageIndex;

export const selectPageSize = (s) => s.pageSize;

export const selectPageOrientation = (s) => s.pageOrientation;

export const selectIsSaving = (s) => s.isSaving;

export const selectAutoSaveEnabled = (s) => s.autoSaveEnabled;

export const selectLastSavedAt = (s) => s.lastSavedAt;

export default {
  selectActivePage,
  selectActivePageObjects,
  selectCanUndo,
  selectCanRedo,
  selectPageCount,
  selectHasUnsavedChanges,
  selectZoom,
  selectGridEnabled,
  selectGridSize,
  selectSnappingEnabled,
  selectSelectedObject,
  selectFields,
  selectClipboard,
  selectActivePageIndex,
  selectPageSize,
  selectPageOrientation,
  selectIsSaving,
  selectAutoSaveEnabled,
  selectLastSavedAt,
};
