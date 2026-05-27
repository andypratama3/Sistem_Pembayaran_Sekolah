/**
 * useCanvasEvents.js — Centralized event management with smart snapping
 * FIXED: Refs pattern prevents infinite re-renders,
 *        added snap guides, improved selection handling
 * SECURITY: Added null checks, proper cleanup, stale closure prevention
 */

import { useEffect, useCallback, useRef } from 'react';
import Konva from 'konva';
import { useTemplateStore } from '../store/useTemplateStore';
import { useCanvasSync } from './useCanvasSync';

const SNAP_THRESHOLD = 6;
const GUIDE_COLOR = '#6366f1';
const GUIDE_DASH = [4, 4];

/**
 * requestAnimationFrame-throttled callback.
 * Ensures the wrapped function fires at most once per animation frame.
 * ISSUE #3 FIX: Added proper cleanup and error handling
 */
function rafThrottle(fn) {
  if (typeof fn !== 'function') {
    throw new TypeError('rafThrottle expects a function');
  }
  
  let frame = null;
  let lastArgs = null;
  let isDestroyed = false;
  
  const throttled = (...args) => {
    if (isDestroyed) return;
    lastArgs = args;
    if (frame !== null) return;
    
    try {
      frame = requestAnimationFrame(() => {
        if (!isDestroyed && frame !== null) {
          frame = null;
          fn(...lastArgs);
        }
      });
    } catch (error) {
      console.error('rafThrottle error:', error);
      frame = null;
    }
  };
  
  // Add cleanup method to cancel pending frames
  throttled.cancel = () => {
    if (frame !== null) {
      cancelAnimationFrame(frame);
      frame = null;
    }
    isDestroyed = true;
    lastArgs = null;
  };
  
  return throttled;
}

/**
 * Hook for managing all Konva stage events
 * ISSUE #1 FIX: Added null checks for stage/layer/transformer
 * ISSUE #5 FIX: Proper dependency arrays and stale closure prevention
 */
export function useCanvasEvents(stage, layer, transformer, pageIndex) {
  // ISSUE #7 FIX: Move hooks ABOVE conditional check (Rules of Hooks)
  const storeRef = useRef(useTemplateStore.getState());
  const guideLinesRef = useRef([]);
  const nodeHandlersRef = useRef(new Map()); // ISSUE #1 FIX: Track handlers for cleanup

  // ISSUE #1 FIX: Proper store subscription with cleanup
  useEffect(() => {
    const unsubscribe = useTemplateStore.subscribe((state) => {
      storeRef.current = state;
    });
    return () => {
      unsubscribe();
    };
  }, []);

  const syncStore = useCanvasSync(pageIndex);
  const syncStoreRef = useRef(syncStore);

  // ISSUE #5 FIX: Proper dependency array
  useEffect(() => {
    syncStoreRef.current = syncStore;
  }, [syncStore]);

  // Validate inputs early, but return object (not early return before hooks)
  // ISSUE #7 FIX: Moved validation after hooks to comply with Rules of Hooks
  const hasValidInputs = !!(stage && layer && transformer);

  // Clear snap guides
  // ISSUE #2 FIX: Proper Konva listener cleanup
  const clearGuides = useCallback(() => {
    guideLinesRef.current.forEach((line) => {
      if (line && typeof line.destroy === 'function') {
        try {
          line.destroy();
        } catch (error) {
          console.error('Error destroying guide line:', error);
        }
      }
    });
    guideLinesRef.current = [];
  }, []);

  // Draw snap guide lines on UI layer
  // ISSUE #1 FIX: Added null checks for stage and uiLayer
  const drawGuide = useCallback((stageRef, points) => {
    if (!stageRef) return;
    if (!points || !Array.isArray(points) || points.length === 0) return;
    
    const uiLayer = stageRef.findOne('.ui');
    if (!uiLayer) return;

    try {
      const line = new Konva.Line({
        points,
        stroke: GUIDE_COLOR,
        strokeWidth: 1,
        dash: GUIDE_DASH,
        listening: false,
        name: 'snap-guide',
      });
      uiLayer.add(line);
      uiLayer.batchDraw();
      guideLinesRef.current.push(line);
    } catch (error) {
      console.error('Error drawing guide:', error);
    }
  }, []);

  // Get snap points from all sibling nodes
  // ISSUE #1 FIX: Added null checks for layer and stage
  const getSnapPoints = useCallback((layerRef, excludeNode) => {
    if (!layerRef) return { x: [], y: [] };

    try {
      const points = { x: [], y: [] };
      const stageRef = layerRef.getStage?.();
      const stageWidth = stageRef?.width?.() || 794;
      const stageHeight = stageRef?.height?.() || 1123;

      // Page edges
      points.x.push(0, stageWidth / 2, stageWidth);
      points.y.push(0, stageHeight / 2, stageHeight);

      // Sibling nodes
      const children = layerRef.getChildren?.();
      if (Array.isArray(children)) {
        children.forEach((node) => {
          if (!node || node === excludeNode || node.getAttr?.('locked')) return;
          const box = node.getClientRect?.({ relativeTo: layerRef });
          if (box) {
            points.x.push(box.x, box.x + box.width / 2, box.x + box.width);
            points.y.push(box.y, box.y + box.height / 2, box.y + box.height);
          }
        });
      }

      return points;
    } catch (error) {
      console.error('Error getting snap points:', error);
      return { x: [], y: [] };
    }
  }, []);

  // Calculate snapped position
  // ISSUE #1 FIX: Added null checks and error handling
  const snapPosition = useCallback((node, snapPoints) => {
    if (!node || !snapPoints) return null;
    if (!storeRef.current?.snappingEnabled) return null;

    try {
      const nodeLayer = node.getLayer?.();
      if (!nodeLayer) return null;

      const box = node.getClientRect?.({ relativeTo: nodeLayer });
      if (!box) return null;

      const nodePoints = {
        x: [box.x, box.x + box.width / 2, box.x + box.width],
        y: [box.y, box.y + box.height / 2, box.y + box.height],
      };

      let snapX = null;
      let snapY = null;

      // Check X snapping
      for (const nx of nodePoints.x) {
        for (const sx of snapPoints.x) {
          if (Math.abs(nx - sx) < SNAP_THRESHOLD) {
            snapX = { offset: sx - nx, guide: sx };
            break;
          }
        }
        if (snapX) break;
      }

      // Check Y snapping
      for (const ny of nodePoints.y) {
        for (const sy of snapPoints.y) {
          if (Math.abs(ny - sy) < SNAP_THRESHOLD) {
            snapY = { offset: sy - ny, guide: sy };
            break;
          }
        }
        if (snapY) break;
      }

      return { snapX, snapY };
    } catch (error) {
      console.error('Error calculating snap position:', error);
      return null;
    }
  }, []);

  // Zoom via mouse wheel (only when Ctrl/Meta held, otherwise allow normal scroll).
  // ISSUE #8 FIX: Added error handling and null checks
  const handleWheel = useCallback((e) => {
    try {
      if (!e?.evt) return;
      if (!e.evt.ctrlKey && !e.evt.metaKey) return;
      e.evt.preventDefault();

      const scaleBy = 1.05;
      const currentZoom = storeRef.current?.zoom || 1;
      const newScale = e.evt.deltaY < 0 ? currentZoom * scaleBy : currentZoom / scaleBy;

      const clampedZoom = Math.max(0.1, Math.min(5, newScale));
      storeRef.current?.setZoom?.(clampedZoom);
    } catch (error) {
      console.error('Error handling wheel event:', error);
    }
  }, []);

  // Click on empty stage → deselect
  // ISSUE #1 FIX: Added null checks for stage, layer, transformer
  const handleStageClick = useCallback((e) => {
    try {
      if (!stage || !layer || !transformer) return;
      if (e?.target === stage) {
        transformer.nodes?.([]);
        storeRef.current?.setSelectedObject?.(null);
        layer.batchDraw?.();
      }
    } catch (error) {
      console.error('Error handling stage click:', error);
    }
  }, [stage, layer, transformer]);

  // After drag/transform → sync to store
  // ISSUE #5 FIX: Proper dependency array to prevent stale closures
  const handleObjectChange = useCallback(() => {
    clearGuides();
    syncStoreRef.current?.(layer);
  }, [layer, clearGuides]);

  // Setup selection for a node.
  // Calling this on the same node twice (e.g. parent re-attaches after add/remove)
  // would otherwise stack duplicate listeners. We tag the node with a marker
  // attribute and skip if already wired.
  const setupNodeSelection = useCallback((node) => {
    if (!node || !transformer || !layer) return;
    if (node.getAttr('_eventsBound')) return;
    
    try {
      node.setAttr('_eventsBound', true);

      node.on('click.tplEditor tap.tplEditor', (e) => {
        try {
          e.cancelBubble = true;

          // Skip if locked
          if (node.getAttr('locked')) return;

          if (e.evt.shiftKey) {
            const nodes = transformer.nodes().concat([node]);
            transformer.nodes(nodes);
          } else {
            transformer.nodes([node]);
          }

           storeRef.current.setSelectedObject(node.id?.() || node.name?.() || null);
          layer.batchDraw();
        } catch (err) {
          console.error('Error in node click handler:', err);
        }
      });

      // Drag with snapping — throttled with requestAnimationFrame to avoid janky
      // recomputation of snap points on every pixel of drag.
      const dragMoveHandler = rafThrottle(() => {
        try {
          if (!storeRef.current.snappingEnabled) return;

          clearGuides();
          const snapPoints = getSnapPoints(layer, node);
          const result = snapPosition(node, snapPoints);

          if (result) {
            if (result.snapX) {
              node.x(node.x() + result.snapX.offset);
              const stageHeight = stage?.height() || 1123;
              drawGuide(stage, [result.snapX.guide, 0, result.snapX.guide, stageHeight], 'vertical');
            }
            if (result.snapY) {
              node.y(node.y() + result.snapY.offset);
              const stageWidth = stage?.width() || 794;
              drawGuide(stage, [0, result.snapY.guide, stageWidth, result.snapY.guide], 'horizontal');
            }
          }
        } catch (err) {
          console.error('Error in drag move handler:', err);
        }
      });
      node.setAttr('_dragMoveHandler', dragMoveHandler);
      node.on('dragmove.tplEditor', dragMoveHandler);

      node.on('dragend.tplEditor transformend.tplEditor', () => {
        try {
          dragMoveHandler.cancel();
          handleObjectChange();
        } catch (err) {
          console.error('Error in drag end handler:', err);
        }
      });
    } catch (err) {
      console.error('Error setting up node selection:', err);
      node.setAttr('_eventsBound', false);
    }
  }, [transformer, layer, stage, handleObjectChange, clearGuides, getSnapPoints, snapPosition, drawGuide]);

  useEffect(() => {
    if (!stage || !layer) return;

    try {
      stage.on('wheel.tplEditor', handleWheel);
      stage.on('click.tplEditor tap.tplEditor', handleStageClick);

      // Setup selection for existing nodes (idempotent — guards against double-bind).
      layer.getChildren().forEach(setupNodeSelection);

      return () => {
        try {
          stage.off('wheel.tplEditor');
          stage.off('click.tplEditor tap.tplEditor');
          // Clear node-level listeners so a re-mount doesn't accumulate.
          layer.getChildren().forEach((node) => {
            try {
              const dragMoveHandler = node.getAttr('_dragMoveHandler');
              if (dragMoveHandler && typeof dragMoveHandler.cancel === 'function') {
                dragMoveHandler.cancel();
              }
              node.off('click.tplEditor tap.tplEditor dragmove.tplEditor dragend.tplEditor transformend.tplEditor');
              node.setAttr('_eventsBound', false);
            } catch (err) {
              console.error('Error cleaning up node listeners:', err);
            }
          });
          clearGuides();
        } catch (err) {
          console.error('Error in event cleanup:', err);
        }
      };
    } catch (err) {
      console.error('Error setting up canvas events:', err);
      return () => {};
    }
   }, [stage, layer, handleWheel, handleStageClick, setupNodeSelection, clearGuides]);

  // ISSUE #7 FIX: Return no-op if inputs are invalid (after all hooks have been called)
  if (!hasValidInputs) {
    return { setupNodeSelection: () => {} };
  }

  return { setupNodeSelection };
}

export default useCanvasEvents;
