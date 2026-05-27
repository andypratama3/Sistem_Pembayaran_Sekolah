/**
 * PropertiesPanel.jsx — Right-sidebar properties orchestrator.
 *
 * Decomposed into focused sub-panels living in `./properties/`. This file is
 * just the controller: it owns property/cell state and dispatches to the
 * sub-panels.
 */

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { useCanvasSync } from '../hooks/useCanvasSync';

import GeneralProps from './properties/GeneralProps';
import TransformProps from './properties/TransformProps';
import AppearanceProps from './properties/AppearanceProps';
import TableStructureProps from './properties/TableStructureProps';
import TableCellEditor from './properties/TableCellEditor';
import TableMergeTool from './properties/TableMergeTool';
import QrCodeProps from './properties/QrCodeProps';
import TextProps from './properties/TextProps';

import './PropertiesPanel.css';

const PREDEFINED_VARIABLES = [
  { id: 'date', name: 'Date' },
  { id: 'time', name: 'Time' },
  { id: 'page_number', name: 'Page Number' },
  { id: 'total_pages', name: 'Total Pages' },
  { id: 'user_name', name: 'User Name' },
  { id: 'company_name', name: 'Company Name' },
];

// Pull validateFormula out of the component so it isn't recreated each render
// and doesn't need to be tracked in dependency arrays.
function validateFormula(formula) {
  if (!formula) return true;
  if (typeof formula !== 'string') return false;
  // Bracket balance check
  const openBrackets = (formula.match(/\{/g) || []).length;
  const closeBrackets = (formula.match(/\}/g) || []).length;
  if (openBrackets !== closeBrackets) return false;
  // Block dangerous keywords
  if (/\b(eval|exec|system|shell|Function|require|import)\b/i.test(formula)) return false;
  return true;
}

// Safe finite-number coercion. Returns undefined when not parseable so callers
// can skip the Konva mutation rather than write NaN.
function toFiniteNumber(value) {
  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : undefined;
  }
  if (typeof value === 'string') {
    // Allow intermediate typing states: empty, minus sign, decimal point
    if (value === '' || value === '-' || value === '.' || value === '-.') return undefined;
    const n = parseFloat(value);
    return Number.isFinite(n) ? n : undefined;
  }
  return undefined;
}

function PropertiesPanel() {
  const [properties, setProperties] = useState({});
  const [selectedCell, setSelectedCell] = useState(null);
  const [mergeRange, setMergeRange] = useState({ startRow: 0, startCol: 0, endRow: 1, endCol: 1 });

  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const selectedObjectId = useTemplateStore((s) => s.selectedObjectId);
  const getSelectedNodeById = useTemplateStore((s) => s.getSelectedNodeById);
  const fields = useTemplateStore((s) => s.fields);

  const syncStore = useCanvasSync(activePageIndex);
  const stageData = stageRegistry.get(activePageIndex);
  const contentLayer = stageData?.contentLayer;
  const transformer = stageData?.transformer;
  const uiLayer = stageData?.uiLayer;

  // ISSUE #3 FIX: Resolve selected node from ID + contentLayer
  const selectedObject = contentLayer ? getSelectedNodeById(contentLayer) : null;

  const isTable = useMemo(
    () =>
      Boolean(
        selectedObject && (selectedObject.className === 'CanvasTable' || selectedObject.name?.() === 'CanvasTable')
      ),
    [selectedObject]
  );

  const isQrCode = useMemo(() => {
    if (!selectedObject) return false;
    if (selectedObject.getAttr?.('isQrCode')) return true;
    const id = selectedObject.id?.();
    return typeof id === 'string' && id.startsWith('qrcode_');
  }, [selectedObject]);

  const isText = useMemo(
    () =>
      Boolean(
        selectedObject &&
          (selectedObject.className === 'Text' ||
            selectedObject.name?.() === 'Text')
      ),
    [selectedObject]
  );

  const allVariables = useMemo(() => {
    const customVars = (fields || []).map((f) => ({ id: f.id, name: f.name, isCustom: true }));
    return [...PREDEFINED_VARIABLES, ...customVars];
  }, [fields]);

  // Initialize cell selection on table activation.
  useEffect(() => {
    if (isTable) {
      setSelectedCell({ row: 0, col: 0 });
      if (selectedObject) {
        selectedObject._selectedCell = { row: 0, col: 0 };
        contentLayer?.batchDraw();
      }
    } else {
      setSelectedCell(null);
    }
    // contentLayer reference can change across pages — include it intentionally
  }, [selectedObject, isTable, contentLayer]);

  // Listen for cell-selection events emitted by the CanvasTable instance.
  useEffect(() => {
    if (!isTable || !selectedObject || typeof selectedObject.on !== 'function') return undefined;
    const handler = (e) => {
      if (e && Number.isFinite(e.row) && Number.isFinite(e.col)) {
        setSelectedCell({ row: e.row, col: e.col });
      }
    };
    selectedObject.on('cell:select', handler);
    return () => {
      try {
        selectedObject.off?.('cell:select', handler);
      } catch {
        // Node may have been destroyed during teardown — safe to ignore
      }
    };
  }, [selectedObject, isTable]);

  // Reflect React state changes back to the Konva instance.
  useEffect(() => {
    if (!isTable || !selectedObject || !selectedCell) return;
    const current = selectedObject._selectedCell;
    if (!current || current.row !== selectedCell.row || current.col !== selectedCell.col) {
      selectedObject._selectedCell = selectedCell;
      contentLayer?.batchDraw();
    }
  }, [selectedCell, selectedObject, isTable, contentLayer]);

  // Snapshot properties from the selected node.
  useEffect(() => {
    if (!selectedObject) {
      setProperties({});
      return;
    }
    // Defensive optional-chaining — Konva nodes always have these getters,
    // but custom shapes (CanvasTable) may not implement every one.
    setProperties({
      id: selectedObject.id?.() || '',
      name: selectedObject.getAttr?.('name') || '',
      x: selectedObject.x?.() ?? 0,
      y: selectedObject.y?.() ?? 0,
      width: selectedObject.width?.() ?? 0,
      height: selectedObject.height?.() ?? 0,
      rotation: selectedObject.rotation?.() ?? 0,
      opacity: selectedObject.opacity?.() ?? 1,
      fill: selectedObject.fill?.() || '#ffffff',
      stroke: selectedObject.stroke?.() || '#000000',
      strokeWidth: selectedObject.strokeWidth?.() ?? 1,
      qrContent: selectedObject.getAttr?.('qrContent') || '',
      text: selectedObject.text?.() || '',
      fontSize: selectedObject.fontSize?.() ?? 14,
      fontFamily: selectedObject.fontFamily?.() || 'Arial',
      align: selectedObject.align?.() || 'left',
    });
  }, [selectedObject]);

  // Mapping of property keys → mutators on the Konva node.
  const handlePropertyChange = useCallback(
    (key, value) => {
      if (!selectedObject) return;

      // For numeric fields, refuse to commit non-finite values to either
      // local state or the Konva node — this prevents NaN propagation.
      const numericKeys = new Set([
        'x', 'y', 'width', 'height', 'rotation', 'opacity', 'strokeWidth', 'fontSize',
      ]);
      let nextValue = value;
      if (numericKeys.has(key)) {
        // Allow intermediate typing states (e.g., '-', '.') in local state
        // but don't commit to Konva until we have a valid number
        if (typeof value === 'string' && (value === '' || value === '-' || value === '.' || value === '-.')) {
          setProperties((prev) => ({ ...prev, [key]: value }));
          return;
        }
        const n = toFiniteNumber(value);
        if (n === undefined) return;
        nextValue = n;
      }

      setProperties((prev) => ({ ...prev, [key]: nextValue }));

      try {
        switch (key) {
          case 'name':
            selectedObject.setAttr('name', nextValue);
            break;
          case 'x':
          case 'y':
            if (typeof selectedObject[key] === 'function') {
              selectedObject[key](nextValue);
            }
            break;
          case 'width':
            selectedObject.width(nextValue);
            if (selectedObject.fillPatternImage && selectedObject.fillPatternImage()) {
              selectedObject.fillPatternScaleX(nextValue / 100);
            }
            break;
          case 'height':
            selectedObject.height(nextValue);
            if (selectedObject.fillPatternImage && selectedObject.fillPatternImage()) {
              selectedObject.fillPatternScaleY(nextValue / 100);
            }
            break;
          case 'rotation':
            selectedObject.rotation(nextValue);
            break;
          case 'opacity':
            selectedObject.opacity(Math.max(0, Math.min(1, nextValue)));
            break;
          case 'fill':
            selectedObject.fill(nextValue);
            break;
          case 'stroke':
            selectedObject.stroke(nextValue);
            break;
          case 'strokeWidth':
            selectedObject.strokeWidth(Math.max(0, nextValue));
            break;
          case 'qrContent':
            selectedObject.setAttr('qrContent', nextValue);
            break;
          case 'text':
            if (typeof selectedObject.text === 'function') {
              selectedObject.text(nextValue);
            }
            break;
          case 'fontSize':
            if (typeof selectedObject.fontSize === 'function') {
              selectedObject.fontSize(nextValue);
            }
            break;
          case 'fontFamily':
            if (typeof selectedObject.fontFamily === 'function') {
              selectedObject.fontFamily(nextValue);
            }
            break;
          case 'align':
            if (typeof selectedObject.align === 'function') {
              selectedObject.align(nextValue);
            }
            break;
          case 'formula':
            if (!validateFormula(nextValue)) {
              console.error('Invalid formula');
              return;
            }
            selectedObject.setAttr('formula', nextValue);
            break;
          default:
            // Unknown property — revert local state to avoid desync
            setProperties((prev) => {
              const copy = { ...prev };
              delete copy[key];
              return copy;
            });
            return;
        }

        // Force transformer + redraw to keep handles synchronized
        if (transformer && typeof transformer.forceUpdate === 'function') {
          transformer.forceUpdate();
        }
        contentLayer?.batchDraw();
        uiLayer?.batchDraw();
        syncStore(contentLayer);
      } catch (error) {
        console.error('Error updating property:', error);
      }
    },
    [selectedObject, contentLayer, transformer, uiLayer, syncStore]
  );

  // Table cell mutators.
  const handleCellTextChange = useCallback(
    (text) => {
      if (!selectedObject || !selectedCell) return;
      if (typeof selectedObject.setCellText !== 'function') return;
      selectedObject.setCellText(selectedCell.row, selectedCell.col, text);
      contentLayer?.batchDraw();
      syncStore(contentLayer);
    },
    [selectedObject, selectedCell, contentLayer, syncStore]
  );

  const handleCellStyleChange = useCallback(
    (styleKey, value) => {
      if (!selectedObject || !selectedCell) return;
      if (typeof selectedObject.setCellStyle !== 'function') return;
      selectedObject.setCellStyle(selectedCell.row, selectedCell.col, { [styleKey]: value });
      contentLayer?.batchDraw();
      syncStore(contentLayer);
    },
    [selectedObject, selectedCell, contentLayer, syncStore]
  );

  // Table structure mutators.
  const handleAddRow = useCallback(
    (afterIndex) => {
      if (!selectedObject || typeof selectedObject.addRow !== 'function') return;
      selectedObject.addRow(afterIndex);
      contentLayer?.batchDraw();
      syncStore(contentLayer);
    },
    [selectedObject, contentLayer, syncStore]
  );

  const handleRemoveRow = useCallback(
    (rowIndex) => {
      if (!selectedObject || typeof selectedObject.removeRow !== 'function') return;
      selectedObject.removeRow(rowIndex);
      if (selectedCell && selectedCell.row >= selectedObject.rows) {
        setSelectedCell({ row: Math.max(0, selectedObject.rows - 1), col: selectedCell.col });
      }
      contentLayer?.batchDraw();
      syncStore(contentLayer);
    },
    [selectedObject, selectedCell, contentLayer, syncStore]
  );

  const handleAddCol = useCallback(
    (afterIndex) => {
      if (!selectedObject || typeof selectedObject.addColumn !== 'function') return;
      selectedObject.addColumn(afterIndex);
      contentLayer?.batchDraw();
      syncStore(contentLayer);
    },
    [selectedObject, contentLayer, syncStore]
  );

  const handleRemoveCol = useCallback(
    (colIndex) => {
      if (!selectedObject || typeof selectedObject.removeColumn !== 'function') return;
      selectedObject.removeColumn(colIndex);
      if (selectedCell && selectedCell.col >= selectedObject.cols) {
        setSelectedCell({ row: selectedCell.row, col: Math.max(0, selectedObject.cols - 1) });
      }
      contentLayer?.batchDraw();
      syncStore(contentLayer);
    },
    [selectedObject, selectedCell, contentLayer, syncStore]
  );

  const handleMergeCells = useCallback(() => {
    if (!selectedObject) return;

    if (typeof selectedObject.mergeCells !== 'function') {
      console.error('Selected object is not a table or does not support merge cells');
      return;
    }

    const startRow = toFiniteNumber(mergeRange.startRow);
    const startCol = toFiniteNumber(mergeRange.startCol);
    const endRow = toFiniteNumber(mergeRange.endRow);
    const endCol = toFiniteNumber(mergeRange.endCol);

    if (
      startRow === undefined ||
      startCol === undefined ||
      endRow === undefined ||
      endCol === undefined
    ) {
      console.error('Invalid merge range: non-numeric values');
      return;
    }

    if (startRow > endRow || startCol > endCol) {
      console.error('Invalid merge range: start must be <= end');
      return;
    }

    if (startRow === endRow && startCol === endCol) {
      console.warn('Merge range covers a single cell — nothing to merge');
      return;
    }

    try {
      selectedObject.mergeCells(startRow, startCol, endRow, endCol);
      contentLayer?.batchDraw();
      syncStore(contentLayer);
    } catch (error) {
      console.error('Error merging cells:', error);
    }
  }, [selectedObject, mergeRange, contentLayer, syncStore]);

  const insertVariableIntoCell = useCallback(
    (varId) => {
      if (!selectedObject || !selectedCell || !varId) return;
      const cellKey = `${selectedCell.row},${selectedCell.col}`;
      const cells = selectedObject.cells || {};
      const cellData = cells[cellKey] || {};
      const currentText = cellData.text || '';
      handleCellTextChange(currentText + `{{${varId}}}`);
    },
    [selectedObject, selectedCell, handleCellTextChange]
  );

  if (!selectedObject) {
    return (
      <div className="properties-panel">
        <div className="properties-panel__empty">
          <div className="properties-panel__empty-icon" aria-hidden="true">🎛️</div>
          Select an object to edit properties
        </div>
      </div>
    );
  }

  const cellKey = selectedCell ? `${selectedCell.row},${selectedCell.col}` : null;
  const tableCells = (isTable && selectedObject.cells) || {};
  const currentCellData = (cellKey && tableCells[cellKey]) || {};
  const cellStyle = currentCellData.style || {};

  return (
    <div className="properties-panel" role="region" aria-label="Object properties">
      <GeneralProps properties={properties} onChange={handlePropertyChange} />
      <TransformProps properties={properties} onChange={handlePropertyChange} />

      {isTable ? (
        <>
          <TableStructureProps
            table={selectedObject}
            selectedCell={selectedCell}
            onSelectCell={setSelectedCell}
            onAddRow={handleAddRow}
            onRemoveRow={handleRemoveRow}
            onAddCol={handleAddCol}
            onRemoveCol={handleRemoveCol}
            contentLayer={contentLayer}
          />
          <TableCellEditor
            selectedCell={selectedCell}
            cellData={currentCellData}
            cellStyle={cellStyle}
            variables={allVariables}
            onTextChange={handleCellTextChange}
            onStyleChange={handleCellStyleChange}
            onInsertVariable={insertVariableIntoCell}
          />
          <TableMergeTool
            table={selectedObject}
            mergeRange={mergeRange}
            onChangeMergeRange={setMergeRange}
            onMergeCells={handleMergeCells}
          />
        </>
      ) : (
        <>
          <AppearanceProps properties={properties} onChange={handlePropertyChange} />
          {isText && (
            <TextProps
              properties={properties}
              onChange={handlePropertyChange}
              variables={allVariables}
            />
          )}
          {isQrCode && (
            <QrCodeProps
              properties={properties}
              onChange={handlePropertyChange}
              variables={allVariables}
            />
          )}
        </>
      )}
    </div>
  );
}

export default PropertiesPanel;
