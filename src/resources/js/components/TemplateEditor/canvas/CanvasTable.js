/**
 * CanvasTable.js — Konva.Group orchestrator for tables.
 *
 * Public API (preserved): rows, cols, cells, colWidths, rowHeights,
 * setCellText, setCellStyle, addRow/Column, removeRow/Column, mergeCells,
 * setHeader/getHeader/setHeaders/getHeaders, setDynamicSource/getDynamicSource,
 * populateFromData, toTableJSON, fromJSON.
 *
 * Internal split:
 *   - CanvasTableData         — pure data model + structural mutations
 *   - CanvasTableRenderer     — turns the data model into Konva nodes
 *   - CanvasTableResizeHandles — column/row drag-to-resize handles
 *   - CanvasTableEditor       — inline <textarea> overlay for editing
 */

import Konva from 'konva';
import { CanvasTableData } from './table/CanvasTableData';
import { CanvasTableRenderer } from './table/CanvasTableRenderer';
import { CanvasTableResizeHandles } from './table/CanvasTableResizeHandles';
import { CanvasTableEditor } from './table/CanvasTableEditor';

// Use a namespace so the table can reliably tear down its own listeners
// without affecting anything an outer caller has attached to the group.
const EVT_NS = '.CanvasTable';

export class CanvasTable extends Konva.Group {
  constructor(config = {}) {
    super({
      id: config.id || `tbl_${Date.now()}`,
      x: config.x || 0,
      y: config.y || 0,
      draggable: config.draggable !== false,
      name: 'CanvasTable',
    });

    this._tableData = new CanvasTableData(config);
    this._selectedCell = null;
    this._editingCell = null;       // { row, col, controller }
    this._isDestroyed = false;

    this._render();
    this._setupEvents();
  }

  // ─── Data model getters (preserve existing public API) ──

  get rows() { return this._tableData.rows; }
  get cols() { return this._tableData.cols; }
  get colWidths() { return this._tableData.colWidths; }
  get rowHeights() { return this._tableData.rowHeights; }
  get cells() { return this._tableData.cells; }
  get totalWidth() { return this._tableData.totalWidth; }
  get totalHeight() { return this._tableData.totalHeight; }

  // ─── Render ─────────────────────────────────────────────

  _render() {
    if (this._isDestroyed) return;
    // destroyChildren only removes child nodes; listeners on `this` survive.
    this.destroyChildren();
    CanvasTableRenderer.renderCells(this, this._tableData, this._selectedCell);
    CanvasTableResizeHandles.attach(this, this._tableData);
    this.getLayer()?.batchDraw();
  }

  _setupEvents() {
    // Use namespaced listeners so destroy() can clean them up cleanly.
    this.on(`click${EVT_NS} tap${EVT_NS}`, (e) => {
      // Don't reselect when interacting with a resize handle.
      if (e?.target?.name?.() === 'col-resize-handle' ||
          e?.target?.name?.() === 'row-resize-handle') {
        return;
      }
      const pos = this.getRelativePointerPosition();
      if (!pos) return;
      const cell = this._tableData.getCellAtPoint(pos.x, pos.y);
      if (cell) {
        this._selectedCell = cell;
        this._render();
        this.fire('cell:select', { ...cell, tableId: this.id() });
      }
    });

    this.on(`dblclick${EVT_NS} dbltap${EVT_NS}`, (e) => {
      if (e?.target?.name?.() === 'col-resize-handle' ||
          e?.target?.name?.() === 'row-resize-handle') {
        return;
      }
      const pos = this.getRelativePointerPosition();
      if (!pos) return;
      const cell = this._tableData.getCellAtPoint(pos.x, pos.y);
      if (cell) this._startCellEdit(cell.row, cell.col);
    });
  }

  /** Commit any open inline editor immediately and drop the controller. */
  _finalizeActiveEdit() {
    if (this._editingCell?.controller?.commit) {
      try { this._editingCell.controller.commit(); } catch { /* noop */ }
    }
    this._editingCell = null;
  }

  _startCellEdit(row, col) {
    const stage = this.getStage();
    if (!stage || this._isDestroyed) return;

    // Always close any prior editor before opening a new one.
    this._finalizeActiveEdit();

    const controller = CanvasTableEditor.start({
      stage,
      group: this,
      data: this._tableData,
      row,
      col,
      onCommit: (r, c, text) => {
        if (this._isDestroyed) return;
        this._tableData.setCellText(r, c, text);
        this._render();
        this.fire('cell:edited', { row: r, col: c, text, tableId: this.id() });
      },
      onAdvance: (_direction, nextRow, nextCol) => {
        this._editingCell = null;
        // Open the next editor on the next animation frame so the previous
        // textarea has fully torn down before we measure positions.
        requestAnimationFrame(() => {
          if (this._isDestroyed) return;
          this._startCellEdit(nextRow, nextCol);
        });
      },
    });

    this._editingCell = { row, col, controller };
  }

  // ─── Cell mutations ─────────────────────────────────────

  setCellText(row, col, text) {
    this._tableData.setCellText(row, col, text);
    this._render();
  }

  setCellStyle(row, col, style) {
    this._tableData.setCellStyle(row, col, style);
    this._render();
  }

  // ─── Header convenience helpers ─────────────────────────

  getHeader(col) {
    return this._tableData.getCell(0, col).text || '';
  }

  setHeader(col, text) {
    this.setCellText(0, col, text);
  }

  getHeaders() {
    const out = [];
    for (let c = 0; c < this._tableData.cols; c++) out.push(this.getHeader(c));
    return out;
  }

  setHeaders(headers) {
    if (!Array.isArray(headers)) return;
    headers.forEach((text, col) => {
      if (col < this._tableData.cols) this.setCellText(0, col, text);
    });
  }

  // ─── Structural mutations ───────────────────────────────

  addRow(afterIndex) {
    this._finalizeActiveEdit();
    this._tableData.addRow(afterIndex);
    // Adjust selection so it doesn't dangle on a moved cell.
    if (this._selectedCell && afterIndex !== undefined && this._selectedCell.row > afterIndex) {
      this._selectedCell = { ...this._selectedCell, row: this._selectedCell.row + 1 };
    }
    this._render();
  }

  removeRow(rowIndex) {
    this._finalizeActiveEdit();
    if (this._selectedCell?.row === rowIndex) this._selectedCell = null;
    else if (this._selectedCell && this._selectedCell.row > rowIndex) {
      this._selectedCell = { ...this._selectedCell, row: this._selectedCell.row - 1 };
    }
    this._tableData.removeRow(rowIndex);
    this._render();
  }

  addColumn(afterIndex) {
    this._finalizeActiveEdit();
    this._tableData.addColumn(afterIndex);
    if (this._selectedCell && afterIndex !== undefined && this._selectedCell.col > afterIndex) {
      this._selectedCell = { ...this._selectedCell, col: this._selectedCell.col + 1 };
    }
    this._render();
  }

  removeColumn(colIndex) {
    this._finalizeActiveEdit();
    if (this._selectedCell?.col === colIndex) this._selectedCell = null;
    else if (this._selectedCell && this._selectedCell.col > colIndex) {
      this._selectedCell = { ...this._selectedCell, col: this._selectedCell.col - 1 };
    }
    this._tableData.removeColumn(colIndex);
    this._render();
  }

  mergeCells(startRow, startCol, endRow, endCol) {
    this._finalizeActiveEdit();
    this._tableData.mergeCells(startRow, startCol, endRow, endCol);
    this._render();
  }

  unmergeCell(row, col) {
    this._finalizeActiveEdit();
    this._tableData.unmergeCell?.(row, col);
    this._render();
  }

  // ─── Dynamic data ───────────────────────────────────────

  setDynamicSource(source) {
    this._tableData.setDynamicSource(source);
  }

  getDynamicSource() {
    return this._tableData.dynamicSource;
  }

  populateFromData(dataRows) {
    this._finalizeActiveEdit();
    this._tableData.populateFromData(dataRows);
    this._render();
  }

  // ─── Serialization ──────────────────────────────────────

  toTableJSON() {
    return {
      id: this.id(),
      className: 'CanvasTable',
      x: this.x(),
      y: this.y(),
      draggable: this.draggable(),
      visible: this.visible(),
      // Round-trip rotation/scale so absolute positions survive a save/load.
      rotation: this.rotation(),
      scaleX: this.scaleX(),
      scaleY: this.scaleY(),
      ...this._tableData.toJSON(),
    };
  }

  static fromJSON(data) {
    const t = new CanvasTable(data);
    if (data?.rotation !== undefined) t.rotation(data.rotation);
    if (data?.scaleX !== undefined) t.scaleX(data.scaleX);
    if (data?.scaleY !== undefined) t.scaleY(data.scaleY);
    if (data?.visible === false) t.visible(false);
    return t;
  }

  // Make sure we tear down the inline editor and our namespaced listeners
  // when the group is destroyed (parent destroy → children destroy chain).
  destroy() {
    this._isDestroyed = true;
    this._finalizeActiveEdit();
    try { this.off(EVT_NS); } catch { /* noop */ }
    return super.destroy();
  }
}

export default CanvasTable;
