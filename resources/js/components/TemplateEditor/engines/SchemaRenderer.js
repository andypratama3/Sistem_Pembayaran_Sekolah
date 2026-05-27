/**
 * SchemaRenderer.js — JSON schema → Konva nodes
 *
 * Single point of truth for converting serialized canvas objects into live Konva
 * nodes. Replaces ad-hoc `Konva.Node.create()` scattered across the codebase.
 *
 * Why exist:
 *   - Centralizes Konva instantiation (CanvasTable needs special handling).
 *   - Resolves {{variables}} during render (live preview with real data).
 *   - Expands dynamic tables (DynamicTableEngine).
 *   - Returns nodes ready to add to a Konva.Layer.
 */

import Konva from 'konva';
import { CanvasTable } from '../canvas/CanvasTable';
import { VariableEngine } from './VariableEngine';
import { DynamicTableEngine } from './DynamicTableEngine';

// Whitelist of Konva classNames the renderer is allowed to instantiate.
const ALLOWED_CLASSNAMES = new Set([
  'Text', 'Rect', 'Circle', 'Ellipse', 'Line', 'Path', 'Arrow',
  'Image', 'Group', 'Star', 'RegularPolygon', 'Ring', 'Wedge',
  'Arc', 'Sprite', 'Label', 'Tag', 'TextPath',
  'CanvasTable',
]);

// URL schemes that are safe for image src and link href.
const SAFE_URL_RE = /^(https?:|data:image\/|\/|\.\.?\/|#)/i;

function isSafeUrl(url) {
  if (typeof url !== 'string' || url.length === 0) return false;
  // Trim and lowercase the leading whitespace; reject javascript: / vbscript: etc.
  const trimmed = url.trim();
  if (/^(javascript|vbscript|file|data:text|data:application):/i.test(trimmed)) return false;
  return SAFE_URL_RE.test(trimmed);
}

export class SchemaRenderer {
  /**
   * Render a list of schema descriptors into Konva nodes.
   *
   * @param {Array<object>} descriptors — output of CanvasSerializer.serializeLayer
   * @param {object} [options]
   * @param {object} [options.context]    — variable context for {{var}} resolution
   * @param {boolean} [options.live=false]— when true, resolve variables and dynamic tables
   * @returns {Array<Konva.Node>}
   */
  static render(descriptors, options = {}) {
    if (!Array.isArray(descriptors)) return [];

    const context = options.context ?? {};
    const live = options.live === true;
    const variableEngine = live ? new VariableEngine(context) : null;

    return descriptors
      .map((descriptor) => SchemaRenderer.renderOne(descriptor, { variableEngine, context, live }))
      .filter(Boolean);
  }

  /**
   * Render a single descriptor.
   *
   * @returns {Konva.Node | null}
   */
  static renderOne(descriptor, { variableEngine, context, live } = {}) {
    if (!descriptor || typeof descriptor !== 'object') return null;

    const className = descriptor.className ?? descriptor.attrs?.className;

    // Whitelist guard: refuse to instantiate unknown / unsafe classes.
    if (!className || !ALLOWED_CLASSNAMES.has(className)) {
      console.warn('[SchemaRenderer] Refused to render unknown className:', className);
      return null;
    }

    try {
      if (className === 'CanvasTable') {
        const expanded = live
          ? DynamicTableEngine.expand(descriptor, context)
          : descriptor;
        return CanvasTable.fromJSON(expanded);
      }

      // Sanitize descriptor before passing to Konva.Node.create — strip dangerous URLs
      // and any prototype-pollution attempts on attrs.
      const safeDescriptor = SchemaRenderer._sanitizeDescriptor(descriptor);

      // Konva built-in shapes — clone and resolve text variables on Text nodes.
      const node = Konva.Node.create(safeDescriptor);
      if (!node) return null;

      if (live && variableEngine && node.className === 'Text') {
        const resolved = variableEngine.resolve(node.text());
        // Sanitize resolved text to prevent XSS
        const sanitized = SchemaRenderer._sanitizeText(resolved);
        node.text(sanitized);
      }

      // Make sure draggable defaults to true (matches editor UX).
      if (typeof node.draggable === 'function' && !node.getAttr('locked')) {
        if (node.draggable() === undefined || node.draggable() === false) {
          // Only force true when the descriptor didn't explicitly set false.
          const explicitDraggable = descriptor.attrs?.draggable ?? descriptor.draggable;
          if (explicitDraggable !== false) {
            node.draggable(true);
          }
        }
      }

      return node;
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error('[SchemaRenderer] Failed to render descriptor', descriptor, error);
      return null;
    }
  }

  /**
   * Strip dangerous URLs from image/link descriptors and remove dangerous keys
   * (__proto__, constructor, prototype) that could enable prototype pollution.
   * Also strips internal attributes that should not be persisted/restored.
   */
  static _sanitizeDescriptor(descriptor) {
    // Internal attributes that should never be passed to Konva.Node.create()
    // These are runtime-only markers set by the editor hooks.
    const INTERNAL_ATTRS = new Set([
      '_eventsBound', '_dragMoveHandler', '_lastSyncTime',
      '_isSelected', '_originalStroke', '_originalStrokeWidth',
    ]);

    const cleanAttrs = (attrs) => {
      if (!attrs || typeof attrs !== 'object') return attrs;
      const out = {};
      for (const key of Object.keys(attrs)) {
        if (key === '__proto__' || key === 'constructor' || key === 'prototype') continue;
        if (INTERNAL_ATTRS.has(key)) continue;
        const val = attrs[key];
        if ((key === 'src' || key === 'href' || key === 'image') && typeof val === 'string') {
          if (!isSafeUrl(val)) {
            console.warn('[SchemaRenderer] Stripped unsafe URL:', val);
            continue;
          }
        }
        out[key] = val;
      }
      return out;
    };

    const out = { ...descriptor };
    if (out.attrs) out.attrs = cleanAttrs(out.attrs);
    // Konva.Node.create allows attrs at the top level too — sanitize if present.
    if (out.children && Array.isArray(out.children)) {
      out.children = out.children.map((c) => SchemaRenderer._sanitizeDescriptor(c));
    }
    return out;
  }

  /**
   * Sanitize text to prevent XSS attacks.
   * Removes HTML tags and script content.
   */
  static _sanitizeText(text) {
    if (typeof text !== 'string') return text;
    // Remove HTML tags and script content
    return text
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
      .replace(/<[^>]+>/g, '');
  }

  /**
   * Convenience: render schema directly into an existing Konva layer.
   *
   * @param {Konva.Layer} layer
   * @param {Array<object>} descriptors
   * @param {object} [options]
   */
  static renderInto(layer, descriptors, options = {}) {
    if (!layer) {
      console.error('[SchemaRenderer] renderInto called with null/undefined layer');
      return [];
    }
    layer.destroyChildren();
    const nodes = SchemaRenderer.render(descriptors, options);
    nodes.forEach((node) => layer.add(node));
    layer.batchDraw();
    return nodes;
  }
}

export default SchemaRenderer;
