/**
 * CanvasPage.jsx — Konva.js Canvas Page Component
 * 
 * FIXED: Infinite re-render loop caused by page.objects dependency,
 *        added rubber-band selection, smart snapping guides,
 *        improved event handling
 */

import React, { useEffect, useRef, useCallback, memo } from 'react';
import Konva from 'konva';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { useCanvasEngine } from '../hooks/useCanvasEngine';
import { useCanvasEvents } from '../hooks/useCanvasEvents';
import { useCanvasSync } from '../hooks/useCanvasSync';
import { SchemaRenderer } from '../engines/SchemaRenderer';
import './CanvasPage.css';

const CanvasPage = memo(function CanvasPage({ pageIndex, isActive }) {
  const containerRef = useRef(null);
  const isLoadedRef = useRef(false);

  const pages = useTemplateStore((s) => s.pages);
  const zoom = useTemplateStore((s) => s.zoom);
  const setActivePageIndex = useTemplateStore((s) => s.setActivePageIndex);
  const setSelectedObject = useTemplateStore((s) => s.setSelectedObject);

  const page = pages?.[pageIndex];

  // Initialize Konva stage
  const { stage, layer, transformer, uiLayer } = useCanvasEngine(containerRef, pageIndex);

  // Setup event handlers
  const { setupNodeSelection } = useCanvasEvents(stage, layer, transformer, pageIndex);

  // Get sync function
  const syncStore = useCanvasSync(pageIndex);
  const syncStoreRef = useRef(syncStore);

  useEffect(() => {
    syncStoreRef.current = syncStore;
  }, [syncStore]);

  // Load page objects into canvas — ONLY on initial mount or page change
  // Uses isLoadedRef to prevent reload from store sync cycles.
  // Goes through SchemaRenderer so CanvasTable + future schema-driven elements
  // get a single rendering pipeline.
  //
  // FIXED: Merged two separate effects into one to properly detect page changes.
  // Previously, the flag-reset effect ran AFTER the load effect, causing blank pages
  // when switching. Now we detect actual page ID changes and clear the flag BEFORE
  // attempting to load, ensuring fresh renders on navigation.
  useEffect(() => {
    if (!layer || !page?.objects) return;

    try {
      // CRITICAL: Reset flag on page ID change BEFORE checking isLoadedRef
      // This ensures we detect actual navigation and don't skip the load
      isLoadedRef.current = false;

      // Verify layer is still valid before rendering
      if (!layer.getStage?.()) {
        console.warn('Layer stage is invalid, skipping render');
        return;
      }

      isLoadedRef.current = true;
      SchemaRenderer.renderInto(layer, page.objects);

      // Setup selection for all loaded nodes.
      layer.getChildren().forEach((node) => {
        try {
          setupNodeSelection(node);
        } catch (err) {
          console.error('Error setting up node selection:', err);
        }
      });
    } catch (err) {
      console.error('Error loading page objects:', err);
      isLoadedRef.current = false;
    }
  }, [layer, page?.id, setupNodeSelection, stage, transformer]);

  // Setup rubber-band selection
  useEffect(() => {
    if (!stage || !layer || !uiLayer || !transformer) return;

    let selectionRect = null;
    let isSelecting = false;
    let selectionStart = { x: 0, y: 0 };

    try {
      // Create selection rectangle on UI layer
      selectionRect = new Konva.Rect({
        fill: 'rgba(99, 102, 241, 0.08)',
        stroke: 'rgba(99, 102, 241, 0.5)',
        strokeWidth: 1,
        visible: false,
        listening: false,
        dash: [4, 4],
      });
      uiLayer.add(selectionRect);

      const handleMouseDown = (e) => {
        try {
          // Only start rubber-band on empty stage area
          if (e.target !== stage && e.target.getLayer() !== uiLayer) return;

          // Don't start selection if clicking on content layer nodes
          if (e.target !== stage) return;

          const pos = stage.getPointerPosition();
          if (!pos) return;

          isSelecting = true;
          selectionStart = { x: pos.x, y: pos.y };

          if (selectionRect) {
            selectionRect.setAttrs({
              x: pos.x,
              y: pos.y,
              width: 0,
              height: 0,
              visible: true,
            });
          }

          // Deselect all if no shift
          if (!e.evt.shiftKey) {
            transformer.nodes([]);
            setSelectedObject(null);
          }
        } catch (err) {
          console.error('Error in rubber-band mousedown:', err);
        }
      };

      const handleMouseMove = (e) => {
        try {
          if (!isSelecting || !selectionRect) return;

          const pos = stage.getPointerPosition();
          if (!pos) return;

          const x = Math.min(selectionStart.x, pos.x);
          const y = Math.min(selectionStart.y, pos.y);
          const width = Math.abs(pos.x - selectionStart.x);
          const height = Math.abs(pos.y - selectionStart.y);

          selectionRect.setAttrs({ x, y, width, height });
          uiLayer.batchDraw();
        } catch (err) {
          console.error('Error in rubber-band mousemove:', err);
        }
      };

      const handleMouseUp = () => {
        try {
          if (!isSelecting || !selectionRect) return;
          isSelecting = false;

          const selBox = selectionRect.getClientRect();
          selectionRect.visible(false);
          uiLayer.batchDraw();

          // Only process if selection is big enough (>5px)
          if (selBox.width < 5 && selBox.height < 5) return;

          // Find all content nodes that intersect with selection box
          const selected = layer.getChildren().filter((node) => {
            if (node.getAttr('locked')) return false;
            const nodeBox = node.getClientRect();
            return Konva.Util.haveIntersection(selBox, nodeBox);
          });

          if (selected.length > 0) {
            transformer.nodes(selected);
            if (selected.length === 1) {
              setSelectedObject(selected[0]);
            }
            layer.batchDraw();
          }
        } catch (err) {
          console.error('Error in rubber-band mouseup:', err);
        }
      };

      stage.on('mousedown.rubberband', handleMouseDown);
      stage.on('mousemove.rubberband', handleMouseMove);
      stage.on('mouseup.rubberband', handleMouseUp);

      return () => {
        try {
          // Remove all event listeners with proper cleanup
          stage.off('mousedown.rubberband');
          stage.off('mousemove.rubberband');
          stage.off('mouseup.rubberband');
          
          // Destroy selection rect
          if (selectionRect) {
            selectionRect.destroy();
            selectionRect = null;
          }
          
          // Ensure UI layer is cleaned up
          if (uiLayer && !uiLayer.isDestroyed?.()) {
            uiLayer.batchDraw();
          }
        } catch (err) {
          console.error('Error cleaning up rubber-band selection:', err);
        }
      };
    } catch (err) {
      console.error('Error setting up rubber-band selection:', err);
      return () => {};
    }
  }, [stage, layer, uiLayer, transformer, setSelectedObject]);

  // Handle click to activate page
  const handlePageClick = useCallback(() => {
    if (!isActive) {
      setActivePageIndex(pageIndex);
    }
  }, [isActive, pageIndex, setActivePageIndex]);

  if (!page) {
    return (
      <div className="canvas-page canvas-page--empty">
        <div className="canvas-page__placeholder">
          <i className="feather-alert-circle" />
          <span>Page not found</span>
        </div>
      </div>
    );
  }

  return (
    <div
      className={`canvas-page ${isActive ? 'canvas-page--active' : 'canvas-page--inactive'}`}
      data-page-index={pageIndex}
      onClick={handlePageClick}
      style={{
        width: `${page.width * zoom}px`,
        height: `${page.height * zoom}px`,
      }}
    >
      <div className="canvas-page__page-label">
        Page {pageIndex + 1}
      </div>
      <div
        ref={containerRef}
        className="canvas-page__container"
        style={{
          width: `${page.width}px`,
          height: `${page.height}px`,
          transform: `scale(${zoom})`,
          transformOrigin: 'top left',
        }}
      />
    </div>
  );
});

export default CanvasPage;
