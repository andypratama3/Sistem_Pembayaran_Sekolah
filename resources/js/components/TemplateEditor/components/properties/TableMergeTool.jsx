/**
 * TableMergeTool.jsx — Coordinate-based merge cells utility.
 */

import React, { memo, useCallback } from 'react';

function TableMergeTool({ table, mergeRange, onChangeMergeRange, onMergeCells }) {
  if (!table) return null;

  const maxRow = Math.max(0, (table.rows ?? 1) - 1);
  const maxCol = Math.max(0, (table.cols ?? 1) - 1);

  const setField = useCallback(
    (key, rawValue) => {
      // Permit transient empty input but never commit NaN/Infinity to state
      if (rawValue === '' || rawValue === '-') {
        onChangeMergeRange((prev) => ({ ...prev, [key]: 0 }));
        return;
      }
      const parsed = parseInt(rawValue, 10);
      if (!Number.isFinite(parsed) || parsed < 0) return;
      const isRow = key.toLowerCase().includes('row');
      const ceiling = isRow ? maxRow : maxCol;
      const clamped = Math.min(ceiling, parsed);
      onChangeMergeRange((prev) => ({ ...prev, [key]: clamped }));
    },
    [onChangeMergeRange, maxRow, maxCol]
  );

  // Validate before merging — start <= end for both dimensions
  const isValidRange =
    mergeRange.startRow <= mergeRange.endRow &&
    mergeRange.startCol <= mergeRange.endCol &&
    (mergeRange.startRow !== mergeRange.endRow || mergeRange.startCol !== mergeRange.endCol);

  const handleKeyDown = useCallback(
    (e) => {
      if (e.key === 'Enter' && isValidRange) {
        e.preventDefault();
        onMergeCells();
      }
    },
    [onMergeCells, isValidRange]
  );

  return (
    <div className="properties-panel__section">
      <h4 className="properties-panel__section-title">Merge Cells</h4>
      <div className="properties-panel__row">
        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="merge-start-row">Start Row</label>
          <input
            id="merge-start-row"
            type="number"
            className="properties-panel__input"
            min="0"
            max={maxRow}
            value={mergeRange.startRow}
            onChange={(e) => setField('startRow', e.target.value)}
            onKeyDown={handleKeyDown}
            aria-label="Merge start row"
          />
        </div>
        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="merge-start-col">Start Col</label>
          <input
            id="merge-start-col"
            type="number"
            className="properties-panel__input"
            min="0"
            max={maxCol}
            value={mergeRange.startCol}
            onChange={(e) => setField('startCol', e.target.value)}
            onKeyDown={handleKeyDown}
            aria-label="Merge start column"
          />
        </div>
      </div>
      <div className="properties-panel__row">
        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="merge-end-row">End Row</label>
          <input
            id="merge-end-row"
            type="number"
            className="properties-panel__input"
            min="0"
            max={maxRow}
            value={mergeRange.endRow}
            onChange={(e) => setField('endRow', e.target.value)}
            onKeyDown={handleKeyDown}
            aria-label="Merge end row"
          />
        </div>
        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="merge-end-col">End Col</label>
          <input
            id="merge-end-col"
            type="number"
            className="properties-panel__input"
            min="0"
            max={maxCol}
            value={mergeRange.endCol}
            onChange={(e) => setField('endCol', e.target.value)}
            onKeyDown={handleKeyDown}
            aria-label="Merge end column"
          />
        </div>
      </div>
      {!isValidRange && (
        <small
          className="properties-panel__help-text"
          style={{ color: '#dc2626', display: 'block', marginTop: '4px' }}
          role="alert"
        >
          Range must span at least two cells with start ≤ end.
        </small>
      )}
      <button
        type="button"
        className="properties-panel__gradient-btn mt-2"
        onClick={onMergeCells}
        disabled={!isValidRange}
        aria-disabled={!isValidRange}
      >
        Merge Selected Range
      </button>
    </div>
  );
}

export default memo(TableMergeTool);
