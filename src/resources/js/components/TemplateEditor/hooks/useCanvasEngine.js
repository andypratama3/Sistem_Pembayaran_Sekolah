/**
 * useCanvasEngine.js — Main hook for Konva Stage lifecycle.
 *
 * Responsibilities:
 *   - Create the Konva Stage / Layers / Transformer when the container mounts.
 *   - Register the stage with the global template store.
 *   - Re-create the stage when the bound page's dimensions change.
 *   - Tear everything down on unmount, with no double-destroy.
 *
 * The returned values are kept in React state (not refs), so consumers
 * actually re-render when the stage becomes available or is replaced.
 */

import { useEffect, useState } from 'react';
import { CanvasEngine } from '../canvas/CanvasEngine';
import { useTemplateStore } from '../store/useTemplateStore';

/**
 * Hook for managing Konva Stage lifecycle.
 *
 * @param {React.RefObject<HTMLDivElement>} containerRef
 * @param {number} pageIndex
 */
export function useCanvasEngine(containerRef, pageIndex) {
  // Public state — components rely on these to render once the stage exists.
  const [engine, setEngine] = useState({
    stage: null,
    layer: null,
    transformer: null,
    uiLayer: null,
    gridLayer: null,
  });

  // Subscribe to the specific page so width/height/id changes drive a
  // re-create of the stage rather than silently desyncing from the model.
  const [pageDimensions, setPageDimensions] = useState(() => {
    const page = useTemplateStore.getState().pages?.[pageIndex];
    return page ? { id: page.id, width: page.width, height: page.height } : null;
  });

  useEffect(() => {
    // Initial sync (in case pageIndex changed without remount).
    const initial = useTemplateStore.getState().pages?.[pageIndex];
    if (initial) {
      setPageDimensions((prev) => {
        const next = { id: initial.id, width: initial.width, height: initial.height };
        if (prev && prev.id === next.id && prev.width === next.width && prev.height === next.height) {
          return prev;
        }
        return next;
      });
    } else {
      setPageDimensions(null);
    }

    // Subscribe to changes on just this page slot.
    const unsub = useTemplateStore.subscribe(
      (state) => state.pages?.[pageIndex],
      (page) => {
        if (!page) {
          setPageDimensions(null);
          return;
        }
        setPageDimensions((prev) => {
          if (prev && prev.id === page.id && prev.width === page.width && prev.height === page.height) {
            return prev;
          }
          return { id: page.id, width: page.width, height: page.height };
        });
      }
    );
    return unsub;
  }, [pageIndex]);

  useEffect(() => {
    if (!containerRef?.current || !pageDimensions) return undefined;

    const { width, height, id: pageId } = pageDimensions;
    if (!Number.isFinite(width) || !Number.isFinite(height) || width <= 0 || height <= 0) {
      return undefined;
    }

    let createdEngine = null;
    let registered = false;

    try {
      // Check if stage already exists and can be resized (optimization)
      const existingEngine = engine.stage ? engine : null;
      
      if (existingEngine && existingEngine.stage && !existingEngine.stage.isDestroyed?.()) {
        // ISSUE #4 FIX: Update stage dimensions without destroy+recreate
        // This is much faster and avoids visual flashing
        try {
          existingEngine.stage.width(width);
          existingEngine.stage.height(height);
          existingEngine.stage.batchDraw();
          // Stage already registered, just update dimensions
          return () => {};
        } catch (updateError) {
          console.warn('Could not update stage dimensions, falling back to recreate:', updateError);
          // Fall through to recreate
        }
      }

      createdEngine = CanvasEngine.createStage(containerRef.current, {
        width,
        height,
        pageId,
      });

      const { stage, contentLayer, uiLayer, gridLayer, transformer } = createdEngine;

      // Always read the current store methods to avoid stale function refs.
      const { registerStage } = useTemplateStore.getState();
      registerStage(pageIndex, { stage, contentLayer, transformer, uiLayer, gridLayer });
      registered = true;

      setEngine({
        stage,
        layer: contentLayer,
        transformer,
        uiLayer,
        gridLayer,
      });
    } catch (error) {
      console.error('Error creating canvas engine:', error);
      setEngine({ stage: null, layer: null, transformer: null, uiLayer: null, gridLayer: null });
    }

    return () => {
      // unregisterStage handles stage.off() + stage.destroy(); do NOT call
      // CanvasEngine.destroyStage() again here or Konva will throw on a
      // double-destroy.
      try {
        if (registered) {
          const { unregisterStage } = useTemplateStore.getState();
          unregisterStage(pageIndex);
        } else if (createdEngine?.stage) {
          // Stage was created but never made it into the registry — destroy
          // directly so we don't leak a Konva instance.
          CanvasEngine.destroyStage(createdEngine.stage);
        }
      } catch (err) {
        console.error('Error unregistering stage:', err);
      }
      setEngine({ stage: null, layer: null, transformer: null, uiLayer: null, gridLayer: null });
    };
    // pageDimensions is the canonical trigger; containerRef is stable across renders.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [containerRef, pageIndex, pageDimensions?.id, pageDimensions?.width, pageDimensions?.height]);

  return engine;
}

export default useCanvasEngine;
