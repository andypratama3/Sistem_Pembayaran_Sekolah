/**
 * useAutoSave.js — Periodic auto-save with cancellation on unmount.
 *
 * Reads from the Zustand store via getState() (no re-render on every keystroke).
 * Uses an AbortController so in-flight requests are cancelled if the component
 * unmounts or `enabled` flips to false.
 *
 * NOTE: callbacks (`getName`, `getDescription`) are stored in refs so the
 * effect doesn't restart every render when the parent passes inline arrows.
 * Without this, a new arrow on every render would clear the interval each
 * render and auto-save would never actually fire.
 */

import { useEffect, useRef, useCallback } from 'react';
import { useTemplateStore } from '../store/useTemplateStore';
import ApiService from '../services/api';

const DEFAULT_INTERVAL_MS = 30_000;

/**
 * @param {object} params
 * @param {string} params.saveUrl
 * @param {boolean} params.enabled
 * @param {() => string} params.getName
 * @param {() => string} params.getDescription
 * @param {number} [params.intervalMs=30000]
 */
export function useAutoSave({ saveUrl, enabled, getName, getDescription, getCategoryId, intervalMs = DEFAULT_INTERVAL_MS }) {
  const abortRef = useRef(null);
  const getNameRef = useRef(getName);
  const getDescriptionRef = useRef(getDescription);
  const getCategoryIdRef = useRef(getCategoryId);
  
  // Track active save request to prevent concurrent saves
  const activeSaveRef = useRef(null);
  
  // Cache resolved variables to prevent multiple re-resolutions
  const resolvedVarsRef = useRef(null);
  const lastFieldsRef = useRef(null);
  const lastPagesRef = useRef(null);

  // Keep callbacks in refs so the interval effect doesn't reset on re-renders.
  useEffect(() => { getNameRef.current = getName; }, [getName]);
  useEffect(() => { getDescriptionRef.current = getDescription; }, [getDescription]);
  useEffect(() => { getCategoryIdRef.current = getCategoryId; }, [getCategoryId]);

  // Memoize variable resolution to avoid re-resolving on every tick
  const getResolvedVariables = useCallback(() => {
    const state = useTemplateStore.getState();
    
    // Only re-resolve if fields or pages changed
    if (lastFieldsRef.current !== state.fields || lastPagesRef.current !== state.pages) {
      lastFieldsRef.current = state.fields;
      lastPagesRef.current = state.pages;
      resolvedVarsRef.current = state.fields.reduce((acc, field) => {
        acc[field.id] = field.value || '';
        return acc;
      }, {});
    }
    
    return resolvedVarsRef.current;
  }, []);

  useEffect(() => {
    if (!enabled || !saveUrl) return undefined;

    let cancelled = false;

    const tick = async () => {
      if (cancelled) return;
      
      // Prevent concurrent saves: if a save is already in flight, skip this tick
      if (activeSaveRef.current) return;

      const state = useTemplateStore.getState();
      const name = (getNameRef.current?.() || '').trim();

      if (!name) return;
      if (state.isSaving) return;
      if (!state.hasUnsavedChanges) return;

      // Create new controller for this save attempt
      const controller = new AbortController();
      const savePromise = (async () => {
        try {
          // Mark this save as active
          activeSaveRef.current = controller;
          
          useTemplateStore.getState().setIsSaving(true);
          
          // Use cached resolved variables instead of re-resolving each time
          const resolvedVars = getResolvedVariables();
          
           await ApiService.autosave(
             saveUrl,
             {
               name,
               description: getDescriptionRef.current?.() ?? '',
               category_id: getCategoryIdRef.current?.() ?? null,
               canvas_layout: state.pages,
               fields: state.fields,
               resolved_variables: resolvedVars,
             },
             { signal: controller.signal }
           );

          // Only mark saved if this save wasn't cancelled and component is still mounted
          if (!controller.signal.aborted && !cancelled && activeSaveRef.current === controller) {
            try {
              useTemplateStore.getState().markSaved();
            } catch (markError) {
              console.error('[AutoSave] Error marking saved:', markError);
            }
          }
        } catch (error) {
          // Only log non-abort errors
          if (error?.name !== 'AbortError' && !cancelled) {
            console.error('[AutoSave] Error:', error);
          }
        } finally {
          // Clear active save only if this controller is still the active one
          if (activeSaveRef.current === controller) {
            activeSaveRef.current = null;
            useTemplateStore.getState().setIsSaving(false);
          }
        }
      })();

      // Store the promise for potential cleanup
      abortRef.current = { controller, promise: savePromise };
    };

    const id = setInterval(tick, intervalMs);
    
    return () => {
      cancelled = true;
      clearInterval(id);
      
      // Abort any in-flight request
      if (abortRef.current?.controller) {
        abortRef.current.controller.abort();
      }
      
      // Clear all refs to prevent memory leaks
      abortRef.current = null;
      activeSaveRef.current = null;
      resolvedVarsRef.current = null;
      lastFieldsRef.current = null;
      lastPagesRef.current = null;
    };
  }, [enabled, saveUrl, intervalMs, getResolvedVariables]);
}

export default useAutoSave;

