/**
 * TransformProps.jsx — Position, size, rotation, opacity controls.
 */

import React, { memo, useCallback } from 'react';

const MIN_DIMENSION = 1;

function TransformProps({ properties, onChange }) {
  // Generic numeric handler that filters NaN/Infinity and supports transient
  // empty input without committing a stale value to the Konva node.
  const handleNumeric = useCallback(
    (key, { allowNegative = true, min, max } = {}) =>
      (e) => {
        const raw = e.target.value;
        if (raw === '' || raw === '-' || raw === '.') {
          // Don't commit while user is mid-typing
          return;
        }
        const parsed = parseFloat(raw);
        if (!Number.isFinite(parsed)) return;
        if (!allowNegative && parsed < 0) return;
        let next = parsed;
        if (typeof min === 'number') next = Math.max(min, next);
        if (typeof max === 'number') next = Math.min(max, next);
        onChange(key, next);
      },
    [onChange]
  );

  const handleOpacity = useCallback(
    (e) => {
      const parsed = parseFloat(e.target.value);
      if (!Number.isFinite(parsed)) return;
      onChange('opacity', Math.max(0, Math.min(1, parsed)));
    },
    [onChange]
  );

  return (
    <>
      <div className="properties-panel__section">
        <h4 className="properties-panel__section-title">Position & Size</h4>

        <div className="properties-panel__row">
          <div className="properties-panel__field">
            <label className="properties-panel__label" htmlFor="prop-x">X</label>
            <input
              id="prop-x"
              type="number"
              className="properties-panel__input"
              value={Math.round(properties.x || 0)}
              onChange={handleNumeric('x')}
              aria-label="X position"
            />
          </div>
          <div className="properties-panel__field">
            <label className="properties-panel__label" htmlFor="prop-y">Y</label>
            <input
              id="prop-y"
              type="number"
              className="properties-panel__input"
              value={Math.round(properties.y || 0)}
              onChange={handleNumeric('y')}
              aria-label="Y position"
            />
          </div>
        </div>

        <div className="properties-panel__row">
          <div className="properties-panel__field">
            <label className="properties-panel__label" htmlFor="prop-width">Width</label>
            <input
              id="prop-width"
              type="number"
              className="properties-panel__input"
              min={MIN_DIMENSION}
              value={Math.round(properties.width || 0)}
              onChange={handleNumeric('width', { allowNegative: false, min: MIN_DIMENSION })}
              aria-label="Width"
            />
          </div>
          <div className="properties-panel__field">
            <label className="properties-panel__label" htmlFor="prop-height">Height</label>
            <input
              id="prop-height"
              type="number"
              className="properties-panel__input"
              min={MIN_DIMENSION}
              value={Math.round(properties.height || 0)}
              onChange={handleNumeric('height', { allowNegative: false, min: MIN_DIMENSION })}
              aria-label="Height"
            />
          </div>
        </div>
      </div>

      <div className="properties-panel__section">
        <h4 className="properties-panel__section-title">Transform</h4>

        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="prop-rotation">Rotation</label>
          <input
            id="prop-rotation"
            type="number"
            className="properties-panel__input"
            min="-360"
            max="360"
            step="1"
            value={Math.round(properties.rotation || 0)}
            onChange={handleNumeric('rotation', { min: -360, max: 360 })}
            aria-label="Rotation in degrees"
          />
        </div>

        <div className="properties-panel__field">
          <label className="properties-panel__label" htmlFor="prop-opacity">Opacity</label>
          <div className="properties-panel__slider-container">
            <input
              id="prop-opacity"
              type="range"
              className="properties-panel__slider"
              min="0"
              max="1"
              step="0.05"
              value={properties.opacity ?? 1}
              onChange={handleOpacity}
              aria-label="Opacity"
              aria-valuemin={0}
              aria-valuemax={1}
              aria-valuenow={properties.opacity ?? 1}
            />
            <span className="properties-panel__value" aria-live="polite">
              {Math.round((properties.opacity ?? 1) * 100)}%
            </span>
          </div>
        </div>
      </div>
    </>
  );
}

export default memo(TransformProps);
