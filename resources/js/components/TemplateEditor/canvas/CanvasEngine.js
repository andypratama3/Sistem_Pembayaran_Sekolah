/**
 * CanvasEngine.js — Core Konva.js wrapper
 *
 * Provides factory methods for creating standardized Konva stages
 * with proper layer hierarchy and event setup.
 */

import Konva from 'konva';

const ZOOM_MIN = 0.1;
const ZOOM_MAX = 5;

/**
 * Factory for creating Konva Stage with standardized configuration
 */
export class CanvasEngine {
  /**
   * Create a new Konva Stage with proper layer hierarchy
   * @param {HTMLDivElement} container - DOM element container
   * @param {object} options
   * @param {number} options.width - Canvas width in pixels
   * @param {number} options.height - Canvas height in pixels
   * @param {string} options.pageId - Page ID for registry
   * @returns {object} { stage, contentLayer, gridLayer, uiLayer, transformer }
   */
  static createStage(container, { width, height, pageId }) {
    if (!container) {
      throw new Error('Container element is required');
    }
    if (!Number.isFinite(width) || !Number.isFinite(height) || width <= 0 || height <= 0) {
      throw new Error(`Invalid stage dimensions: ${width} x ${height}`);
    }

    const stage = new Konva.Stage({
      container,
      width,
      height,
      draggable: false,
    });

    // Layer for grid (not serialized, listening disabled)
    const gridLayer = new Konva.Layer({
      name: 'grid',
      listening: false,
      id: `layer-grid-${pageId}`,
    });

    // Main content layer (all user objects)
    const contentLayer = new Konva.Layer({
      name: 'content',
      id: `layer-content-${pageId}`,
    });

    // UI layer for transformer and selection handles (not serialized)
    const uiLayer = new Konva.Layer({
      name: 'ui',
      listening: true,
      id: `layer-ui-${pageId}`,
    });

    // Add layers to stage in order
    stage.add(gridLayer);
    stage.add(contentLayer);
    stage.add(uiLayer);

    // Create transformer for selection and resize
    const transformer = new Konva.Transformer({
      rotateAnchorOffset: 30,
      enabledAnchors: [
        'top-left',
        'top-center',
        'top-right',
        'middle-right',
        'middle-left',
        'bottom-left',
        'bottom-center',
        'bottom-right',
      ],
      // Prevent attached-then-destroyed nodes from leaving the transformer in
      // a broken state — Konva will silently skip null entries.
      shouldOverdrawWholeArea: true,
      boundBoxFunc: (oldBox, newBox) => {
        if (!newBox) return oldBox;
        // Enforce minimum size
        if (newBox.width < 5 || newBox.height < 5) return oldBox;
        return newBox;
      },
    });

    uiLayer.add(transformer);

    return {
      stage,
      contentLayer,
      gridLayer,
      uiLayer,
      transformer,
    };
  }

  /**
   * Destroy stage with full cleanup. Safe to call more than once.
   */
  static destroyStage(stage) {
    if (!stage) return;
    if (typeof stage.isDestroyed === 'function' && stage.isDestroyed()) return;

    try {
      // Remove drag tracking globally so we don't leave dangling references
      // to a stage that's about to be torn down.
      try { stage.off(); } catch { /* noop */ }
      stage.destroy();
    } catch (error) {
      console.error('Error destroying stage:', error);
    }
  }

  /**
   * Set zoom with pivot point. Pivot is in screen (container) coordinates.
   */
  static setZoom(stage, newZoom, pivotPoint) {
    if (!stage) return newZoom;
    if (typeof stage.isDestroyed === 'function' && stage.isDestroyed()) return newZoom;

    const oldScale = stage.scaleX() || 1;
    const pointer = pivotPoint || {
      x: stage.width() / 2,
      y: stage.height() / 2,
    };

    const mousePointTo = {
      x: (pointer.x - stage.x()) / oldScale,
      y: (pointer.y - stage.y()) / oldScale,
    };

    const clampedZoom = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, Number.isFinite(newZoom) ? newZoom : 1));

    stage.scale({ x: clampedZoom, y: clampedZoom });
    stage.position({
      x: pointer.x - mousePointTo.x * clampedZoom,
      y: pointer.y - mousePointTo.y * clampedZoom,
    });

    stage.batchDraw();

    return clampedZoom;
  }

  /**
   * Pan stage by offset
   */
  static pan(stage, dx, dy) {
    if (!stage) return;
    if (typeof stage.isDestroyed === 'function' && stage.isDestroyed()) return;

    const pos = stage.position();
    stage.position({
      x: pos.x + dx,
      y: pos.y + dy,
    });

    stage.batchDraw();
  }

  /**
   * Reset stage to default zoom and position
   */
  static resetView(stage) {
    if (!stage) return;
    if (typeof stage.isDestroyed === 'function' && stage.isDestroyed()) return;

    stage.scale({ x: 1, y: 1 });
    stage.position({ x: 0, y: 0 });
    stage.batchDraw();
  }

  /**
   * Fit stage to content. Computes bounds in *layer-local* coordinates
   * (relativeTo the stage's content layer) so repeated calls don't compound
   * the previous zoom/pan transform.
   */
  static fitToContent(stage, contentLayer, padding = 20) {
    if (!stage || !contentLayer) return;
    if (typeof stage.isDestroyed === 'function' && stage.isDestroyed()) return;

    const children = contentLayer.getChildren();
    if (children.length === 0) {
      this.resetView(stage);
      return;
    }

    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

    children.forEach((node) => {
      // relativeTo: contentLayer makes the box independent of any current
      // stage transform — so fitToContent is idempotent.
      const box = node.getClientRect({ relativeTo: contentLayer });
      if (!box || !Number.isFinite(box.width) || !Number.isFinite(box.height)) return;
      minX = Math.min(minX, box.x);
      minY = Math.min(minY, box.y);
      maxX = Math.max(maxX, box.x + box.width);
      maxY = Math.max(maxY, box.y + box.height);
    });

    if (!Number.isFinite(minX) || !Number.isFinite(minY)) {
      this.resetView(stage);
      return;
    }

    const contentWidth = maxX - minX;
    const contentHeight = maxY - minY;
    if (contentWidth <= 0 || contentHeight <= 0) {
      this.resetView(stage);
      return;
    }

    const scaleX = (stage.width() - padding * 2) / contentWidth;
    const scaleY = (stage.height() - padding * 2) / contentHeight;
    const scale = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, Math.min(scaleX, scaleY, 1)));

    stage.scale({ x: scale, y: scale });
    stage.position({
      x: -minX * scale + padding,
      y: -minY * scale + padding,
    });

    stage.batchDraw();
  }
}

export default CanvasEngine;
