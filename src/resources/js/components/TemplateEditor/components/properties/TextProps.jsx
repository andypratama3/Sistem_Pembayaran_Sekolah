/**
 * TextProps.jsx — Properti khusus untuk elemen Teks dan Paragraf.
 */

import React, { memo, useCallback } from 'react';

const FONTS = [
  'Arial',
  'Times New Roman',
  'Courier New',
  'Georgia',
  'Verdana',
  'Trebuchet MS',
  'Impact',
  'Helvetica',
  'Palatino Linotype',
  'Book Antiqua',
  'Lucida Sans',
  'Tahoma',
  'Comic Sans MS',
  'Segoe UI',
  'Calibri',
  'Cambria',
  'Garamond',
];

const MIN_FONT_SIZE = 6;
const MAX_FONT_SIZE = 120;
const DEFAULT_FONT_SIZE = 14;

function TextProps({ properties, onChange, variables = [] }) {
  const handleInsertVariable = useCallback(
    (varId) => {
      if (!varId) return;
      const current = properties.text || '';
      onChange('text', current + `{{${varId}}}`);
    },
    [properties.text, onChange]
  );

  const handleFontSizeChange = useCallback(
    (e) => {
      const raw = e.target.value;
      // Permit transient empty input without snapping to default; commit nothing
      if (raw === '') return;
      const parsed = parseInt(raw, 10);
      if (!Number.isFinite(parsed)) return;
      const clamped = Math.max(MIN_FONT_SIZE, Math.min(MAX_FONT_SIZE, parsed));
      onChange('fontSize', clamped);
    },
    [onChange]
  );

  return (
    <div className="properties-panel__section">
      <h4 className="properties-panel__section-title">Text Settings</h4>

      {/* Konten Teks */}
      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-text-content">
          Text Content
        </label>
        <textarea
          id="prop-text-content"
          className="properties-panel__textarea"
          value={properties.text || ''}
          onChange={(e) => onChange('text', e.target.value)}
          placeholder="Enter text..."
          rows={4}
          aria-label="Text content"
        />
      </div>

      {/* Bind Variabel */}
      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-text-variable">
          Insert Variable
        </label>
        <select
          id="prop-text-variable"
          className="properties-panel__select"
          value=""
          onChange={(e) => handleInsertVariable(e.target.value)}
          aria-label="Insert variable into text"
        >
          <option value="">-- Choose field/variable --</option>
          {variables.map((v) => (
            <option key={v.id} value={v.id}>
              {v.isCustom ? `[Field] ${v.name}` : `[System] ${v.name}`}
            </option>
          ))}
        </select>
      </div>

      {/* Pengaturan Font */}
      <div className="properties-panel__row">
        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="prop-font-family">
            Font Family
          </label>
          <select
            id="prop-font-family"
            className="properties-panel__select"
            value={properties.fontFamily || 'Arial'}
            onChange={(e) => onChange('fontFamily', e.target.value)}
            aria-label="Font family"
          >
            {FONTS.map((font) => (
              <option key={font} value={font}>
                {font}
              </option>
            ))}
          </select>
        </div>

        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="prop-font-size">
            Font Size
          </label>
          <input
            id="prop-font-size"
            type="number"
            className="properties-panel__input"
            min={MIN_FONT_SIZE}
            max={MAX_FONT_SIZE}
            step="1"
            value={properties.fontSize ?? DEFAULT_FONT_SIZE}
            onChange={handleFontSizeChange}
            aria-label="Font size in pixels"
          />
        </div>
      </div>

      {/* Perataan Teks (Alignment) */}
      <div className="properties-panel__field">
        <label className="properties-panel__label" id="prop-text-align-label">Text Alignment</label>
        <div
          className="properties-panel__align-group"
          role="group"
          aria-labelledby="prop-text-align-label"
          style={{ display: 'flex', gap: '4px' }}
        >
          {['left', 'center', 'right', 'justify'].map((align) => {
            const active = (properties.align || 'left') === align;
            return (
              <button
                key={align}
                type="button"
                className={`properties-panel__icon-btn ${active ? 'active' : ''}`}
                onClick={() => onChange('align', align)}
                title={`Align ${align}`}
                aria-label={`Align text ${align}`}
                aria-pressed={active}
                style={{
                  flex: 1,
                  padding: '6px',
                  border: '1px solid #cbd5e1',
                  borderRadius: '4px',
                  background: active ? '#e2e8f0' : '#ffffff',
                  cursor: 'pointer',
                  fontWeight: active ? 'bold' : 'normal',
                }}
              >
                {{ left: '◀ Left', center: '▲ Center', right: '▶ Right', justify: '☰ Justify' }[align]}
              </button>
            );
          })}
        </div>
      </div>
    </div>
  );
}

export default memo(TextProps);
