/**
 * TableCellEditor.jsx — Per-cell editing: text, variable binding, styling.
 */

import React, { memo, useCallback } from 'react';

const MIN_FONT_SIZE = 6;
const MAX_FONT_SIZE = 72;
const DEFAULT_FONT_SIZE = 13;
const MIN_PADDING = 0;
const MAX_PADDING = 40;

function TableCellEditor({
  selectedCell,
  cellData,
  cellStyle,
  variables = [],
  onTextChange,
  onStyleChange,
  onInsertVariable,
}) {
  const handleInsert = useCallback(
    (e) => {
      const v = e.target.value;
      if (v) onInsertVariable(v);
    },
    [onInsertVariable]
  );

  const handleFontSize = useCallback(
    (e) => {
      const raw = e.target.value;
      if (raw === '') return;
      const parsed = parseInt(raw, 10);
      if (!Number.isFinite(parsed)) return;
      onStyleChange('fontSize', Math.max(MIN_FONT_SIZE, Math.min(MAX_FONT_SIZE, parsed)));
    },
    [onStyleChange]
  );

  const handlePadding = useCallback(
    (e) => {
      const raw = e.target.value;
      if (raw === '') return;
      const parsed = parseInt(raw, 10);
      if (!Number.isFinite(parsed)) return;
      onStyleChange('padding', Math.max(MIN_PADDING, Math.min(MAX_PADDING, parsed)));
    },
    [onStyleChange]
  );

  if (!selectedCell) return null;

  const safeCellData = cellData || {};
  const safeCellStyle = cellStyle || {};

  return (
    <div className="properties-panel__section">
      <h4 className="properties-panel__section-title">
        Cell Editor ({selectedCell.row}, {selectedCell.col})
      </h4>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="cell-text">Cell Text</label>
        <textarea
          id="cell-text"
          className="properties-panel__textarea"
          value={safeCellData.text || ''}
          onChange={(e) => onTextChange(e.target.value)}
          placeholder="Enter cell text..."
          aria-label="Cell text"
        />
      </div>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="cell-variable">Bind Variable</label>
        <select
          id="cell-variable"
          className="properties-panel__select"
          value=""
          onChange={handleInsert}
          aria-label="Insert variable into cell"
        >
          <option value="">-- Choose field/variable --</option>
          {variables.map((v) => (
            <option key={v.id} value={v.id}>
              {v.isCustom ? `[Field] ${v.name}` : `[System] ${v.name}`}
            </option>
          ))}
        </select>
      </div>

      <div className="properties-panel__row">
        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="cell-bg">Cell Background</label>
          <div className="properties-panel__color-input">
            <input
              id="cell-bg"
              type="color"
              className="properties-panel__color"
              value={safeCellStyle.bg || '#ffffff'}
              onChange={(e) => onStyleChange('bg', e.target.value)}
              aria-label="Cell background color"
            />
          </div>
        </div>

        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="cell-color">Text Color</label>
          <div className="properties-panel__color-input">
            <input
              id="cell-color"
              type="color"
              className="properties-panel__color"
              value={safeCellStyle.color || '#1e293b'}
              onChange={(e) => onStyleChange('color', e.target.value)}
              aria-label="Cell text color"
            />
          </div>
        </div>
      </div>

      <div className="properties-panel__row">
        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="cell-font-size">Font Size</label>
          <input
            id="cell-font-size"
            type="number"
            className="properties-panel__input"
            min={MIN_FONT_SIZE}
            max={MAX_FONT_SIZE}
            step="1"
            value={safeCellStyle.fontSize ?? DEFAULT_FONT_SIZE}
            onChange={handleFontSize}
            aria-label="Cell font size"
          />
        </div>

        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="cell-padding">Cell Padding</label>
          <input
            id="cell-padding"
            type="number"
            className="properties-panel__input"
            min={MIN_PADDING}
            max={MAX_PADDING}
            step="1"
            value={safeCellStyle.padding ?? 8}
            onChange={handlePadding}
            aria-label="Cell padding"
          />
        </div>
      </div>

      <div className="properties-panel__field">
        <label className="properties-panel__label" id="cell-format-label">Text Formatting</label>
        <div
          className="properties-panel__format-row"
          role="group"
          aria-labelledby="cell-format-label"
        >
          <button
            type="button"
            className={`properties-panel__toggle-btn ${safeCellStyle.bold ? 'active' : ''}`}
            onClick={() => onStyleChange('bold', !safeCellStyle.bold)}
            title="Bold"
            aria-label="Toggle bold"
            aria-pressed={Boolean(safeCellStyle.bold)}
          >
            <strong>B</strong>
          </button>
          <button
            type="button"
            className={`properties-panel__toggle-btn ${safeCellStyle.italic ? 'active' : ''}`}
            onClick={() => onStyleChange('italic', !safeCellStyle.italic)}
            title="Italic"
            aria-label="Toggle italic"
            aria-pressed={Boolean(safeCellStyle.italic)}
          >
            <em>I</em>
          </button>
          <button
            type="button"
            className={`properties-panel__toggle-btn ${safeCellStyle.underline ? 'active' : ''}`}
            onClick={() => onStyleChange('underline', !safeCellStyle.underline)}
            title="Underline"
            aria-label="Toggle underline"
            aria-pressed={Boolean(safeCellStyle.underline)}
          >
            <u>U</u>
          </button>
        </div>
      </div>

      <div className="properties-panel__field">
        <label className="properties-panel__label" id="cell-align-label">Text Alignment</label>
        <div className="properties-panel__align-group">
          <div
            className="properties-panel__align-subgroup"
            role="group"
            aria-labelledby="cell-align-label"
          >
            {['left', 'center', 'right', 'justify'].map((align) => {
              const active = (safeCellStyle.align || 'left') === align;
              return (
                <button
                  key={align}
                  type="button"
                  className={`properties-panel__icon-btn ${active ? 'active' : ''}`}
                  onClick={() => onStyleChange('align', align)}
                  title={`Align ${align}`}
                  aria-label={`Horizontal align ${align}`}
                  aria-pressed={active}
                >
                  {{ left: '◀', center: '▲', right: '▶', justify: '☰' }[align]}
                </button>
              );
            })}
          </div>

          <div className="properties-panel__align-subgroup" role="group" aria-label="Vertical alignment">
            {['top', 'middle', 'bottom'].map((vAlign) => {
              const active = (safeCellStyle.verticalAlign || 'middle') === vAlign;
              return (
                <button
                  key={vAlign}
                  type="button"
                  className={`properties-panel__icon-btn ${active ? 'active' : ''}`}
                  onClick={() => onStyleChange('verticalAlign', vAlign)}
                  title={`Align ${vAlign}`}
                  aria-label={`Vertical align ${vAlign}`}
                  aria-pressed={active}
                >
                  {{ top: '┳', middle: '┫', bottom: '┻' }[vAlign]}
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}

export default memo(TableCellEditor);
