/**
 * canvasUtils.js — Utility functions for Konva.js canvas
 * 
 * Provides safe canvas access and utility functions
 */

import { stageRegistry } from '../store/useTemplateStore';
import { CanvasSerializer } from '../canvas/CanvasSerializer';

/**
 * Get content layer safely
 * Replaces: getCanvasSafe(canvases, activePageIndex, 'Op')
 */
export function getLayerSafe(pageIndex, operationName = 'Operation') {
  const stageData = stageRegistry.get(pageIndex);

  if (!stageData?.contentLayer) {
    console.warn(
      `[${operationName}] Content layer not available for page ${pageIndex}`
    );
    return null;
  }

  return stageData.contentLayer;
}

/**
 * Get currently selected node
 * Replaces: getActiveObjectSafe(canvas)
 */
export function getSelectedNode(pageIndex) {
  const stageData = stageRegistry.get(pageIndex);
  const nodes = stageData?.transformer?.nodes() || [];
  return nodes[0] || null;
}

/**
 * Get all selected nodes
 */
export function getSelectedNodes(pageIndex) {
  const stageData = stageRegistry.get(pageIndex);
  return stageData?.transformer?.nodes() || [];
}

/**
 * Get stage data
 */
export function getStageSafe(pageIndex) {
  return stageRegistry.get(pageIndex) || null;
}

/**
 * Serialize layer to JSON
 * Replaces: serializeCanvasObjects(canvas)
 */
export function serializeLayer(pageIndex) {
  const layer = getLayerSafe(pageIndex, 'serialize');
  if (!layer) return [];
  return CanvasSerializer.serializeLayer(layer);
}

/**
 * Batch draw layer
 * Replaces: renderCanvasSafe(canvas)
 */
export function batchDrawSafe(pageIndex) {
  const layer = getLayerSafe(pageIndex, 'batchDraw');
  layer?.batchDraw();
}

/**
 * Validate layer is available
 * Replaces: validateCanvas(canvas)
 */
export function validateLayer(pageIndex) {
  return !!stageRegistry.get(pageIndex)?.contentLayer;
}

/**
 * Debounce function for canvas updates
 */
export function debounceCanvasUpdate(fn, delay = 150) {
  if (typeof fn !== 'function') {
    throw new Error('Debounce requires a function');
  }
  if (delay < 0 || !Number.isFinite(delay)) {
    throw new Error('Delay must be a positive number');
  }
  
  let timeoutId = null;
  let isDestroyed = false;
  
  const debounced = (...args) => {
    if (isDestroyed) return;
    if (timeoutId !== null) {
      clearTimeout(timeoutId);
    }
    timeoutId = setTimeout(() => {
      if (!isDestroyed) {
        fn(...args);
      }
      timeoutId = null;
    }, delay);
  };
  
  // Cleanup function for component unmount
  debounced.cleanup = () => {
    isDestroyed = true;
    if (timeoutId !== null) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
  };
  
  return debounced;
}

/**
 * Clone node with offset
 */
export function cloneNode(pageIndex, nodeId, offsetX = 20, offsetY = 20) {
  if (!Number.isFinite(offsetX) || !Number.isFinite(offsetY)) {
    console.error('Clone offsets must be valid numbers');
    return null;
  }
  
  const layer = getLayerSafe(pageIndex, 'clone');
  if (!layer) return null;

  const node = layer.findOne(`#${nodeId}`);
  if (!node) {
    console.warn(`Node with ID ${nodeId} not found for cloning`);
    return null;
  }

  try {
    const cloned = CanvasSerializer.cloneNode(node, offsetX, offsetY);
    if (cloned) {
      layer.add(cloned);
      layer.batchDraw();
    }
    return cloned;
  } catch (error) {
    console.error('Error cloning node:', error);
    return null;
  }
}

/**
 * Delete node
 */
export function deleteNode(pageIndex, nodeId) {
  const layer = getLayerSafe(pageIndex, 'delete');
  if (!layer) return false;

  const node = layer.findOne(`#${nodeId}`);
  if (!node) return false;

  node.destroy();
  layer.batchDraw();
  return true;
}

/**
 * Delete multiple nodes
 */
export function deleteNodes(pageIndex, nodeIds) {
  if (!Array.isArray(nodeIds) || nodeIds.length === 0) {
    console.warn('deleteNodes requires a non-empty array of node IDs');
    return false;
  }
  
  const layer = getLayerSafe(pageIndex, 'deleteMultiple');
  if (!layer) return false;

  try {
    const nodes = nodeIds
      .map((id) => {
        if (typeof id !== 'string') {
          console.warn(`Invalid node ID type: ${typeof id}`);
          return null;
        }
        return layer.findOne(`#${id}`);
      })
      .filter((n) => n !== null);

    if (nodes.length === 0) {
      console.warn('No valid nodes found to delete');
      return false;
    }

    nodes.forEach((node) => {
      try {
        node.destroy();
      } catch (error) {
        console.error('Error destroying node:', error);
      }
    });
    
    layer.batchDraw();
    return true;
  } catch (error) {
    console.error('Error in deleteNodes:', error);
    return false;
  }
}

/**
 * Get node by ID
 */
export function getNodeById(pageIndex, nodeId) {
  const layer = getLayerSafe(pageIndex, 'getNode');
  if (!layer) return null;

  return layer.findOne(`#${nodeId}`);
}

/**
 * Get all nodes
 */
export function getAllNodes(pageIndex) {
  const layer = getLayerSafe(pageIndex, 'getAllNodes');
  if (!layer) return [];

  return layer.getChildren();
}

/**
 * Get nodes by type
 */
export function getNodesByType(pageIndex, className) {
  const layer = getLayerSafe(pageIndex, 'getNodesByType');
  if (!layer) return [];

  return layer.getChildren().filter((node) => node.className === className);
}

/**
 * Set node attribute
 */
export function setNodeAttr(pageIndex, nodeId, key, value) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.setAttr(key, value);
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Set multiple node attributes
 */
export function setNodeAttrs(pageIndex, nodeId, attrs) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.setAttrs(attrs);
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Get node attribute
 */
export function getNodeAttr(pageIndex, nodeId, key) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return null;

  return node.getAttr(key);
}

/**
 * Get node position
 */
export function getNodePosition(pageIndex, nodeId) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return null;

  return { x: node.x(), y: node.y() };
}

/**
 * Set node position
 */
export function setNodePosition(pageIndex, nodeId, x, y) {
  if (!Number.isFinite(x) || !Number.isFinite(y)) {
    console.warn('setNodePosition: x and y must be finite numbers');
    return false;
  }
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.position({ x, y });
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Get node size
 */
export function getNodeSize(pageIndex, nodeId) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return null;

  return { width: node.width(), height: node.height() };
}

/**
 * Set node size
 */
export function setNodeSize(pageIndex, nodeId, width, height) {
  if (!Number.isFinite(width) || !Number.isFinite(height) || width < 0 || height < 0) {
    console.warn('setNodeSize: width and height must be non-negative finite numbers');
    return false;
  }
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.width(width);
  node.height(height);
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Get node rotation
 */
export function getNodeRotation(pageIndex, nodeId) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return 0;

  return node.rotation();
}

/**
 * Set node rotation
 */
export function setNodeRotation(pageIndex, nodeId, rotation) {
  if (!Number.isFinite(rotation)) {
    console.warn('setNodeRotation: rotation must be a finite number');
    return false;
  }
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.rotation(rotation);
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Get node opacity
 */
export function getNodeOpacity(pageIndex, nodeId) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return 1;

  return node.opacity();
}

/**
 * Set node opacity
 */
export function setNodeOpacity(pageIndex, nodeId, opacity) {
  if (!Number.isFinite(opacity)) {
    console.warn('setNodeOpacity: opacity must be a finite number');
    return false;
  }
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.opacity(Math.max(0, Math.min(1, opacity)));
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Get node fill color
 */
export function getNodeFill(pageIndex, nodeId) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return null;

  return node.fill();
}

/**
 * Set node fill color
 */
export function setNodeFill(pageIndex, nodeId, fill) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.fill(fill);
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Get node stroke color
 */
export function getNodeStroke(pageIndex, nodeId) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return null;

  return node.stroke();
}

/**
 * Set node stroke color
 */
export function setNodeStroke(pageIndex, nodeId, stroke) {
  const node = getNodeById(pageIndex, nodeId);
  if (!node) return false;

  node.stroke(stroke);
  node.getLayer()?.batchDraw();
  return true;
}

/**
 * Export layer as image
 */
export async function exportLayerAsImage(pageIndex, options = {}) {
  const stageData = getStageSafe(pageIndex);
  if (!stageData?.stage) return null;

  return CanvasSerializer.toDataURL(stageData.stage, options);
}

/**
 * Toast helpers (re-export from services)
 */
export { showError, showSuccess, showWarning } from '../services/toast';

export default {
  getLayerSafe,
  getSelectedNode,
  getSelectedNodes,
  getStageSafe,
  serializeLayer,
  batchDrawSafe,
  validateLayer,
  debounceCanvasUpdate,
  cloneNode,
  deleteNode,
  deleteNodes,
  getNodeById,
  getAllNodes,
  getNodesByType,
  setNodeAttr,
  setNodeAttrs,
  getNodeAttr,
  getNodePosition,
  setNodePosition,
  getNodeSize,
  setNodeSize,
  getNodeRotation,
  setNodeRotation,
  getNodeOpacity,
  setNodeOpacity,
  getNodeFill,
  setNodeFill,
  getNodeStroke,
  setNodeStroke,
  exportLayerAsImage,
};
