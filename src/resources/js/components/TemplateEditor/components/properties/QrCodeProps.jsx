/**
 * QrCodeProps.jsx — QR Code content & dynamic variable binding.
 */

import React, { memo, useCallback } from 'react';

function QrCodeProps({ properties, onChange, variables = [] }) {
  const handleInsertVariable = useCallback(
    (varId) => {
      if (!varId) return;
      const current = properties.qrContent || '';
      onChange('qrContent', current + `{{${varId}}}`);
    },
    [properties.qrContent, onChange]
  );

  return (
    <div className="properties-panel__section">
      <h4 className="properties-panel__section-title">QR Code Settings</h4>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-qr-content">
          QR Code Content / URL
        </label>
        <textarea
          id="prop-qr-content"
          className="properties-panel__textarea"
          value={properties.qrContent || ''}
          onChange={(e) => onChange('qrContent', e.target.value)}
          placeholder="Enter QR content (e.g. school URL) or bind variables..."
          rows={3}
          aria-label="QR code content"
        />
        <small className="properties-panel__help-text" style={{ fontSize: '11px', color: '#64748b', marginTop: '4px', display: 'block' }}>
          Supports dynamic variables like <code>{`{{student_id}}`}</code>
        </small>
      </div>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-qr-variable">
          Bind Variable
        </label>
        <select
          id="prop-qr-variable"
          className="properties-panel__select"
          value=""
          onChange={(e) => handleInsertVariable(e.target.value)}
          aria-label="Insert variable into QR content"
        >
          <option value="">-- Choose field/variable --</option>
          {variables.map((v) => (
            <option key={v.id} value={v.id}>
              {v.isCustom ? `[Field] ${v.name}` : `[System] ${v.name}`}
            </option>
          ))}
        </select>
      </div>
    </div>
  );
}

export default memo(QrCodeProps);
