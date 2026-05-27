/**
 * StarterGalleryModal.jsx — Starter blueprint picker.
 *
 * Renders the gallery; data lives in `constants/starterTemplates.js`.
 */

import React, { useEffect, useRef } from 'react';
import { useTemplateStore, PAGE_SIZES } from '../store/useTemplateStore';
import { STARTER_TEMPLATES, STARTER_DEFAULT_FIELDS } from '../constants/starterTemplates';

// Use crypto.randomUUID when available; fall back to a Math.random-based id.
const generateId = (prefix) => {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return `${prefix}_${crypto.randomUUID()}`;
  }
  return `${prefix}_${Math.random().toString(36).substring(2, 11)}_${Date.now().toString(36)}`;
};

const FABRIC_TO_KONVA_TYPES = {
  textbox: 'Text',
  'i-text': 'Text',
  text: 'Text',
  rect: 'Rect',
  circle: 'Circle',
  ellipse: 'Ellipse',
  line: 'Line',
  image: 'Image',
  path: 'Path',
  group: 'Group',
  table: 'CanvasTable',
};

const FABRIC_ATTR_RENAME = {
  left: 'x',
  top: 'y',
  textAlign: 'align',
  // charSpacing in Fabric is in 1/1000 em units. Konva uses `letterSpacing` in pixels.
  // A value of 200 in Fabric ≈ ~2-4px in Konva depending on font size.
  // We'll convert it proportionally.
};

/**
 * Convert charSpacing from Fabric (1/1000 em) to Konva letterSpacing (pixels).
 * Fabric charSpacing 100 ≈ 0.1em. At 16px font, that's ~1.6px.
 */
function convertCharSpacing(charSpacing, fontSize = 16) {
  if (!charSpacing || charSpacing === 0) return undefined;
  // Fabric uses 1/1000 em, so divide by 1000 and multiply by fontSize
  return Math.round((charSpacing / 1000) * fontSize * 10) / 10;
}

function fabricToKonva(obj) {
  const className = FABRIC_TO_KONVA_TYPES[obj.type];
  if (!className) return null;

  if (className === 'CanvasTable') {
    // Convert Fabric table format (data: 2D array, columnWidths, rowHeights)
    // to Konva CanvasTable format (cells: {"row,col": {text}}, colWidths, rows, cols)
    const x = obj.left ?? 0;
    const y = obj.top ?? 0;
    const data = obj.data || [];
    const rows = obj.rows ?? data.length ?? 3;
    const cols = obj.cols ?? (data[0]?.length ?? 3);
    const colWidths = obj.columnWidths || Array(cols).fill(120);
    const rowHeights = obj.rowHeights || Array(rows).fill(40);

    // Convert 2D data array to cells map
    const cells = {};
    for (let r = 0; r < rows; r++) {
      for (let c = 0; c < cols; c++) {
        const text = data[r]?.[c] ?? '';
        if (text || r === 0) {
          cells[`${r},${c}`] = {
            text,
            style: r === 0 ? { bold: true } : undefined,
          };
        }
      }
    }

    return {
      className: 'CanvasTable',
      id: generateId('tbl'),
      x,
      y,
      rows,
      cols,
      colWidths,
      rowHeights,
      cells,
      draggable: true,
      style: {
        headerBg: '#ffffff',
        borderColor: obj.borderColor || '#94a3b8',
        fontSize: obj.tableFontSize || 11,
        fontFamily: obj.tableFontFamily || 'Arial',
      },
      dynamicSource: obj.dynamicSource || null,
    };
  }

  const attrs = {};
  for (const [key, value] of Object.entries(obj)) {
    if (key === 'type' || key === 'id' || key === 'charSpacing') continue;
    const konvaKey = FABRIC_ATTR_RENAME[key] || key;
    attrs[konvaKey] = value;
  }

  // Handle charSpacing → letterSpacing conversion
  if (obj.charSpacing) {
    const fontSize = obj.fontSize || 16;
    const converted = convertCharSpacing(obj.charSpacing, fontSize);
    if (converted) {
      attrs.letterSpacing = converted;
    }
  }

  if (className === 'Line' && obj.x1 != null && obj.y1 != null) {
    attrs.points = [
      obj.x1 ?? 0, obj.y1 ?? 0,
      obj.x2 ?? obj.x1 ?? 0, obj.y2 ?? obj.y1 ?? 0,
    ];
    delete attrs.x1; delete attrs.y1; delete attrs.x2; delete attrs.y2;
  }

  if (className === 'Text' && obj.fontWeight) {
    const styleParts = [];
    if (obj.fontWeight === 'bold' || obj.fontWeight > 400) styleParts.push('bold');
    if (obj.fontStyle === 'italic') styleParts.push('italic');
    if (styleParts.length) attrs.fontStyle = styleParts.join(' ');
    delete attrs.fontWeight;
  }

  if (className === 'Text' && obj.underline) {
    attrs.textDecoration = 'underline';
    delete attrs.underline;
  }

  // Ensure text nodes have draggable
  if (className === 'Text') {
    attrs.draggable = true;
  }

  return { className, attrs };
}

function StarterGalleryModal({ isOpen, onClose }) {
  const setPages = useTemplateStore((s) => s.setPages);
  const setFields = useTemplateStore((s) => s.setFields);
  const saveState = useTemplateStore((s) => s.saveState);
  const setPageSize = useTemplateStore((s) => s.setPageSize);

  const dialogRef = useRef(null);
  const previousFocusRef = useRef(null);

  // Close on Escape and restore focus when the modal closes.
  useEffect(() => {
    if (!isOpen) return;

    previousFocusRef.current = document.activeElement;

    const handleKey = (e) => {
      if (e.key === 'Escape') {
        e.stopPropagation();
        onClose();
      }
    };
    window.addEventListener('keydown', handleKey);

    const focusTimer = setTimeout(() => {
      dialogRef.current?.focus();
    }, 0);

    return () => {
      window.removeEventListener('keydown', handleKey);
      clearTimeout(focusTimer);
      const prev = previousFocusRef.current;
      if (prev && typeof prev.focus === 'function') {
        prev.focus();
      }
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const handleSelectStarter = (starter) => {
    setPageSize(starter.pageSize);

    const convertedObjects = starter.objects
      .map((obj) => {
        const konva = fabricToKonva(obj);
        if (!konva) {
          console.warn('[StarterGallery] Skipped unknown object type:', obj.type);
          return null;
        }
        // CanvasTable objects are already in final format (no attrs wrapper)
        if (konva.className === 'CanvasTable' && !konva.attrs) {
          if (!konva.id) konva.id = generateId('tbl');
          return konva;
        }
        // Regular objects use { className, attrs } format
        if (!konva.attrs) konva.attrs = {};
        konva.attrs.id = generateId('obj');
        return konva;
      })
      .filter(Boolean);

    setPages([
      {
        id: generateId('page'),
        width: PAGE_SIZES[starter.pageSize].width,
        height: PAGE_SIZES[starter.pageSize].height,
        objects: convertedObjects,
      },
    ]);

    setFields([...STARTER_DEFAULT_FIELDS, ...starter.fields]);

    saveState();
    onClose();
  };

  return (
    <div
      className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[1000] flex items-center justify-center p-8 animate-in fade-in duration-300"
      onClick={onClose}
    >
      <div
        ref={dialogRef}
        tabIndex={-1}
        role="dialog"
        aria-modal="true"
        aria-labelledby="starter-gallery-title"
        onClick={(e) => e.stopPropagation()}
        className="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-4xl border border-slate-200 dark:border-slate-800 overflow-hidden animate-in zoom-in duration-500 flex flex-col max-h-[85vh] focus:outline-none"
      >
        <div className="px-10 py-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
          <div>
            <h2 id="starter-gallery-title" className="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tighter">
              Choose Template Starter Blueprint
            </h2>
            <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
              Accelerate school document creation with rich design presets
            </p>
          </div>
          <button
            onClick={onClose}
            className="w-12 h-12 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:text-rose-500 transition-colors"
            aria-label="Close gallery"
          >
            <i className="feather-x text-lg" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-10 grid grid-cols-1 md:grid-cols-3 gap-6 custom-scrollbar bg-slate-50/50 dark:bg-slate-900/50">
          {STARTER_TEMPLATES.map((starter) => (
            <button
              key={starter.id}
              type="button"
              onClick={() => handleSelectStarter(starter)}
              className="group flex flex-col bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-indigo-500/50 dark:hover:border-indigo-900/50 transition-all duration-300 cursor-pointer active:scale-[0.98] text-left"
            >
              <div className={`h-40 bg-gradient-to-tr ${starter.color} p-5 flex flex-col justify-between text-white relative overflow-hidden shrink-0`}>
                <div className="absolute -right-8 -bottom-8 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500" />
                <div className="flex items-start gap-3">
                  <div className="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-inner shrink-0">
                    <i className={`${starter.icon} text-base`} />
                  </div>
                  <div className="flex-1 min-w-0">
                    <h3 className="text-sm font-bold leading-tight line-clamp-2">
                      {starter.title}
                    </h3>
                    <p className="text-[10px] text-white/70 mt-1 line-clamp-2 leading-relaxed">
                      {starter.desc}
                    </p>
                  </div>
                </div>
                <div className="flex gap-2">
                  <span className="px-2 py-0.5 bg-white/20 backdrop-blur-md rounded-full text-[8px] font-bold uppercase tracking-wider border border-white/10">
                    {starter.category}
                  </span>
                  <span className="px-2 py-0.5 bg-white/20 backdrop-blur-md rounded-full text-[8px] font-bold uppercase tracking-wider border border-white/10 font-mono">
                    {starter.pageSize.toUpperCase()}
                  </span>
                </div>
              </div>

              <div className="px-5 py-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-700">
                <span className="text-[9px] font-bold uppercase tracking-widest text-slate-400 group-hover:text-indigo-600 transition-colors">
                  Gunakan Template
                </span>
                <i className="feather-arrow-right text-xs text-slate-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all" />
              </div>
            </button>
          ))}
        </div>

        <div className="px-10 py-6 bg-slate-50 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800 text-center shrink-0">
          <p className="text-[9px] font-black text-rose-400 uppercase tracking-[0.3em]">
            Warning: Selecting a blueprint will replace your current page layout contents.
          </p>
        </div>
      </div>
    </div>
  );
}

export default StarterGalleryModal;
