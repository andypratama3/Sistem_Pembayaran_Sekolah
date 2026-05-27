/**
 * CanvasTableEditor.js — Inline textarea overlay for editing a table cell.
 *
 * Creates a positioned <textarea> on the stage container, focuses it, and
 * commits the value back to the table on blur / Enter / Tab. Escape cancels.
 *
 * The overlay accounts for:
 *   - stage scale (zoom) and stage position (pan)
 *   - the host group's absolute transform (so nested / rotated tables work)
 *   - per-cell padding overrides
 *   - shift+Tab for backwards navigation
 */

export class CanvasTableEditor {
  /**
   * Begin editing a cell. Returns { textarea, cancel } so the caller can track
   * the active edit if needed.
   *
   * @param {object} params
   * @param {Konva.Stage} params.stage
   * @param {Konva.Group} params.group
   * @param {CanvasTableData} params.data
   * @param {number} params.row
   * @param {number} params.col
   * @param {(row:number, col:number, text:string) => void} params.onCommit
   * @param {(direction:'right'|'down'|'left'|'up', row:number, col:number) => void} params.onAdvance
   */
  static start({ stage, group, data, row, col, onCommit, onAdvance }) {
    if (!stage || !group || !data) return null;
    if (stage.isListening && stage.isListening() === false && stage._isDestroyed) return null;

    const cell = data.getCell(row, col);
    const { style } = data;

    // Per-cell padding override (matches CanvasTableRenderer)
    const cellPadding = cell.style?.padding !== undefined
      ? parseFloat(cell.style.padding)
      : style.cellPadding;

    const localCellX = data.getCellX(col);
    const localCellY = data.getCellY(row);
    const cellW = data.colWidths[col];
    const cellH = data.rowHeights[row];

    // Convert local (group) point to absolute (stage container) screen coordinates.
    // group.getAbsoluteTransform() folds in stage scale/position AND any group transform,
    // so the textarea lines up with the rendered cell after zoom/pan/rotation.
    const transform = group.getAbsoluteTransform().copy();
    const topLeft = transform.point({ x: localCellX, y: localCellY });
    const bottomRight = transform.point({ x: localCellX + cellW, y: localCellY + cellH });

    const screenW = bottomRight.x - topLeft.x;
    const screenH = bottomRight.y - topLeft.y;
    const stageScale = stage.scaleX() || 1;

    const container = stage.container();
    const textarea = document.createElement('textarea');
    textarea.value = cell.text || '';
    // Use scale on textarea so the font metrics match the rendered text under zoom.
    textarea.style.cssText = `
      position: absolute;
      left: ${topLeft.x + cellPadding * stageScale}px;
      top: ${topLeft.y + cellPadding * stageScale}px;
      width: ${Math.max(1, screenW - cellPadding * 2 * stageScale)}px;
      height: ${Math.max(1, screenH - cellPadding * 2 * stageScale)}px;
      font-size: ${(cell.style?.fontSize || style.fontSize) * stageScale}px;
      font-family: ${cell.style?.fontFamily || style.fontFamily};
      font-weight: ${cell.style?.bold ? 'bold' : 'normal'};
      font-style: ${cell.style?.italic ? 'italic' : 'normal'};
      color: ${cell.style?.color || '#1e293b'};
      border: 2px solid #3b82f6;
      outline: none;
      resize: none;
      background: white;
      padding: 2px;
      margin: 0;
      box-sizing: border-box;
      z-index: 1000;
      overflow: hidden;
      line-height: 1.2;
    `;

    // Remember whether the container already had a non-static position; only set
    // it ourselves if needed, and restore on cleanup so we don't leak a style.
    const previousPosition = container.style.position;
    const computedPosition = window.getComputedStyle(container).position;
    if (computedPosition === 'static') {
      container.style.position = 'relative';
    }

    container.appendChild(textarea);
    // Defer focus until the textarea is in the DOM and laid out.
    requestAnimationFrame(() => {
      try { textarea.focus(); textarea.select(); } catch { /* noop */ }
    });

    let committed = false;

    const cleanupContainerStyle = () => {
      if (computedPosition === 'static') {
        container.style.position = previousPosition;
      }
    };

    const removeTextarea = () => {
      if (textarea.parentNode === container) {
        try { container.removeChild(textarea); } catch { /* already removed */ }
      }
    };

    const commit = () => {
      if (committed) return;
      committed = true;
      // Detach blur first so removeChild can't re-trigger commit during teardown.
      textarea.removeEventListener('blur', commit);
      removeTextarea();
      cleanupContainerStyle();
      onCommit?.(row, col, textarea.value);
    };

    const cancel = () => {
      if (committed) return;
      committed = true;
      textarea.removeEventListener('blur', commit);
      removeTextarea();
      cleanupContainerStyle();
    };

    textarea.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const nextRow = row + 1 < data.rows ? row + 1 : row;
        commit();
        if (nextRow !== row) onAdvance?.('down', nextRow, col);
        return;
      }

      if (e.key === 'Tab') {
        e.preventDefault();
        // shift+Tab → backwards, Tab → forwards
        let nextRow = row;
        let nextCol = col;
        let direction = 'right';

        if (e.shiftKey) {
          direction = 'left';
          if (col - 1 >= 0) {
            nextCol = col - 1;
          } else if (row - 1 >= 0) {
            nextRow = row - 1;
            nextCol = data.cols - 1;
          } else {
            // already at top-left; just commit and stop
            commit();
            return;
          }
        } else {
          direction = 'right';
          if (col + 1 < data.cols) {
            nextCol = col + 1;
          } else if (row + 1 < data.rows) {
            nextRow = row + 1;
            nextCol = 0;
          } else {
            commit();
            return;
          }
        }

        commit();
        onAdvance?.(direction, nextRow, nextCol);
        return;
      }

      if (e.key === 'Escape') {
        e.preventDefault();
        cancel();
      }
    });

    // Stop interactions inside the textarea from reaching the Konva stage,
    // otherwise mousedown/click bubbles up and triggers cell selection /
    // deselection during typing.
    const stopProp = (e) => { e.stopPropagation(); };
    textarea.addEventListener('mousedown', stopProp);
    textarea.addEventListener('click', stopProp);
    textarea.addEventListener('dblclick', stopProp);
    textarea.addEventListener('pointerdown', stopProp);
    textarea.addEventListener('touchstart', stopProp);

    textarea.addEventListener('blur', commit);

    return { textarea, cancel, commit };
  }
}

export default CanvasTableEditor;
