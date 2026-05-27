/**
 * elementFactory.js — Data-driven element creation
 * Replaces 13 switch-case statements in Toolbar.jsx
 */

import Konva from 'konva';
import { CanvasTable } from '../canvas/CanvasTable';

/**
 * Element configuration registry
 * Data-driven approach replaces switch-case
 */
export const ELEMENT_CONFIGS = {
  text: {
    label: 'Teks',
    icon: 'feather-type',
    color: 'text-blue-500',
    section: 'text',
    create: (base) =>
      new Konva.Text({
        ...base,
        text: 'Ketik di sini',
        fontSize: 18,
        fontFamily: 'Arial',
        fill: '#1e293b',
        width: 200,
        draggable: true,
      }),
  },

  paragraph: {
    label: 'Paragraf',
    icon: 'feather-align-left',
    color: 'text-cyan-500',
    section: 'text',
    create: (base) =>
      new Konva.Text({
        ...base,
        text: 'Teks paragraf...',
        fontSize: 14,
        fontFamily: 'Arial',
        fill: '#1e293b',
        width: 300,
        wrap: 'word',
        lineHeight: 1.5,
        draggable: true,
      }),
  },

  rect: {
    label: 'Kotak',
    icon: 'feather-square',
    color: 'text-green-500',
    section: 'shapes',
    create: (base) =>
      new Konva.Rect({
        ...base,
        width: 150,
        height: 100,
        fill: '#e2e8f0',
        stroke: '#94a3b8',
        strokeWidth: 1,
        cornerRadius: 4,
        draggable: true,
      }),
  },

  circle: {
    label: 'Lingkaran',
    icon: 'feather-circle',
    color: 'text-purple-500',
    section: 'shapes',
    create: (base) =>
      new Konva.Circle({
        ...base,
        radius: 50,
        fill: '#ddd6fe',
        stroke: '#8b5cf6',
        strokeWidth: 1,
        draggable: true,
      }),
  },

  ellipse: {
    label: 'Elips',
    icon: 'feather-maximize-2',
    color: 'text-pink-500',
    section: 'shapes',
    create: (base) =>
      new Konva.Ellipse({
        ...base,
        radiusX: 80,
        radiusY: 50,
        fill: '#fce7f3',
        stroke: '#ec4899',
        strokeWidth: 1,
        draggable: true,
      }),
  },

  line: {
    label: 'Garis',
    icon: 'feather-minus',
    color: 'text-slate-500',
    section: 'shapes',
    create: (base) =>
      new Konva.Line({
        ...base,
        points: [0, 0, 200, 0],
        stroke: '#475569',
        strokeWidth: 2,
        draggable: true,
      }),
  },

  image: {
    label: 'Gambar',
    icon: 'feather-image',
    color: 'text-orange-500',
    section: 'media',
    create: (base) =>
      new Konva.Rect({
        ...base,
        width: 200,
        height: 150,
        fill: '#f1f5f9',
        stroke: '#cbd5e1',
        strokeWidth: 1,
        // Konva uses `dash`, not `strokeDashArray`. Using the wrong key meant
        // the dashed border never rendered.
        dash: [5, 5],
        draggable: true,
        name: 'image-placeholder',
      }),
  },

  table: {
    label: 'Tabel',
    icon: 'feather-grid',
    color: 'text-teal-500',
    section: 'data',
    create: (base) =>
      new CanvasTable({
        ...base,
        rows: 3,
        cols: 4,
        colWidths: [120, 120, 120, 120],
        rowHeights: [40, 40, 40],
        cells: {
          '0,0': { text: 'Header 1', style: { bold: true } },
          '0,1': { text: 'Header 2', style: { bold: true } },
          '0,2': { text: 'Header 3', style: { bold: true } },
          '0,3': { text: 'Header 4', style: { bold: true } },
        },
      }),
  },

  qrcode: {
    label: 'QR Code',
    icon: 'feather-maximize',
    color: 'text-gray-700',
    section: 'data',
    create: (base) => {
      const rect = new Konva.Rect({
        ...base,
        width: 100,
        height: 100,
        stroke: '#64748b',
        strokeWidth: 1,
        name: 'qrcode-placeholder',
        isQrCode: true,
        qrContent: 'https://school.domain/verify',
        draggable: true,
      });

      // Guard against non-browser environments (SSR / tests) where Image is
      // not defined. The placeholder still renders without the pattern.
      if (typeof Image === 'undefined') return rect;

      const qrImage = new Image();
      // Cache and load events must be wired BEFORE setting `src` so the
      // onload fires reliably even when the data URI resolves synchronously.
      qrImage.onload = () => {
        rect.getLayer()?.batchDraw();
      };
      qrImage.onerror = () => {
        // eslint-disable-next-line no-console
        console.warn('[elementFactory] QR placeholder image failed to load');
      };
      qrImage.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="white" stroke="%23cbd5e1" stroke-width="2"/><rect x="10" y="10" width="30" height="30" fill="%231e293b"/><rect x="15" y="15" width="20" height="20" fill="white"/><rect x="18" y="18" width="14" height="14" fill="%231e293b"/><rect x="60" y="10" width="30" height="30" fill="%231e293b"/><rect x="65" y="15" width="20" height="20" fill="white"/><rect x="68" y="18" width="14" height="14" fill="%231e293b"/><rect x="10" y="60" width="30" height="30" fill="%231e293b"/><rect x="15" y="65" width="20" height="20" fill="white"/><rect x="18" y="68" width="14" height="14" fill="%231e293b"/><rect x="45" y="45" width="10" height="10" fill="%231e293b"/><rect x="60" y="60" width="10" height="10" fill="%231e293b"/><rect x="70" y="70" width="20" height="20" fill="%231e293b"/><rect x="70" y="60" width="10" height="10" fill="%231e293b"/><rect x="80" y="50" width="10" height="10" fill="%231e293b"/><rect x="50" y="70" width="10" height="20" fill="%231e293b"/><rect x="60" y="80" width="10" height="10" fill="%231e293b"/><rect x="45" y="25" width="10" height="10" fill="%231e293b"/><rect x="45" y="10" width="10" height="10" fill="%231e293b"/></svg>';
      rect.fillPatternImage(qrImage);
      rect.fillPatternScaleX(1);
      rect.fillPatternScaleY(1);
      rect.fillPatternRepeat('no-repeat');

      return rect;
    },
  },

  divider: {
    label: 'Pembatas',
    icon: 'feather-more-horizontal',
    color: 'text-slate-400',
    section: 'layout',
    create: (base) =>
      new Konva.Line({
        ...base,
        points: [0, 0, 400, 0],
        stroke: '#cbd5e1',
        strokeWidth: 1,
        dash: [6, 3],
        draggable: true,
      }),
  },
};

/**
 * Factory class for creating elements
 */
export class ElementFactory {
  /**
   * Create element by type
   *
   * @param {string} type
   * @param {{ x?: number, y?: number }} [position]
   */
  static create(type, position) {
    const config = ELEMENT_CONFIGS[type];
    if (!config) {
      throw new Error(`Unknown element type: ${type}`);
    }

    // Defensive: callers occasionally pass `null` or partial positions.
    const safePosition = position && typeof position === 'object' ? position : {};
    const x = Number.isFinite(safePosition.x) ? safePosition.x : 100;
    const y = Number.isFinite(safePosition.y) ? safePosition.y : 100;

    // ID format: <type>_<time36>_<rand36>. Combining time + random avoids the
    // collision risk of plain `Date.now()` when many elements are created in
    // the same millisecond (paste-spam, batch import, etc).
    const base = {
      x,
      y,
      id: `${type}_${Date.now().toString(36)}_${Math.random()
        .toString(36)
        .slice(2, 9)}`,
      name: config.label,
    };

    return config.create(base);
  }

  /**
   * Get config for element type
   */
  static getConfig(type) {
    return ELEMENT_CONFIGS[type];
  }

  /**
   * Get all sections with their elements
   */
  static getSections() {
    const sections = {};
    Object.entries(ELEMENT_CONFIGS).forEach(([type, config]) => {
      if (!sections[config.section]) sections[config.section] = [];
      sections[config.section].push({ type, ...config });
    });
    return sections;
  }

  /**
   * Get all element types
   */
  static getTypes() {
    return Object.keys(ELEMENT_CONFIGS);
  }
}

export default ElementFactory;
