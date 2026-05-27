/**
 * CanvasSerializer.js — Serialization for Konva nodes
 *
 * Handles both regular Konva nodes and special types like CanvasTable.
 *
 * Round-trip safety: a node serialized via `serializeLayer` and then loaded
 * back via `loadToLayer` should reproduce the original geometry, draggable
 * state, lock state, and visibility.
 */

import Konva from 'konva';
import { CanvasTable } from './CanvasTable';

const QR_PLACEHOLDER_SVG =
  'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">' +
  '<rect width="100" height="100" fill="white" stroke="%23cbd5e1" stroke-width="2"/>' +
  '<rect x="10" y="10" width="30" height="30" fill="%231e293b"/>' +
  '<rect x="15" y="15" width="20" height="20" fill="white"/>' +
  '<rect x="18" y="18" width="14" height="14" fill="%231e293b"/>' +
  '<rect x="60" y="10" width="30" height="30" fill="%231e293b"/>' +
  '<rect x="65" y="15" width="20" height="20" fill="white"/>' +
  '<rect x="68" y="18" width="14" height="14" fill="%231e293b"/>' +
  '<rect x="10" y="60" width="30" height="30" fill="%231e293b"/>' +
  '<rect x="15" y="65" width="20" height="20" fill="white"/>' +
  '<rect x="18" y="68" width="14" height="14" fill="%231e293b"/>' +
  '<rect x="45" y="45" width="10" height="10" fill="%231e293b"/>' +
  '<rect x="60" y="60" width="10" height="10" fill="%231e293b"/>' +
  '<rect x="70" y="70" width="20" height="20" fill="%231e293b"/>' +
  '<rect x="70" y="60" width="10" height="10" fill="%231e293b"/>' +
  '<rect x="80" y="50" width="10" height="10" fill="%231e293b"/>' +
  '<rect x="50" y="70" width="10" height="20" fill="%231e293b"/>' +
  '<rect x="60" y="80" width="10" height="10" fill="%231e293b"/>' +
  '<rect x="45" y="25" width="10" height="10" fill="%231e293b"/>' +
  '<rect x="45" y="10" width="10" height="10" fill="%231e293b"/></svg>';

// Names of internal helper nodes (resize handles, selection rings, etc.) we
// must not persist when serializing groups/layers.
const NON_SERIALIZABLE_NAMES = new Set(['col-resize-handle', 'row-resize-handle']);

function isSerializableNode(node) {
  if (!node) return false;
  const name = typeof node.name === 'function' ? node.name() : node.name;
  if (name && NON_SERIALIZABLE_NAMES.has(name)) return false;
  return true;
}

/**
 * Serialize content layer to JSON array
 * Replaces: canvas.getObjects().map(o => o.toJSON(keys))
 */
export class CanvasSerializer {
  static serializeLayer(contentLayer) {
    if (!contentLayer) return [];

    const out = [];
    contentLayer.getChildren().forEach((node) => {
      if (!isSerializableNode(node)) return;
      // CanvasTable has special serialization
      if (node.className === 'CanvasTable' || (typeof node.toTableJSON === 'function')) {
        out.push(node.toTableJSON());
        return;
      }
      // Regular Konva nodes
      out.push(node.toObject());
    });
    return out;
  }

  /**
   * Load from JSON array to content layer
   * Replaces: canvas.loadFromJSON(data)
   */
  static loadToLayer(contentLayer, objects) {
    if (!contentLayer) return;

    // Clear existing content
    contentLayer.destroyChildren();

    if (!Array.isArray(objects)) {
      console.warn('Objects must be an array');
      return;
    }

    objects.forEach((objData) => {
      if (!objData || typeof objData !== 'object') return;

      let node;

      try {
        if (objData.className === 'CanvasTable') {
          node = CanvasTable.fromJSON(objData);
        } else {
          // Use Konva's built-in deserialization
          node = Konva.Node.create(objData);
          if (!node) return;

          // Preserve the original draggable state. Default to draggable when
          // the field was never persisted, but honour explicit `false` from
          // locked-state nodes so they don't suddenly become movable.
          const savedDraggable = objData.attrs?.draggable;
          const isLocked = !!objData.attrs?.locked;
          if (typeof node.draggable === 'function') {
            if (isLocked) {
              node.draggable(false);
              node.listening(false);
            } else if (savedDraggable === undefined) {
              node.draggable(true);
            } else {
              node.draggable(!!savedDraggable);
            }
          }

          // QR code special handling — attach SVG fill pattern.
          const isQr = node.getAttr('isQrCode') ||
            (typeof node.id === 'function' && node.id() && node.id().startsWith('qrcode_'));
          if (isQr) {
            CanvasSerializer._attachQrPattern(node);
          }
        }

        if (node) contentLayer.add(node);
      } catch (error) {
        console.error('Error deserializing object:', objData, error);
      }
    });

    contentLayer.batchDraw();
  }

  /**
   * Attach the placeholder QR fill pattern to a node, handling the case where
   * the data-URL image loads synchronously (already-decoded) vs async.
   */
  static _attachQrPattern(node) {
    const qrImage = new Image();
    const apply = () => {
      if (node._isDestroyed || node.isDestroyed?.() ) return;
      try {
        const w = typeof node.width === 'function' ? node.width() : 100;
        const h = typeof node.height === 'function' ? node.height() : 100;
        node.fillPatternImage(qrImage);
        node.fillPatternScaleX((w || 100) / 100);
        node.fillPatternScaleY((h || 100) / 100);
        node.fillPatternRepeat('no-repeat');
        node.getLayer()?.batchDraw();
      } catch (err) {
        console.error('Error applying QR pattern:', err);
      }
    };
    // Bind handlers BEFORE assigning src so cached / synchronous loads still fire.
    qrImage.onload = apply;
    qrImage.onerror = (err) => console.warn('QR placeholder image failed to load', err);
    qrImage.src = QR_PLACEHOLDER_SVG;
    // If the image was already complete (cached or synchronous data URL),
    // apply right away — the load event may have fired before the listener
    // was attached.
    if (qrImage.complete && qrImage.naturalWidth > 0) {
      apply();
    }
  }

  /**
   * Export stage as image data URL
   * Useful for thumbnails and previews
   */
  static async toDataURL(stage, options = {}) {
    if (!stage) return null;
    if (typeof stage.isDestroyed === 'function' && stage.isDestroyed()) return null;

    try {
      return stage.toDataURL({
        mimeType: options.mimeType || 'image/png',
        quality: options.quality || 0.8,
        pixelRatio: options.pixelRatio || 2,
        ...options,
      });
    } catch (error) {
      console.error('Error converting stage to data URL:', error);
      return null;
    }
  }

  /**
   * Export multiple stages as images (for PDF generation)
   */
  static async toPDFData(stages) {
    if (!Array.isArray(stages)) {
      console.error('Stages must be an array');
      return [];
    }

    try {
      const pages = await Promise.all(
        stages.map((stage) =>
          CanvasSerializer.toDataURL(stage, {
            mimeType: 'image/jpeg',
            quality: 0.95,
            pixelRatio: 3, // High-res for printing
          })
        )
      );

      return pages.filter((page) => page !== null);
    } catch (error) {
      console.error('Error generating PDF data:', error);
      return [];
    }
  }

  /**
   * Clone a node with new ID
   */
  static cloneNode(node, offsetX = 0, offsetY = 0) {
    if (!node) return null;

    try {
      // CanvasTable requires special clone handling — Konva.clone() produces
      // a plain Group, not a functional CanvasTable instance.
      if (node.className === 'CanvasTable' || typeof node.toTableJSON === 'function') {
        const tableData = node.toTableJSON();
        tableData.id = `tbl_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
        tableData.x = (tableData.x ?? 0) + offsetX;
        tableData.y = (tableData.y ?? 0) + offsetY;
        return CanvasTable.fromJSON(tableData);
      }

      const cloned = node.clone({
        x: (typeof node.x === 'function' ? node.x() : 0) + offsetX,
        y: (typeof node.y === 'function' ? node.y() : 0) + offsetY,
        id: `${node.className}_${Date.now()}_${Math.random().toString(36).substring(2, 7)}`,
      });
      // Konva.clone copies event listeners too. Strip them so the new node
      // doesn't fire stale callbacks bound to the old instance.
      try { cloned.off(); } catch { /* noop */ }
      return cloned;
    } catch (error) {
      console.error('Error cloning node:', error);
      return null;
    }
  }

  /**
   * Get all nodes as plain objects (for export/save)
   */
  static getLayerData(contentLayer) {
    if (!contentLayer) return null;

    return {
      width: contentLayer.getStage()?.width() || 0,
      height: contentLayer.getStage()?.height() || 0,
      objects: this.serializeLayer(contentLayer),
    };
  }

  /**
   * Restore layer from saved data
   */
  static restoreLayerData(contentLayer, data) {
    if (!contentLayer || !data) return;

    this.loadToLayer(contentLayer, data.objects || []);
  }
}

export default CanvasSerializer;
