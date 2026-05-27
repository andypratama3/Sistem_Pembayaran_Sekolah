/**
 * TableStructureProps.jsx — Add/remove rows & columns + visual grid selector.
 */

import React, { memo, useCallback } from 'react';

function TableStructureProps({
  table,
  selectedCell,
  onSelectCell,
  onAddRow,
  onRemoveRow,
  onAddCol,
  onRemoveCol,
  contentLayer,
}) {
  // Keep all hooks above the early return to obey rules-of-hooks.
  const handleSelectCell = useCallback(
    (row, col) => {
      onSelectCell({ row, col });
      // PropertiesPanel's effect propagates this to the Konva instance and
      // triggers batchDraw, so do not mutate `table._selectedCell` here.
    },
    [onSelectCell]
  );

  if (!table) return null;

  const rows = table.rows ?? 0;
  const cols = table.cols ?? 0;
  const cells = table.cells || {};

  return (
    <>
      <div className="properties-panel__section">
        <h4 className="properties-panel__section-title">Table Structure</h4>

        <div className="properties-panel__row mb-2">
          <div className="properties-panel__label">Rows: {rows}</div>
          <div className="properties-panel__label">Cols: {cols}</div>
        </div>

        <div className="properties-panel__field">
          <label className="properties-panel__label">Modify Rows</label>
          <div className="properties-panel__btn-group" role="group" aria-label="Modify rows">
            <button
              type="button"
              className="properties-panel__action-btn"
              onClick={() => onAddRow(selectedCell?.row ?? rows - 1)}
              title="Add Row Below Selected Cell"
              aria-label="Add row below selected cell"
            >
              ➕ Baris
            </button>
            <button
              type="button"
              className="properties-panel__action-btn btn-danger-outline"
              onClick={() => onRemoveRow(selectedCell?.row ?? rows - 1)}
              disabled={rows <= 1}
              title="Remove Current Row"
              aria-label="Remove current row"
            >
              ❌ Baris
            </button>
          </div>
        </div>

        <div className="properties-panel__field">
          <label className="properties-panel__label">Modify Columns</label>
          <div className="properties-panel__btn-group" role="group" aria-label="Modify columns">
            <button
              type="button"
              className="properties-panel__action-btn"
              onClick={() => onAddCol(selectedCell?.col ?? cols - 1)}
              title="Add Column After Selected Cell"
              aria-label="Add column after selected cell"
            >
              ➕ Kolom
            </button>
            <button
              type="button"
              className="properties-panel__action-btn btn-danger-outline"
              onClick={() => onRemoveCol(selectedCell?.col ?? cols - 1)}
              disabled={cols <= 1}
              title="Remove Current Column"
              aria-label="Remove current column"
            >
              ❌ Kolom
            </button>
          </div>
        </div>
      </div>

      <div className="properties-panel__section">
        <h4 className="properties-panel__section-title">Visual Grid Selector</h4>
        <p className="properties-panel__help-text">Click a cell below to style it:</p>

        <div className="properties-panel__grid-wrapper">
          <table
            className="properties-panel__mini-grid"
            role="grid"
            aria-label="Table cell selector"
          >
            <tbody>
              {Array.from({ length: rows }).map((_, r) => (
                <tr key={r}>
                  {Array.from({ length: cols }).map((_, c) => {
                    const cellKey = `${r},${c}`;
                    const cellData = cells[cellKey] || {};
                    const isCellSelected = selectedCell?.row === r && selectedCell?.col === c;

                    if (cellData._mergedInto) return null;

                    const text = typeof cellData.text === 'string' ? cellData.text : '';
                    const display = text
                      ? text.length > 6
                        ? text.substring(0, 5) + '..'
                        : text
                      : `(${r},${c})`;

                    return (
                      <td
                        key={c}
                        rowSpan={cellData.rowSpan || 1}
                        colSpan={cellData.colSpan || 1}
                        className={`properties-panel__mini-cell ${isCellSelected ? 'selected' : ''}`}
                        role="gridcell"
                        aria-selected={isCellSelected}
                        tabIndex={isCellSelected ? 0 : -1}
                        onClick={() => handleSelectCell(r, c)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            handleSelectCell(r, c);
                          }
                        }}
                      >
                        {display}
                      </td>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}

export default memo(TableStructureProps);
