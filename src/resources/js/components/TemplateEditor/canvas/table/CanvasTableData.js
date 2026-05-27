/**
 * CanvasTableData.js — Pure data model for CanvasTable.
 *
 * Holds rows/cols/widths/heights/cells/style with no Konva dependency.
 * All structural mutations (add/remove row, merge cells, etc.) live here so
 * they can be unit-tested without booting a Konva stage.
 */

const DEFAULT_COL_WIDTH = 120;
const DEFAULT_ROW_HEIGHT = 40;

export const DEFAULT_TABLE_STYLE = Object.freeze({
  borderColor: '#cbd5e1',
  borderWidth: 1,
  headerBg: '#ffffff',
  cellBg: '#ffffff',
  fontSize: 13,
  fontFamily: 'Arial',
  cellPadding: 8,
});

export class CanvasTableData {
  constructor(config = {}) {
    const rows = config.rows ?? 3;
    const cols = config.cols ?? 3;

    this.rows = rows;
    this.cols = cols;
    this.colWidths = config.colWidths ? [...config.colWidths] : Array(cols).fill(DEFAULT_COL_WIDTH);
    this.rowHeights = config.rowHeights ? [...config.rowHeights] : Array(rows).fill(DEFAULT_ROW_HEIGHT);
    this.cells = config.cells ? { ...config.cells } : {};
    this.style = { ...DEFAULT_TABLE_STYLE, ...(config.style || {}) };
    this.dynamicSource = config.dynamicSource ?? null;

    // Backfill missing widths/heights so config-supplied arrays of wrong length
    // don't leave us with NaN positions.
    while (this.colWidths.length < this.cols) this.colWidths.push(DEFAULT_COL_WIDTH);
    while (this.rowHeights.length < this.rows) this.rowHeights.push(DEFAULT_ROW_HEIGHT);
    this.colWidths.length = this.cols;
    this.rowHeights.length = this.rows;
  }

  // ─── Coordinate helpers ────────────────────────────────

  static cellKey(row, col) {
    return `${row},${col}`;
  }

  static parseKey(key) {
    const [r, c] = key.split(',').map(Number);
    return { row: r, col: c };
  }

  getCellX(col) {
    return this.colWidths.slice(0, col).reduce((a, b) => a + b, 0);
  }

  getCellY(row) {
    return this.rowHeights.slice(0, row).reduce((a, b) => a + b, 0);
  }

  get totalWidth() {
    return this.colWidths.reduce((a, b) => a + b, 0);
  }

  get totalHeight() {
    return this.rowHeights.reduce((a, b) => a + b, 0);
  }

  /**
   * Returns {row, col} for a local-coordinate point, or null if outside.
   * If the point lies inside a merged region, returns the *anchor* cell.
   */
  getCellAtPoint(x, y) {
    if (x < 0 || y < 0 || x > this.totalWidth || y > this.totalHeight) return null;

    let foundCol = -1;
    let cumX = 0;
    for (let col = 0; col < this.cols; col++) {
      cumX += this.colWidths[col];
      if (x <= cumX) { foundCol = col; break; }
    }
    if (foundCol < 0) return null;

    let foundRow = -1;
    let cumY = 0;
    for (let row = 0; row < this.rows; row++) {
      cumY += this.rowHeights[row];
      if (y <= cumY) { foundRow = row; break; }
    }
    if (foundRow < 0) return null;

    // If the hit cell was merged into another, redirect to the anchor.
    const cell = this.getCell(foundRow, foundCol);
    if (cell && cell._mergedInto) {
      const anchor = CanvasTableData.parseKey(cell._mergedInto);
      return { row: anchor.row, col: anchor.col };
    }
    return { row: foundRow, col: foundCol };
  }

  // ─── Cell mutations ────────────────────────────────────

  setCellText(row, col, text) {
    const key = CanvasTableData.cellKey(row, col);
    this.cells[key] = { ...this.cells[key], text };
  }

  setCellStyle(row, col, style) {
    const key = CanvasTableData.cellKey(row, col);
    this.cells[key] = {
      ...this.cells[key],
      style: { ...(this.cells[key]?.style || {}), ...style },
    };
  }

  getCell(row, col) {
    return this.cells[CanvasTableData.cellKey(row, col)] || {};
  }

  // ─── Structural mutations ──────────────────────────────

  addRow(afterIndex) {
    const insertAt = afterIndex !== undefined ? afterIndex + 1 : this.rows;
    this.rows += 1;
    this.rowHeights.splice(insertAt, 0, DEFAULT_ROW_HEIGHT);
    this.cells = this._shiftCells((r, c) => (r >= insertAt ? [r + 1, c] : [r, c]));
    // Also rewrite any _mergedInto pointers so they still reference live anchors.
    this._remapMergeAnchors((r, c) => (r >= insertAt ? [r + 1, c] : [r, c]));
  }

  removeRow(rowIndex) {
    if (this.rows <= 1) return;

    // Drop any merge groups that lose their anchor or any of their member cells.
    this._unmergeRowsTouching(rowIndex, rowIndex);

    this.rows -= 1;
    this.rowHeights.splice(rowIndex, 1);
    this.cells = this._shiftCells((r, c) => {
      if (r === rowIndex) return null;
      if (r > rowIndex) return [r - 1, c];
      return [r, c];
    });
    this._remapMergeAnchors((r, c) => {
      if (r === rowIndex) return null;
      if (r > rowIndex) return [r - 1, c];
      return [r, c];
    });
  }

  addColumn(afterIndex) {
    const insertAt = afterIndex !== undefined ? afterIndex + 1 : this.cols;
    this.cols += 1;
    this.colWidths.splice(insertAt, 0, DEFAULT_COL_WIDTH);
    this.cells = this._shiftCells((r, c) => (c >= insertAt ? [r, c + 1] : [r, c]));
    this._remapMergeAnchors((r, c) => (c >= insertAt ? [r, c + 1] : [r, c]));
  }

  removeColumn(colIndex) {
    if (this.cols <= 1) return;

    this._unmergeColsTouching(colIndex, colIndex);

    this.cols -= 1;
    this.colWidths.splice(colIndex, 1);
    this.cells = this._shiftCells((r, c) => {
      if (c === colIndex) return null;
      if (c > colIndex) return [r, c - 1];
      return [r, c];
    });
    this._remapMergeAnchors((r, c) => {
      if (c === colIndex) return null;
      if (c > colIndex) return [r, c - 1];
      return [r, c];
    });
  }

  mergeCells(startRow, startCol, endRow, endCol) {
    // Validate bounds.
    if (
      startRow < 0 || startCol < 0 ||
      endRow >= this.rows || endCol >= this.cols ||
      endRow < startRow || endCol < startCol
    ) return;
    if (startRow === endRow && startCol === endCol) return;

    // First, dissolve any pre-existing merges that overlap this range so we
    // don't end up with cells claiming two anchors.
    this._dissolveMergesInRange(startRow, startCol, endRow, endCol);

    const key = CanvasTableData.cellKey(startRow, startCol);
    this.cells[key] = {
      ...this.cells[key],
      colSpan: endCol - startCol + 1,
      rowSpan: endRow - startRow + 1,
    };

    for (let r = startRow; r <= endRow; r++) {
      for (let c = startCol; c <= endCol; c++) {
        if (r === startRow && c === startCol) continue;
        const mergedKey = CanvasTableData.cellKey(r, c);
        // Strip any conflicting span attrs — only the anchor carries them.
        const { colSpan, rowSpan, ...rest } = this.cells[mergedKey] || {};
        this.cells[mergedKey] = { ...rest, _mergedInto: key };
      }
    }
  }

  /** Unmerge a single anchor (undo a prior mergeCells call). */
  unmergeCell(row, col) {
    const key = CanvasTableData.cellKey(row, col);
    const cell = this.cells[key];
    if (!cell || (!cell.colSpan && !cell.rowSpan)) return;
    const colSpan = cell.colSpan || 1;
    const rowSpan = cell.rowSpan || 1;

    // Strip span flags from anchor.
    const { colSpan: _cs, rowSpan: _rs, ...rest } = cell;
    if (Object.keys(rest).length === 0) delete this.cells[key];
    else this.cells[key] = rest;

    for (let r = row; r < row + rowSpan; r++) {
      for (let c = col; c < col + colSpan; c++) {
        if (r === row && c === col) continue;
        const k = CanvasTableData.cellKey(r, c);
        const m = this.cells[k];
        if (!m) continue;
        const { _mergedInto, ...mr } = m;
        if (Object.keys(mr).length === 0) delete this.cells[k];
        else this.cells[k] = mr;
      }
    }
  }

  setColWidth(col, width) {
    this.colWidths[col] = Math.max(20, width);
  }

  setRowHeight(row, height) {
    this.rowHeights[row] = Math.max(15, height);
  }

  setDynamicSource(source) {
    this.dynamicSource = source;
  }

  /**
   * Populate data rows from a 2D array, preserving the header row.
   */
  populateFromData(dataRows) {
    if (!Array.isArray(dataRows) || dataRows.length === 0) return;

    const headerCells = {};
    for (let col = 0; col < this.cols; col++) {
      const key = CanvasTableData.cellKey(0, col);
      if (this.cells[key]) headerCells[key] = { ...this.cells[key] };
    }

    this.cells = { ...headerCells };
    this.rows = dataRows.length + 1;
    this.rowHeights = [
      this.rowHeights[0] || DEFAULT_ROW_HEIGHT,
      ...Array(dataRows.length).fill(this.rowHeights[1] || DEFAULT_ROW_HEIGHT),
    ];

    dataRows.forEach((rowData, rowIndex) => {
      const r = rowIndex + 1;
      if (!Array.isArray(rowData)) return;
      rowData.forEach((cellText, col) => {
        if (col < this.cols) {
          this.cells[CanvasTableData.cellKey(r, col)] = { text: String(cellText ?? '') };
        }
      });
    });
  }

  // ─── Serialization ─────────────────────────────────────

  toJSON() {
    return {
      rows: this.rows,
      cols: this.cols,
      colWidths: [...this.colWidths],
      rowHeights: [...this.rowHeights],
      // Deep copy cells so external mutation can't leak back into the model.
      cells: Object.fromEntries(
        Object.entries(this.cells).map(([k, v]) => [k, { ...v, style: v?.style ? { ...v.style } : undefined }])
      ),
      style: { ...this.style },
      dynamicSource: this.dynamicSource || null,
    };
  }

  // ─── Internal ──────────────────────────────────────────

  /**
   * Apply a coordinate transform to all cells. The mapper returns either
   * a new [row, col] tuple, or null to drop the cell.
   */
  _shiftCells(mapper) {
    const next = {};
    for (const [key, value] of Object.entries(this.cells)) {
      const [r, c] = key.split(',').map(Number);
      const mapped = mapper(r, c);
      if (mapped === null) continue;
      const [newR, newC] = mapped;
      next[CanvasTableData.cellKey(newR, newC)] = value;
    }
    return next;
  }

  /**
   * Rewrite `_mergedInto` pointers after a row/column shift so they still
   * reference the new (post-shift) anchor. If the mapper drops the anchor,
   * the merge pointer is cleared.
   */
  _remapMergeAnchors(mapper) {
    for (const [key, value] of Object.entries(this.cells)) {
      if (!value || !value._mergedInto) continue;
      const { row, col } = CanvasTableData.parseKey(value._mergedInto);
      const mapped = mapper(row, col);
      if (mapped === null) {
        const { _mergedInto, ...rest } = value;
        if (Object.keys(rest).length === 0) delete this.cells[key];
        else this.cells[key] = rest;
      } else {
        this.cells[key] = { ...value, _mergedInto: CanvasTableData.cellKey(mapped[0], mapped[1]) };
      }
    }
  }

  /**
   * Dissolve any merge whose region overlaps the given rectangular range.
   * Both anchor span flags and `_mergedInto` pointers are cleared.
   */
  _dissolveMergesInRange(startRow, startCol, endRow, endCol) {
    const toUnmerge = [];
    for (const [key, value] of Object.entries(this.cells)) {
      if (!value) continue;
      const { row, col } = CanvasTableData.parseKey(key);
      if (value.colSpan || value.rowSpan) {
        const cs = value.colSpan || 1;
        const rs = value.rowSpan || 1;
        const r2 = row + rs - 1;
        const c2 = col + cs - 1;
        const overlaps = !(r2 < startRow || row > endRow || c2 < startCol || col > endCol);
        if (overlaps) toUnmerge.push({ row, col });
      }
    }
    toUnmerge.forEach(({ row, col }) => this.unmergeCell(row, col));
  }

  /** Drop any merged group that touches the given rows. */
  _unmergeRowsTouching(startRow, endRow) {
    this._dissolveMergesInRange(startRow, 0, endRow, this.cols - 1);
  }

  /** Drop any merged group that touches the given columns. */
  _unmergeColsTouching(startCol, endCol) {
    this._dissolveMergesInRange(0, startCol, this.rows - 1, endCol);
  }
}

export default CanvasTableData;
