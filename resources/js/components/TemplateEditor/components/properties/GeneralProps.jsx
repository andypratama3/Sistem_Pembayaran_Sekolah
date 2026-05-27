/**
 * GeneralProps.jsx — Name + read-only ID for any selected node.
 */

import React, { memo } from 'react';

function GeneralProps({ properties, onChange }) {
  return (
    <div className="properties-panel__section">
      <h4 className="properties-panel__section-title">General</h4>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-name">Name</label>
        <input
          id="prop-name"
          type="text"
          className="properties-panel__input"
          value={properties.name || ''}
          onChange={(e) => onChange('name', e.target.value)}
          aria-label="Object name"
          maxLength={120}
        />
      </div>

      <div className="properties-panel__field">
        <label className="properties-panel__label" htmlFor="prop-id">ID</label>
        <input
          id="prop-id"
          type="text"
          className="properties-panel__input"
          value={properties.id || ''}
          readOnly
          aria-readonly="true"
          aria-label="Object ID (read-only)"
          tabIndex={-1}
        />
      </div>
    </div>
  );
}

export default memo(GeneralProps);
