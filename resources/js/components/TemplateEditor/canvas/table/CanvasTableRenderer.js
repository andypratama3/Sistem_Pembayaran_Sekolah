/**
 * CanvasTableRenderer.js — Konva render pass for a CanvasTableData.
 *
 * Pure render: takes a `CanvasTableData` instance and a target Konva.Group,
 * clears the group, and adds rect+text children for each visible cell.
 * Selection highlight is opt-in via `selectedCell`.
 */

import Konva from 'konva';
import { CanvasTableData } from './CanvasTableData';

export class CanvasTableRenderer {
  /**
   * Render all cells of the table into the given group.
   *
   * @param {Konva.Group} group
   * @param {CanvasTableData} data
   * @param {{row:number,col:number}|null} selectedCell
   */
  static renderCells(group, data, selectedCell) {
    const { style } = data;

    for (let row = 0; row < data.rows; row++) {
      for (let col = 0; col < data.cols; col++) {
        const cell = data.getCell(row, col);
        if (cell._mergedInto) continue;

        const rect = CanvasTableRenderer._buildCellRect(data, row, col, cell, style, selectedCell);
        const text = CanvasTableRenderer._buildCellText(data, row, col, cell, style);
        group.add(rect);
        group.add(text);
      }
    }
  }

  static _buildCellRect(data, row, col, cell, style, selectedCell) {
    const key = CanvasTableData.cellKey(row, col);
    const colSpan = cell.colSpan || 1;
    const rowSpan = cell.rowSpan || 1;
    const isHeader = row === 0;
    const isSelected = selectedCell?.row === row && selectedCell?.col === col;

    const rect = new Konva.Rect({
      id: `cell-rect-${key}`,
      x: data.getCellX(col),
      y: data.getCellY(row),
      width: data.colWidths.slice(col, col + colSpan).reduce((a, b) => a + b, 0),
      height: data.rowHeights.slice(row, row + rowSpan).reduce((a, b) => a + b, 0),
      fill: cell.style?.bg || (isHeader ? style.headerBg : style.cellBg),
      stroke: isSelected ? '#3b82f6' : style.borderColor,
      strokeWidth: isSelected ? 2 : style.borderWidth,
    });

    return rect;
  }

  static _buildCellText(data, row, col, cell, style) {
    const key = CanvasTableData.cellKey(row, col);
    const colSpan = cell.colSpan || 1;
    const rowSpan = cell.rowSpan || 1;
    const cellW = data.colWidths.slice(col, col + colSpan).reduce((a, b) => a + b, 0);
    const cellH = data.rowHeights.slice(row, row + rowSpan).reduce((a, b) => a + b, 0);

    const padding = cell.style?.padding !== undefined
      ? parseFloat(cell.style.padding)
      : style.cellPadding;

    return new Konva.Text({
      id: `cell-text-${key}`,
      x: data.getCellX(col) + padding,
      y: data.getCellY(row) + padding,
      width: cellW - padding * 2,
      height: cellH - padding * 2,
      text: cell.text || '',
      fontSize: cell.style?.fontSize || style.fontSize,
      fontFamily: cell.style?.fontFamily || style.fontFamily,
      fontStyle: CanvasTableRenderer._buildFontStyle(cell.style),
      textDecoration: cell.style?.underline ? 'underline' : 'none',
      align: cell.style?.align || 'left',
      verticalAlign: cell.style?.verticalAlign || 'middle',
      fill: cell.style?.color || '#1e293b',
      wrap: 'word',
      ellipsis: true,
      listening: false,
    });
  }

  static _buildFontStyle(cellStyle = {}) {
    if (cellStyle.bold && cellStyle.italic) return 'bold italic';
    if (cellStyle.bold) return 'bold';
    if (cellStyle.italic) return 'italic';
    return 'normal';
  }
}

export default CanvasTableRenderer;
