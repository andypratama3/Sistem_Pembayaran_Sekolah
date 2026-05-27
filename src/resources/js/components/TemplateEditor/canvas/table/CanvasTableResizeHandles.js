/**
 * CanvasTableResizeHandles.js — Drag-to-resize handles for table columns/rows.
 *
 * Adds invisible 6px-wide draggable lines between columns and between rows.
 * On drag, mutates the `CanvasTableData` and fires a `table:resized` event on
 * the host group so the orchestrator can re-render.
 *
 * Handles operate in *group-local* coordinates (as Konva does for child nodes
 * of a group), so the math here is independent of stage scale / position.
 * Cursor changes are applied via mouseenter/mouseleave on the stage container,
 * since Konva doesn't honour an arbitrary `cursor` shape attribute.
 */

import Konva from 'konva';

const MIN_COL_WIDTH = 20;
const MIN_ROW_HEIGHT = 15;

export class CanvasTableResizeHandles {
  /**
   * Add column + row resize handles to the given group.
   *
   * @param {Konva.Group} group  — host CanvasTable group
   * @param {CanvasTableData} data
   */
  static attach(group, data) {
    CanvasTableResizeHandles._attachColumns(group, data);
    CanvasTableResizeHandles._attachRows(group, data);
  }

  static _bindCursor(handle, group, cursor) {
    handle.on('mouseenter', () => {
      const stage = group.getStage();
      const container = stage?.container();
      if (container) container.style.cursor = cursor;
    });
    handle.on('mouseleave', () => {
      const stage = group.getStage();
      const container = stage?.container();
      // Don't clobber other cursors if a drag is happening on a different element.
      if (container && container.style.cursor === cursor) {
        container.style.cursor = '';
      }
    });
    // Reset cursor after drag ends in case mouseleave fired during drag-capture.
    handle.on('dragend', () => {
      const stage = group.getStage();
      const container = stage?.container();
      if (container) container.style.cursor = '';
    });
  }

  static _attachColumns(group, data) {
    let cumX = 0;

    for (let col = 0; col < data.cols - 1; col++) {
      cumX += data.colWidths[col];
      const lineX = cumX;

      // Capture per-iteration values in const so closures use them, not the
      // mutating loop variable.
      const colIndex = col;

      const handle = new Konva.Line({
        id: `col-resize-${colIndex}`,
        // Drawn relative to (lineX, 0) so dragmove can read e.target.x() directly
        // as the new boundary position in group-local coordinates.
        x: lineX,
        y: 0,
        points: [0, 0, 0, data.totalHeight],
        stroke: 'transparent',
        strokeWidth: 6,
        hitStrokeWidth: 8,
        draggable: true,
        name: 'col-resize-handle',
        listening: true,
        // Group-local clamp expressed in absolute coords (Konva calls this with
        // an absolute stage point, so we must convert to/from absolute here).
        dragBoundFunc: function (pos) {
          const groupTransform = group.getAbsoluteTransform().copy();
          // Local target x/y corresponding to current absolute pointer.
          const inverted = groupTransform.copy().invert();
          const local = inverted.point(pos);

          const minLocalX = data.getCellX(colIndex) + MIN_COL_WIDTH;
          const maxLocalX = data.getCellX(colIndex + 1) + data.colWidths[colIndex + 1] - MIN_COL_WIDTH;
          const clampedLocalX = Math.max(minLocalX, Math.min(local.x, maxLocalX));

          // Lock Y to handle's intended group-local Y of 0.
          const abs = groupTransform.point({ x: clampedLocalX, y: 0 });
          return abs;
        },
      });

      handle.on('dragmove', (e) => {
        // e.target.x() is in group-local coords (handle is a child of group).
        const localX = e.target.x();
        const newWidth = Math.max(MIN_COL_WIDTH, localX - data.getCellX(colIndex));
        data.setColWidth(colIndex, newWidth);
        group.fire('table:internal-resize', { axis: 'col', index: colIndex });
      });

      handle.on('dragend', () => {
        group.fire('table:resized', { tableId: group.id() });
      });

      // Don't let drag/click on a resize handle bubble up and start a table drag
      // or trigger cell selection.
      handle.on('mousedown touchstart pointerdown', (e) => {
        e.cancelBubble = true;
      });

      CanvasTableResizeHandles._bindCursor(handle, group, 'col-resize');

      group.add(handle);
    }
  }

  static _attachRows(group, data) {
    let cumY = 0;

    for (let row = 0; row < data.rows - 1; row++) {
      cumY += data.rowHeights[row];
      const lineY = cumY;
      const rowIndex = row;

      const handle = new Konva.Line({
        id: `row-resize-${rowIndex}`,
        x: 0,
        y: lineY,
        points: [0, 0, data.totalWidth, 0],
        stroke: 'transparent',
        strokeWidth: 6,
        hitStrokeWidth: 8,
        draggable: true,
        name: 'row-resize-handle',
        listening: true,
        dragBoundFunc: function (pos) {
          const groupTransform = group.getAbsoluteTransform().copy();
          const inverted = groupTransform.copy().invert();
          const local = inverted.point(pos);

          const minLocalY = data.getCellY(rowIndex) + MIN_ROW_HEIGHT;
          const maxLocalY = data.getCellY(rowIndex + 1) + data.rowHeights[rowIndex + 1] - MIN_ROW_HEIGHT;
          const clampedLocalY = Math.max(minLocalY, Math.min(local.y, maxLocalY));

          const abs = groupTransform.point({ x: 0, y: clampedLocalY });
          return abs;
        },
      });

      handle.on('dragmove', (e) => {
        const localY = e.target.y();
        const newHeight = Math.max(MIN_ROW_HEIGHT, localY - data.getCellY(rowIndex));
        data.setRowHeight(rowIndex, newHeight);
        group.fire('table:internal-resize', { axis: 'row', index: rowIndex });
      });

      handle.on('dragend', () => {
        group.fire('table:resized', { tableId: group.id() });
      });

      handle.on('mousedown touchstart pointerdown', (e) => {
        e.cancelBubble = true;
      });

      CanvasTableResizeHandles._bindCursor(handle, group, 'row-resize');

      group.add(handle);
    }
  }
}

export default CanvasTableResizeHandles;
