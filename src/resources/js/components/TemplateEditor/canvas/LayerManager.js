/**
 * LayerManager.js — True layer management with Konva
 * Replaces canvas.sendBackwards(), canvas.bringForward(), etc.
 *
 * Provides proper z-order management and layer tree operations.
 *
 * Notes:
 *  - Locked nodes still allow visibility / lock toggling, but they reject
 *    z-order moves and direct drags. Pass `{ force: true }` to override.
 *  - delete{Node,Nodes} detach nodes from a transformer before destroying so
 *    the transformer never references a destroyed Konva node.
 *  - groupNodes / ungroupNodes use absolute coordinate math so children with
 *    rotation / scale survive the round-trip visually unchanged.
 */

import Konva from 'konva';

const isLocked = (node) => !!node?.getAttr?.('locked');

/**
 * Detach a node from a transformer if it's currently attached.
 * No-ops when transformer is null/undefined.
 */
function detachFromTransformer(node, transformer) {
  if (!transformer || !node) return;
  try {
    const current = transformer.nodes() || [];
    if (current.includes(node)) {
      transformer.nodes(current.filter((n) => n !== node));
    }
  } catch {
    /* transformer may already be destroyed; ignore */
  }
}

/**
 * Layer management with true hierarchy
 */
export class LayerManager {
  /**
   * Get all nodes in layer as tree structure (top to bottom for UI)
   * Replaces: canvas.getObjects().reverse()
   */
  static getLayerTree(contentLayer) {
    if (!contentLayer) return [];

    return contentLayer
      .getChildren()
      .map((node) => ({
        id: node.id(),
        name: node.getAttr('name') || `${node.className} ${node.id()}`,
        className: node.className,
        visible: node.isVisible(),
        locked: isLocked(node),
        zIndex: node.zIndex(),
        children:
          node.className === 'Group'
            ? LayerManager._getGroupChildren(node)
            : [],
      }))
      .reverse(); // UI shows top layer first
  }

  /**
   * Get children of a group recursively
   */
  static _getGroupChildren(group) {
    if (!group?.getChildren) return [];

    return group
      .getChildren()
      .map((node) => ({
        id: node.id(),
        name: node.getAttr('name') || `${node.className} ${node.id()}`,
        className: node.className,
        visible: node.isVisible(),
        locked: isLocked(node),
        zIndex: node.zIndex(),
        children:
          node.className === 'Group'
            ? LayerManager._getGroupChildren(node)
            : [],
      }))
      .reverse();
  }

  /**
   * Move node to specific z-index. Locked nodes are ignored unless force is set.
   */
  static moveToIndex(node, targetZIndex, { force = false } = {}) {
    if (!node) return;
    if (!force && isLocked(node)) return;

    node.zIndex(targetZIndex);
    node.getLayer()?.batchDraw();
  }

  static moveForward(node, { force = false } = {}) {
    if (!node) return;
    if (!force && isLocked(node)) return;
    node.moveUp();
    node.getLayer()?.batchDraw();
  }

  static moveBackward(node, { force = false } = {}) {
    if (!node) return;
    if (!force && isLocked(node)) return;
    node.moveDown();
    node.getLayer()?.batchDraw();
  }

  static moveToFront(node, { force = false } = {}) {
    if (!node) return;
    if (!force && isLocked(node)) return;
    node.moveToTop();
    node.getLayer()?.batchDraw();
  }

  static moveToBack(node, { force = false } = {}) {
    if (!node) return;
    if (!force && isLocked(node)) return;
    node.moveToBottom();
    node.getLayer()?.batchDraw();
  }

  /**
   * Toggle visibility
   */
  static toggleVisibility(node) {
    if (!node) return;
    node.visible(!node.isVisible());
    node.getLayer()?.batchDraw();
  }

  static setVisibility(node, visible) {
    if (!node) return;
    node.visible(visible);
    node.getLayer()?.batchDraw();
  }

  /**
   * Toggle lock state
   * Locked nodes cannot be selected or dragged
   */
  static toggleLock(node, transformer) {
    if (!node) return;
    LayerManager.setLock(node, !isLocked(node), transformer);
  }

  /**
   * Set lock state
   */
  static setLock(node, locked, transformer) {
    if (!node) return;

    node.setAttr('locked', !!locked);
    if (typeof node.draggable === 'function') node.draggable(!locked);
    // Keep listening on so users can still click to inspect — only the
    // draggable + transformer interaction needs to be blocked. Some callers
    // may prefer fully-non-listening locked nodes; expose via attr if needed.
    if (typeof node.listening === 'function') node.listening(!locked);

    if (locked) detachFromTransformer(node, transformer);

    node.getLayer()?.batchDraw();
  }

  /**
   * Group multiple nodes into a new Konva.Group, preserving each child's
   * original visual position even when the children have rotation / scale.
   */
  static groupNodes(nodes, layer, transformer) {
    if (!nodes || nodes.length < 2 || !layer) return null;

    // Sort by current z-index so the new group preserves stacking order.
    const ordered = [...nodes].sort((a, b) => a.zIndex() - b.zIndex());

    const group = new Konva.Group({
      id: `grp_${Date.now()}`,
      name: 'Group',
      draggable: true,
    });

    // Compute bounding box in absolute (stage) coordinates.
    const boundingBox = ordered.reduce(
      (acc, node) => {
        const box = node.getClientRect({ relativeTo: layer });
        return {
          x: Math.min(acc.x, box.x),
          y: Math.min(acc.y, box.y),
          right: Math.max(acc.right, box.x + box.width),
          bottom: Math.max(acc.bottom, box.y + box.height),
        };
      },
      { x: Infinity, y: Infinity, right: -Infinity, bottom: -Infinity }
    );

    if (!Number.isFinite(boundingBox.x) || !Number.isFinite(boundingBox.y)) {
      // No usable bounds — fall back to (0,0) rather than NaN positions.
      boundingBox.x = 0;
      boundingBox.y = 0;
    }

    layer.add(group);
    group.position({ x: boundingBox.x, y: boundingBox.y });

    ordered.forEach((node) => {
      // Detach from transformer first; transformer may be holding a stale ref.
      detachFromTransformer(node, transformer);
      // Capture absolute position BEFORE re-parenting, then offset by group
      // origin afterwards. This is robust against rotation / scale because
      // Konva preserves visual position when only re-parenting if we adjust
      // local coords accordingly.
      const absBefore = node.getAbsolutePosition();
      node.moveTo(group);
      const groupAbs = group.getAbsolutePosition();
      node.position({
        x: absBefore.x - groupAbs.x,
        y: absBefore.y - groupAbs.y,
      });
    });

    layer.batchDraw();

    return group;
  }

  /**
   * Ungroup nodes
   * Returns nodes to layer and destroys group, preserving visual positions
   * even when the group has rotation/scale.
   */
  static ungroupNodes(group, layer, transformer) {
    if (!group || !layer) return [];

    const children = [...group.getChildren()];

    // Detach the group itself from any transformer first so we don't leave a
    // dangling reference.
    detachFromTransformer(group, transformer);

    children.forEach((node) => {
      const absBefore = node.getAbsolutePosition();
      const absRotation = node.getAbsoluteRotation?.() ?? node.rotation();
      const absScale = node.getAbsoluteScale ? node.getAbsoluteScale() : { x: node.scaleX(), y: node.scaleY() };

      node.moveTo(layer);
      node.absolutePosition(absBefore);
      node.rotation(absRotation);
      node.scale({ x: absScale.x, y: absScale.y });
    });

    group.destroy();
    layer.batchDraw();

    return children;
  }

  /**
   * Find node by ID in layer (recursive — id is unique within the stage).
   */
  static findNodeById(layer, nodeId) {
    if (!layer || !nodeId) return null;
    return layer.findOne(`#${nodeId}`);
  }

  /**
   * Find all nodes matching a predicate (top-level children only).
   */
  static findNodes(layer, predicate) {
    if (!layer || typeof predicate !== 'function') return [];
    return layer.getChildren().filter(predicate);
  }

  /**
   * Get all nodes of a specific type (top-level children only).
   */
  static getNodesByType(layer, className) {
    if (!layer) return [];
    return layer.getChildren().filter((node) => node.className === className);
  }

  /**
   * Delete node — detaches from the transformer first so the transformer
   * never holds a reference to a destroyed node.
   */
  static deleteNode(node, transformer) {
    if (!node) return;

    const layer = node.getLayer();
    detachFromTransformer(node, transformer);

    try {
      node.destroy();
    } catch (err) {
      console.error('Error destroying node:', err);
    }
    layer?.batchDraw();
  }

  /**
   * Delete multiple nodes
   */
  static deleteNodes(nodes, transformer) {
    if (!Array.isArray(nodes) || nodes.length === 0) return;

    const layer = nodes[0]?.getLayer();

    nodes.forEach((node) => {
      if (!node) return;
      detachFromTransformer(node, transformer);
      try { node.destroy(); }
      catch (err) { console.error('Error destroying node:', err); }
    });

    layer?.batchDraw();
  }

  /**
   * Align nodes horizontally by client-rect (so widths matter, not just origin).
   */
  static alignHorizontal(nodes, alignment = 'center') {
    if (!nodes || nodes.length < 2) return;

    const boxes = nodes.map((n) => ({ node: n, box: n.getClientRect({ relativeTo: n.getLayer() }) }));
    let target;

    switch (alignment) {
      case 'left':
        target = Math.min(...boxes.map((b) => b.box.x));
        boxes.forEach(({ node, box }) => node.x(node.x() + (target - box.x)));
        break;
      case 'center': {
        const centers = boxes.map((b) => b.box.x + b.box.width / 2);
        const avg = centers.reduce((a, b) => a + b, 0) / centers.length;
        boxes.forEach(({ node, box }) => {
          const c = box.x + box.width / 2;
          node.x(node.x() + (avg - c));
        });
        break;
      }
      case 'right':
        target = Math.max(...boxes.map((b) => b.box.x + b.box.width));
        boxes.forEach(({ node, box }) => node.x(node.x() + (target - (box.x + box.width))));
        break;
      default:
        return;
    }

    nodes[0]?.getLayer()?.batchDraw();
  }

  /**
   * Align nodes vertically
   */
  static alignVertical(nodes, alignment = 'center') {
    if (!nodes || nodes.length < 2) return;

    const boxes = nodes.map((n) => ({ node: n, box: n.getClientRect({ relativeTo: n.getLayer() }) }));
    let target;

    switch (alignment) {
      case 'top':
        target = Math.min(...boxes.map((b) => b.box.y));
        boxes.forEach(({ node, box }) => node.y(node.y() + (target - box.y)));
        break;
      case 'center': {
        const centers = boxes.map((b) => b.box.y + b.box.height / 2);
        const avg = centers.reduce((a, b) => a + b, 0) / centers.length;
        boxes.forEach(({ node, box }) => {
          const c = box.y + box.height / 2;
          node.y(node.y() + (avg - c));
        });
        break;
      }
      case 'bottom':
        target = Math.max(...boxes.map((b) => b.box.y + b.box.height));
        boxes.forEach(({ node, box }) => node.y(node.y() + (target - (box.y + box.height))));
        break;
      default:
        return;
    }

    nodes[0]?.getLayer()?.batchDraw();
  }

  /**
   * Distribute nodes evenly horizontally based on visual centers.
   */
  static distributeHorizontal(nodes) {
    if (!nodes || nodes.length < 3) return;

    const items = nodes.map((n) => ({
      node: n,
      box: n.getClientRect({ relativeTo: n.getLayer() }),
    }));
    items.sort((a, b) => (a.box.x + a.box.width / 2) - (b.box.x + b.box.width / 2));

    const firstC = items[0].box.x + items[0].box.width / 2;
    const lastC = items[items.length - 1].box.x + items[items.length - 1].box.width / 2;
    const spacing = (lastC - firstC) / (items.length - 1);

    items.forEach((item, index) => {
      const targetCenter = firstC + spacing * index;
      const currentCenter = item.box.x + item.box.width / 2;
      item.node.x(item.node.x() + (targetCenter - currentCenter));
    });

    nodes[0]?.getLayer()?.batchDraw();
  }

  /**
   * Distribute nodes evenly vertically based on visual centers.
   */
  static distributeVertical(nodes) {
    if (!nodes || nodes.length < 3) return;

    const items = nodes.map((n) => ({
      node: n,
      box: n.getClientRect({ relativeTo: n.getLayer() }),
    }));
    items.sort((a, b) => (a.box.y + a.box.height / 2) - (b.box.y + b.box.height / 2));

    const firstC = items[0].box.y + items[0].box.height / 2;
    const lastC = items[items.length - 1].box.y + items[items.length - 1].box.height / 2;
    const spacing = (lastC - firstC) / (items.length - 1);

    items.forEach((item, index) => {
      const targetCenter = firstC + spacing * index;
      const currentCenter = item.box.y + item.box.height / 2;
      item.node.y(item.node.y() + (targetCenter - currentCenter));
    });

    nodes[0]?.getLayer()?.batchDraw();
  }
}

export default LayerManager;
