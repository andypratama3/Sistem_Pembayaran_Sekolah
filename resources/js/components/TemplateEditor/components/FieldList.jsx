/**
 * FieldList.jsx — Custom fields/variables management
 * FIXED: deprecated onKeyPress → onKeyDown, added field type selection
 */

import React, { useState, useCallback, memo } from 'react';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { ElementFactory } from '../services/elementFactory';
import { CanvasSerializer } from '../canvas/CanvasSerializer';
import { showSuccess, showError } from '../services/toast';
import './FieldList.css';

const FIELD_TYPES = [
  { value: 'text', label: 'Text' },
  { value: 'number', label: 'Number' },
  { value: 'date', label: 'Date' },
  { value: 'select', label: 'Select' },
  { value: 'checkbox', label: 'Checkbox' },
  { value: 'formula', label: 'Formula' },
];

function FieldList() {
  const [newFieldName, setNewFieldName] = useState('');
  const [newFieldType, setNewFieldType] = useState('text');

  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const fields = useTemplateStore((s) => s.fields);
  const addField = useTemplateStore((s) => s.addField);
  const removeField = useTemplateStore((s) => s.removeField);

  // Add new field
  const handleAddField = useCallback(() => {
    const trimmed = newFieldName.trim();
    if (!trimmed) return;

    // Prevent duplicate field names (case-insensitive)
    const existing = (fields || []).find(
      (f) => f && f.name && f.name.toLowerCase() === trimmed.toLowerCase()
    );
    if (existing) {
      showError(`Field "${trimmed}" already exists`);
      return;
    }

    try {
      addField({
        name: trimmed,
        type: newFieldType,
        defaultValue: '',
      });

      setNewFieldName('');
      showSuccess(`Field "${trimmed}" added`);
    } catch (error) {
      console.error('Error adding field:', error);
      showError('Failed to add field');
    }
  }, [newFieldName, newFieldType, addField, fields]);

  // Handle key down (Enter to add)
  const handleKeyDown = useCallback((e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleAddField();
    }
  }, [handleAddField]);

  // Insert field as text into canvas
  const handleInsertField = useCallback((field) => {
    const stageData = stageRegistry.get(activePageIndex);
    if (!stageData?.contentLayer) {
      showError('Canvas is not ready yet. Please wait for the page to load.');
      return;
    }

    try {
      const stage = stageData.stage;
      const scale = stage.scaleX() || 1;
      const stagePos = stage.position();
      const centerX = (stage.width() / 2 - stagePos.x) / scale;
      const centerY = (stage.height() / 2 - stagePos.y) / scale;

      const node = ElementFactory.create('text', {
        x: Math.max(50, centerX - 50),
        y: Math.max(50, centerY - 20),
      });

      node.text(`{{${field.name}}}`);
      node.setAttr('fieldId', field.id);

      stageData.contentLayer.add(node);
      stageData.contentLayer.batchDraw();
      stageData.transformer.nodes([node]);

      // Setup events — guard e.evt for touch/tap events
      node.on('click tap', (e) => {
        if (e) e.cancelBubble = true;
        stageData.transformer.nodes([node]);
        useTemplateStore.getState().setSelectedObject(node);
        stageData.contentLayer.batchDraw();
      });

      node.on('dragend transformend', () => {
        const currentPageIndex = useTemplateStore.getState().activePageIndex;
        const serialized = CanvasSerializer.serializeLayer(stageData.contentLayer);
        useTemplateStore.getState().updatePage(currentPageIndex, { objects: serialized });
        useTemplateStore.getState().saveState();
      });

      // Sync
      const serialized = CanvasSerializer.serializeLayer(stageData.contentLayer);
      useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
      useTemplateStore.getState().saveState();
      useTemplateStore.getState().setSelectedObject(node);

      showSuccess(`Inserted {{${field.name}}}`);
    } catch (error) {
      console.error('Error inserting field:', error);
      showError('Failed to insert field into canvas');
    }
  }, [activePageIndex]);

  return (
    <div className="field-list">
      <div className="field-list__header">
        <h3 className="field-list__title">Template Fields</h3>
        <span className="field-list__count">{fields?.length || 0}</span>
      </div>

      <div className="field-list__add-section">
        <div className="field-list__add-row">
          <input
            type="text"
            className="field-list__input"
            placeholder="Field name..."
            value={newFieldName}
            onChange={(e) => setNewFieldName(e.target.value)}
            onKeyDown={handleKeyDown}
            aria-label="New field name"
          />
          <select
            className="field-list__type-select"
            value={newFieldType}
            onChange={(e) => setNewFieldType(e.target.value)}
            aria-label="New field type"
          >
            {FIELD_TYPES.map((t) => (
              <option key={t.value} value={t.value}>{t.label}</option>
            ))}
          </select>
        </div>
        <button
          type="button"
          className="field-list__button field-list__button--add"
          onClick={handleAddField}
          disabled={!newFieldName.trim()}
        >
          <i className="feather-plus" aria-hidden="true" />
          <span>Add Field</span>
        </button>
      </div>

      <div className="field-list__list">
        {!fields || fields.length === 0 ? (
          <div className="field-list__empty">
            <i className="feather-inbox" aria-hidden="true" />
            <p>No fields defined yet</p>
            <span>Add fields to create dynamic template variables</span>
          </div>
        ) : (
          fields.map((field) => (
            <div key={field.id} className="field-list__item">
              <div className="field-list__item-info">
                <div className="field-list__item-name">
                  <code>{`{{${field.name}}}`}</code>
                </div>
                <div className="field-list__item-type">
                  <span className={`field-list__type-badge field-list__type-badge--${field.type}`}>
                    {field.type}
                  </span>
                </div>
              </div>

              <div className="field-list__item-actions">
                <button
                  type="button"
                  className="field-list__action-btn field-list__action-btn--insert"
                  onClick={() => handleInsertField(field)}
                  title="Insert into canvas"
                  aria-label={`Insert ${field.name} into canvas`}
                >
                  <i className="feather-plus-circle" aria-hidden="true" />
                </button>
                <button
                  type="button"
                  className="field-list__action-btn field-list__action-btn--delete"
                  onClick={() => removeField(field.id)}
                  title="Remove field"
                  aria-label={`Remove field ${field.name}`}
                >
                  <i className="feather-x" aria-hidden="true" />
                </button>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}

export default memo(FieldList);
