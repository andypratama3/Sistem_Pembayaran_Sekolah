/**
 * LayersPanel.jsx — Layer management with z-order controls
 * IMPROVED: Added move up/down buttons, better visual hierarchy, refresh on selection change
 */

import React, { useState, useEffect, useCallback } from 'react';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { LayerManager } from '../canvas/LayerManager';
import { CanvasSerializer } from '../canvas/CanvasSerializer';
import { useCanvasSync } from '../hooks/useCanvasSync';
import './LayersPanel.css';

function LayersPanel() {
  const [layerTree, setLayerTree] = useState([]);
  const [expandedGroups, setExpandedGroups] = useState(new Set());

  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const selectedObject = useTemplateStore((s) => s.selectedObject);

  const stageData = stageRegistry.get(activePageIndex);
  const contentLayer = stageData?.contentLayer;
  const transformer = stageData?.transformer;
  const syncStore = useCanvasSync(activePageIndex);

  // Refresh layer tree
  const refreshTree = useCallback(() => {
    if (!contentLayer) {
      setLayerTree([]);
      return;
    }
    setLayerTree(LayerManager.getLayerTree(contentLayer));
  }, [contentLayer]);

  // Setup listeners & refresh on content change
  useEffect(() => {
    if (!contentLayer) return;

    const events = ['add', 'remove', 'dragend', 'transformend'];
    let timeoutId = null;
    const handler = () => {
      if (timeoutId) clearTimeout(timeoutId);
      timeoutId = setTimeout(refreshTree, 50);
    };
    events.forEach((evt) => contentLayer.on(evt, handler));
    refreshTree();

    return () => {
      if (timeoutId) clearTimeout(timeoutId);
      events.forEach((evt) => contentLayer.off(evt, handler));
    };
  }, [contentLayer, refreshTree]);

  // Also refresh when selection changes
  useEffect(() => {
    refreshTree();
  }, [selectedObject, refreshTree]);

  const handleToggleVisibility = useCallback((nodeId) => {
    const node = contentLayer?.findOne(`#${nodeId}`);
    if (node) {
      LayerManager.toggleVisibility(node);
      refreshTree();
      syncStore(contentLayer);
    }
  }, [contentLayer, refreshTree, syncStore]);

  const handleToggleLock = useCallback((nodeId) => {
    const node = contentLayer?.findOne(`#${nodeId}`);
    if (node && transformer) {
      LayerManager.toggleLock(node, transformer);
      refreshTree();
      syncStore(contentLayer);
    }
  }, [contentLayer, transformer, refreshTree, syncStore]);

  const handleSelectLayer = useCallback((nodeId) => {
    const node = contentLayer?.findOne(`#${nodeId}`);
    if (node && transformer) {
      transformer.nodes([node]);
      useTemplateStore.getState().setSelectedObject(node);
      contentLayer?.batchDraw();
    }
  }, [contentLayer, transformer]);

  const handleMoveUp = useCallback((nodeId) => {
    const node = contentLayer?.findOne(`#${nodeId}`);
    if (node) {
      LayerManager.moveForward(node);
      refreshTree();
      syncStore(contentLayer);
    }
  }, [contentLayer, refreshTree, syncStore]);

  const handleMoveDown = useCallback((nodeId) => {
    const node = contentLayer?.findOne(`#${nodeId}`);
    if (node) {
      LayerManager.moveBackward(node);
      refreshTree();
      syncStore(contentLayer);
    }
  }, [contentLayer, refreshTree, syncStore]);

  const handleDelete = useCallback((nodeId) => {
    const node = contentLayer?.findOne(`#${nodeId}`);
    if (node) {
      if (transformer) {
        transformer.nodes(transformer.nodes().filter((n) => n !== node));
      }
      LayerManager.deleteNode(node);
      refreshTree();
      syncStore(contentLayer);
      useTemplateStore.getState().setSelectedObject(null);
    }
  }, [contentLayer, transformer, refreshTree, syncStore]);

  // Check if a node is currently selected
  const isNodeSelected = useCallback((nodeId) => {
    return selectedObject?.id?.() === nodeId;
  }, [selectedObject]);

  const renderLayerItem = useCallback((layer, depth = 0) => (
    <div key={layer.id} className="layers-panel__item">
      <div
        className={`layers-panel__item-content ${isNodeSelected(layer.id) ? 'layers-panel__item-content--selected' : ''}`}
        style={{ paddingLeft: `${12 + depth * 14}px` }}
      >
        {/* Expand button for groups */}
        {layer.children && layer.children.length > 0 ? (
          <button
            className="layers-panel__expand-btn"
            onClick={() => {
              setExpandedGroups((prev) => {
                const next = new Set(prev);
                if (next.has(layer.id)) next.delete(layer.id);
                else next.add(layer.id);
                return next;
              });
            }}
          >
            <i className={`feather-chevron-${expandedGroups.has(layer.id) ? 'down' : 'right'}`} />
          </button>
        ) : (
          <span className="layers-panel__spacer" />
        )}

        {/* Layer name */}
        <button
          className="layers-panel__layer-name"
          onClick={() => handleSelectLayer(layer.id)}
          title={`Select: ${layer.name}`}
        >
          <span className="layers-panel__layer-icon">
            {layer.className === 'Text' && <i className="feather-type" />}
            {layer.className === 'Rect' && <i className="feather-square" />}
            {layer.className === 'Circle' && <i className="feather-circle" />}
            {layer.className === 'Group' && <i className="feather-folder" />}
            {layer.className === 'Line' && <i className="feather-minus" />}
            {layer.className === 'Image' && <i className="feather-image" />}
            {layer.className === 'CanvasTable' && <i className="feather-grid" />}
            {!['Text', 'Rect', 'Circle', 'Group', 'Line', 'Image', 'CanvasTable'].includes(layer.className) && (
              <i className="feather-box" />
            )}
          </span>
          <span className="layers-panel__layer-text">{layer.name}</span>
        </button>

        {/* Controls */}
        <div className="layers-panel__controls">
          <button
            className={`layers-panel__control-btn ${!layer.visible ? 'layers-panel__control-btn--off' : ''}`}
            onClick={() => handleToggleVisibility(layer.id)}
            title={layer.visible ? 'Hide' : 'Show'}
          >
            <i className={`feather-${layer.visible ? 'eye' : 'eye-off'}`} />
          </button>
          <button
            className={`layers-panel__control-btn ${layer.locked ? 'layers-panel__control-btn--active' : ''}`}
            onClick={() => handleToggleLock(layer.id)}
            title={layer.locked ? 'Unlock' : 'Lock'}
          >
            <i className={`feather-${layer.locked ? 'lock' : 'unlock'}`} />
          </button>
        </div>
      </div>

      {/* Children */}
      {expandedGroups.has(layer.id) && layer.children?.length > 0 && (
        <div className="layers-panel__children">
          {layer.children.map((child) => renderLayerItem(child, depth + 1))}
        </div>
      )}
    </div>
  ), [expandedGroups, isNodeSelected, handleSelectLayer, handleToggleVisibility, handleToggleLock]);

  return (
    <div className="layers-panel">
      <div className="layers-panel__header">
        <h3 className="layers-panel__title">Layers</h3>
        <span className="layers-panel__count">{layerTree.length}</span>
      </div>

      {/* Quick actions for selected */}
      {selectedObject && (
        <div className="layers-panel__actions">
          <button className="layers-panel__action" onClick={() => handleMoveUp(selectedObject.id())} title="Move up">
            <i className="feather-arrow-up" />
          </button>
          <button className="layers-panel__action" onClick={() => handleMoveDown(selectedObject.id())} title="Move down">
            <i className="feather-arrow-down" />
          </button>
          <button className="layers-panel__action layers-panel__action--danger" onClick={() => handleDelete(selectedObject.id())} title="Delete">
            <i className="feather-trash-2" />
          </button>
        </div>
      )}

      <div className="layers-panel__list">
        {!layerTree || layerTree.length === 0 ? (
          <div className="layers-panel__empty">
            <i className="feather-layers" />
            <span>No elements on canvas</span>
          </div>
        ) : (
          layerTree.map((layer) => renderLayerItem(layer))
        )}
      </div>
    </div>
  );
}

export default LayersPanel;
