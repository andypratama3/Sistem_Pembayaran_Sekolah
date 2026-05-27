/**
 * ShortcutsHelpModal.jsx — Keyboard shortcuts reference
 * FIXED: Guard against undefined SHORTCUTS keys, proper isOpen handling
 */

import React, { useEffect, useRef } from 'react';
import { SHORTCUTS, formatShortcut } from '../hooks/useKeyboardShortcuts';

function ShortcutsHelpModal({ isOpen, onClose }) {
  const dialogRef = useRef(null);
  const previousFocusRef = useRef(null);

  // Close on Escape, restore focus on unmount, and move focus into dialog
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

    // Move focus into the dialog so screen-readers / keyboard users land here
    const focusTimer = setTimeout(() => {
      dialogRef.current?.focus();
    }, 0);

    return () => {
      window.removeEventListener('keydown', handleKey);
      clearTimeout(focusTimer);
      // Restore focus to the element that opened the modal
      const prev = previousFocusRef.current;
      if (prev && typeof prev.focus === 'function') {
        prev.focus();
      }
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const categories = {
    'Selection': ['SELECT_ALL', 'DESELECT'],
    'Editing': ['DUPLICATE', 'DELETE', 'CUT', 'COPY', 'PASTE'],
    'Grouping': ['GROUP', 'UNGROUP'],
    'Visibility': ['LOCK', 'HIDE'],
    'History': ['UNDO', 'REDO'],
    'Zoom': ['ZOOM_IN', 'ZOOM_OUT', 'ZOOM_100'],
  };

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-[1000] p-4 animate-in fade-in duration-300" onClick={onClose}>
      <div
        ref={dialogRef}
        tabIndex={-1}
        role="dialog"
        aria-modal="true"
        aria-labelledby="shortcuts-help-title"
        className="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-lg w-full max-h-[75vh] overflow-hidden flex flex-col focus:outline-none animate-in zoom-in duration-300"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
          <div>
            <h2 id="shortcuts-help-title" className="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tighter">Keyboard Shortcuts</h2>
            <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Quick reference for editor commands</p>
          </div>
          <button
            onClick={onClose}
            aria-label="Close shortcuts dialog"
            className="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 transition-colors"
          >
            <i className="feather-x text-lg"></i>
          </button>
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar bg-slate-50/50 dark:bg-slate-900/50">
          {Object.entries(categories).map(([category, shortcutKeys]) => (
            <div key={category}>
              <h3 className="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">
                {category}
              </h3>
              <div className="space-y-1">
                {shortcutKeys.map((key) => {
                  const shortcut = SHORTCUTS[key];
                  if (!shortcut) return null;
                  return (
                    <div
                      key={key}
                      className="flex items-center justify-between py-2.5 px-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-800 shadow-sm hover:border-indigo-500/30 transition-colors"
                    >
                      <span className="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tight">{shortcut.label}</span>
                      <kbd className="px-2 py-1 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-[10px] font-mono text-slate-500 dark:text-slate-400 shadow-inner">
                        {formatShortcut(shortcut)}
                      </kbd>
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </div>

        {/* Footer */}
        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0 bg-white dark:bg-slate-900">
          <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            <i className="feather-info mr-1 text-indigo-400" /> Press <kbd className="px-1.5 py-0.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[9px] font-mono">Shift + ?</kbd> to toggle
          </p>
          <button
            onClick={onClose}
            className="px-6 py-2 bg-slate-900 dark:bg-slate-800 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-black dark:hover:bg-slate-700 transition-all"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  );
}

export default ShortcutsHelpModal;
