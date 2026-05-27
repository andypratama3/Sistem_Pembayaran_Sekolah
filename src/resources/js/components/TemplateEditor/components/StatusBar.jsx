/**
 * StatusBar.jsx — Bottom status bar (self-contained, reads from store)
 * FIXED: Removed prop dependency, reads all state from store directly
 */

import React, { useCallback, useMemo, memo } from 'react';
import { useTemplateStore, PAGE_SIZES } from '../store/useTemplateStore';
import './StatusBar.css';

function StatusBar() {
  const hasUnsavedChanges = useTemplateStore((s) => s.hasUnsavedChanges);
  const pages = useTemplateStore((s) => s.pages) || [];
  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const zoom = useTemplateStore((s) => s.zoom);
  const gridEnabled = useTemplateStore((s) => s.gridEnabled);
  const snappingEnabled = useTemplateStore((s) => s.snappingEnabled);
  const autoSaveEnabled = useTemplateStore((s) => s.autoSaveEnabled);
  const lastSavedAt = useTemplateStore((s) => s.lastSavedAt);
  const isSaving = useTemplateStore((s) => s.isSaving);
  const pageSize = useTemplateStore((s) => s.pageSize);
  const setZoom = useTemplateStore((s) => s.setZoom);
  const setGridEnabled = useTemplateStore((s) => s.setGridEnabled);
  const setSnappingEnabled = useTemplateStore((s) => s.setSnappingEnabled);

  const currentPageSize = PAGE_SIZES[pageSize] || PAGE_SIZES.a4_portrait;

  // Memoize formatted time so it's only recomputed when lastSavedAt changes.
  // Use the user's locale (`undefined`) instead of hardcoding 'id-ID' so the
  // timestamp respects browser settings.
  const formattedSavedTime = useMemo(() => {
    if (!lastSavedAt) return null;
    try {
      return new Date(lastSavedAt).toLocaleTimeString(undefined, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
      });
    } catch (_e) {
      return null;
    }
  }, [lastSavedAt]);

  const handleZoomIn = useCallback(() => {
    setZoom(Math.min(Math.round((zoom + 0.1) * 10) / 10, 3));
  }, [zoom, setZoom]);

  const handleZoomOut = useCallback(() => {
    setZoom(Math.max(Math.round((zoom - 0.1) * 10) / 10, 0.1));
  }, [zoom, setZoom]);

  const handleZoomReset = useCallback(() => {
    setZoom(1);
  }, [setZoom]);

  return (
    <div className="status-bar">
      <div className="status-bar__left">
        {/* Save status */}
        <div className="status-bar__item">
          <div className={`status-bar__indicator ${isSaving ? 'saving' : hasUnsavedChanges ? 'unsaved' : 'saved'}`} />
          <span className="status-bar__label">
            {isSaving
              ? 'Saving...'
              : hasUnsavedChanges
              ? 'Unsaved'
              : 'Saved'}
          </span>
          {lastSavedAt && !isSaving && (
            <span className="status-bar__time">{formattedSavedTime}</span>
          )}
        </div>

        <div className="status-bar__separator" />

        {/* Grid & Snap toggles */}
        <button
          className={`status-bar__toggle ${gridEnabled ? 'active' : ''}`}
          onClick={() => setGridEnabled(!gridEnabled)}
          title="Toggle grid"
        >
          <i className="feather-grid" />
          <span>Grid</span>
        </button>

        <button
          className={`status-bar__toggle ${snappingEnabled ? 'active' : ''}`}
          onClick={() => setSnappingEnabled(!snappingEnabled)}
          title="Toggle snapping"
        >
          <i className="feather-crosshair" />
          <span>Snap</span>
        </button>

        {autoSaveEnabled && (
          <>
            <div className="status-bar__separator" />
            <div className="status-bar__item">
              <i className="feather-refresh-cw status-bar__icon--auto" />
              <span className="status-bar__label">Auto-save</span>
            </div>
          </>
        )}
      </div>

      <div className="status-bar__right">
        {/* Page size */}
        <div className="status-bar__badge">{currentPageSize.label}</div>

        {/* Zoom controls */}
        <div className="status-bar__zoom">
          <button className="status-bar__zoom-btn" onClick={handleZoomOut} title="Zoom out">
            <i className="feather-minus" />
          </button>
          <button className="status-bar__zoom-value" onClick={handleZoomReset} title="Reset zoom">
            {Math.round(zoom * 100)}%
          </button>
          <button className="status-bar__zoom-btn" onClick={handleZoomIn} title="Zoom in">
            <i className="feather-plus" />
          </button>
        </div>

        {/* Page info */}
        <div className="status-bar__badge">
          Page {activePageIndex + 1} / {pages.length}
        </div>
      </div>
    </div>
  );
}

export default memo(StatusBar);
