/**
 * AppearanceProps.jsx — Fill / stroke / stroke width controls for shapes.
 */

import React, { memo, useCallback } from 'react';

// Validate hex color format (#rgb, #rrggbb, #rrggbbaa)
const HEX_COLOR_RE = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/;

function AppearanceProps({ properties, onChange }) {
  const handleColorTextChange = useCallback(
    (key, value) => {
      // Allow user to keep typing; only commit valid hex colors to Konva node
      if (value === '' || HEX_COLOR_RE.test(value)) {
        onChange(key, value || (key === 'fill' ? '#ffffff' : '#000000'));
      }
    },
    [onChange]
  );

  const handleStrokeWidthChange = useCallback(
    (e) => {
      const raw = e.target.value;
      if (raw === '') {
        onChange('strokeWidth', 0);
        return;
      }
      const parsed = parseFloat(raw);
      if (Number.isFinite(parsed) && parsed >= 0) {
        onChange('strokeWidth', parsed);
      }
    },
    [onChange]
  );

  return (
    <div className="properties-panel__section">
      <h4 className="properties-panel__section-title">Appearance</h4>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-fill-color">Fill Color</label>
        <div className="properties-panel__color-input">
          <input
            id="prop-fill-color"
            type="color"
            className="properties-panel__color"
            aria-label="Fill color picker"
            value={properties.fill || '#ffffff'}
            onChange={(e) => onChange('fill', e.target.value)}
          />
          <input
            type="text"
            className="properties-panel__input"
            aria-label="Fill color hex value"
            value={properties.fill || '#ffffff'}
            onChange={(e) => handleColorTextChange('fill', e.target.value)}
            placeholder="#ffffff"
            pattern="^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$"
          />
        </div>
      </div>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-stroke-color">Stroke Color</label>
        <div className="properties-panel__color-input">
          <input
            id="prop-stroke-color"
            type="color"
            className="properties-panel__color"
            aria-label="Stroke color picker"
            value={properties.stroke || '#000000'}
            onChange={(e) => onChange('stroke', e.target.value)}
          />
          <input
            type="text"
            className="properties-panel__input"
            aria-label="Stroke color hex value"
            value={properties.stroke || '#000000'}
            onChange={(e) => handleColorTextChange('stroke', e.target.value)}
            placeholder="#000000"
            pattern="^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$"
          />
        </div>
      </div>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-stroke-width">Stroke Width</label>
        <input
          id="prop-stroke-width"
          type="number"
          className="properties-panel__input"
          min="0"
          step="0.5"
          value={properties.strokeWidth ?? 1}
          onChange={handleStrokeWidthChange}
        />
      </div>
    </div>
  );
}

export default memo(AppearanceProps);
