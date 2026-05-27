/**
 * useCanvasSync.js — Synchronize canvas to store with debouncing
 * Single source of truth for serialization
 * 
 * FIXES APPLIED:
 * 1. Added null/undefined checks for layer and serialized data
 * 2. Added try-catch for serialization errors (prevents unhandled exceptions)
 * 3. Added validation for pageIndex (prevents invalid state updates)
 * 4. Added debouncing to prevent rapid successive syncs (race condition fix)
 * 5. Added cleanup for pending syncs on unmount (memory leak fix)
 * 6. Added error logging for debugging
 * 7. Proper dependency array to prevent stale closures
 * 8. Added guard against empty serialized data
 * 9. Added timestamp tracking to prevent duplicate syncs
 * 10. Added proper error handling in store operations
 * 11. FIXED: Toolbar debounce bypass — refactored to use shared sync utility
 */

import { useCallback, useRef, useEffect } from 'react';
import { useTemplateStore } from '../store/useTemplateStore';
import { syncCanvasToStore } from '../utils/canvasSync';

// Debounce delay in milliseconds to prevent rapid successive syncs
const SYNC_DEBOUNCE_MS = 100;

/**
 * Hook for syncing content layer to store with debounce
 * 
 * @param {number} pageIndex - The index of the page to sync
 * @returns {Function} Sync function that takes a layer and syncs it to store
 */
export function useCanvasSync(pageIndex) {
  const updatePage = useTemplateStore((s) => s.updatePage);
  const saveState = useTemplateStore((s) => s.saveState);
  
  const pendingSyncRef = useRef(null);
  const lastSyncTimeRef = useRef(0);
  const isMountedRef = useRef(true);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      isMountedRef.current = false;
      if (pendingSyncRef.current) {
        clearTimeout(pendingSyncRef.current);
        pendingSyncRef.current = null;
      }
    };
  }, []);

  return useCallback(
    (layer) => {
      // Validate pageIndex
      if (typeof pageIndex !== 'number' || pageIndex < 0) {
        console.error('useCanvasSync: Invalid pageIndex', pageIndex);
        return;
      }

      // Validate layer
      if (!layer) {
        console.warn('useCanvasSync: Layer is null or undefined');
        return;
      }

      // Store actions for shared utility
      const storeActions = { updatePage, saveState };

      // Check if we should debounce
      const now = Date.now();
      const timeSinceLastSync = now - lastSyncTimeRef.current;

      if (timeSinceLastSync < SYNC_DEBOUNCE_MS) {
        // Clear any pending sync and schedule a new one
        if (pendingSyncRef.current) {
          clearTimeout(pendingSyncRef.current);
        }

        pendingSyncRef.current = setTimeout(() => {
          if (!isMountedRef.current) return;
          
          const success = syncCanvasToStore(layer, pageIndex, storeActions);
          if (success) {
            lastSyncTimeRef.current = Date.now();
          }
          pendingSyncRef.current = null;
        }, SYNC_DEBOUNCE_MS);

        return;
      }

      // Perform immediate sync
      const success = syncCanvasToStore(layer, pageIndex, storeActions);
      if (success) {
        lastSyncTimeRef.current = Date.now();
      }
    },
    [pageIndex, updatePage, saveState]
  );
}

export default useCanvasSync;

