import { describe, it, expect } from 'vitest';
import { CanvasTableData } from '../CanvasTableData';

describe('CanvasTableData', () => {
  it('initializes with sensible defaults', () => {
    const t = new CanvasTableData();
    expect(t.rows).toBe(3);
    expect(t.cols).toBe(3);
    expect(t.colWidths).toHaveLength(3);
    expect(t.rowHeights).toHaveLength(3);
  });

  it('totalWidth and totalHeight sum the dimensions', () => {
    const t = new CanvasTableData({ rows: 2, cols: 2, colWidths: [50, 60], rowHeights: [10, 20] });
    expect(t.totalWidth).toBe(110);
    expect(t.totalHeight).toBe(30);
  });

  it('getCellAtPoint() returns row+col for a point inside the grid', () => {
    const t = new CanvasTableData({
      rows: 2, cols: 2,
      colWidths: [100, 100], rowHeights: [50, 50],
    });
    expect(t.getCellAtPoint(50, 25)).toEqual({ row: 0, col: 0 });
    expect(t.getCellAtPoint(150, 25)).toEqual({ row: 0, col: 1 });
    expect(t.getCellAtPoint(150, 75)).toEqual({ row: 1, col: 1 });
  });

  it('addRow inserts at the right index and shifts subsequent cells', () => {
    const t = new CanvasTableData({ rows: 2, cols: 1, cells: { '0,0': { text: 'A' }, '1,0': { text: 'B' } } });
    t.addRow(0); // insert after row 0
    expect(t.rows).toBe(3);
    expect(t.cells['0,0']?.text).toBe('A');
    expect(t.cells['2,0']?.text).toBe('B'); // original row 1 shifted down to row 2
    expect(t.cells['1,0']).toBeUndefined();  // newly inserted row is empty
  });

  it('removeRow drops cells and shifts later rows up', () => {
    const t = new CanvasTableData({
      rows: 3, cols: 1,
      cells: { '0,0': { text: 'A' }, '1,0': { text: 'B' }, '2,0': { text: 'C' } },
    });
    t.removeRow(1);
    expect(t.rows).toBe(2);
    expect(t.cells['0,0']?.text).toBe('A');
    expect(t.cells['1,0']?.text).toBe('C'); // C moved up
    expect(t.cells['2,0']).toBeUndefined();
  });

  it('addColumn shifts cells right of the insertion point', () => {
    const t = new CanvasTableData({
      rows: 1, cols: 2,
      cells: { '0,0': { text: 'A' }, '0,1': { text: 'B' } },
    });
    t.addColumn(0);
    expect(t.cols).toBe(3);
    expect(t.cells['0,0']?.text).toBe('A');
    expect(t.cells['0,2']?.text).toBe('B');
  });

  it('removeColumn drops cells in the removed column', () => {
    const t = new CanvasTableData({
      rows: 1, cols: 3,
      cells: { '0,0': { text: 'A' }, '0,1': { text: 'B' }, '0,2': { text: 'C' } },
    });
    t.removeColumn(1);
    expect(t.cols).toBe(2);
    expect(t.cells['0,0']?.text).toBe('A');
    expect(t.cells['0,1']?.text).toBe('C');
  });

  it('mergeCells records colSpan/rowSpan and marks others as merged', () => {
    const t = new CanvasTableData({ rows: 2, cols: 2 });
    t.mergeCells(0, 0, 1, 1);
    expect(t.cells['0,0'].colSpan).toBe(2);
    expect(t.cells['0,0'].rowSpan).toBe(2);
    expect(t.cells['0,1']._mergedInto).toBe('0,0');
    expect(t.cells['1,0']._mergedInto).toBe('0,0');
    expect(t.cells['1,1']._mergedInto).toBe('0,0');
  });

  it('removeRow does nothing when only one row remains', () => {
    const t = new CanvasTableData({ rows: 1, cols: 1 });
    t.removeRow(0);
    expect(t.rows).toBe(1);
  });

  it('populateFromData preserves header and replaces data rows', () => {
    const t = new CanvasTableData({
      rows: 2, cols: 2,
      cells: {
        '0,0': { text: 'No', style: { bold: true } },
        '0,1': { text: 'Name', style: { bold: true } },
        '1,0': { text: 'OLD' },
        '1,1': { text: 'OLD' },
      },
    });

    t.populateFromData([['1', 'Alice'], ['2', 'Bob']]);

    expect(t.rows).toBe(3);
    expect(t.cells['0,0'].text).toBe('No');
    expect(t.cells['0,0'].style.bold).toBe(true);
    expect(t.cells['1,0'].text).toBe('1');
    expect(t.cells['2,1'].text).toBe('Bob');
  });

  it('toJSON() roundtrips through the constructor', () => {
    const original = new CanvasTableData({
      rows: 2, cols: 2,
      cells: { '0,0': { text: 'A' } },
      style: { fontSize: 20 },
      dynamicSource: 'grades',
    });

    const restored = new CanvasTableData(original.toJSON());

    expect(restored.rows).toBe(2);
    expect(restored.cells['0,0'].text).toBe('A');
    expect(restored.style.fontSize).toBe(20);
    expect(restored.dynamicSource).toBe('grades');
  });
});
