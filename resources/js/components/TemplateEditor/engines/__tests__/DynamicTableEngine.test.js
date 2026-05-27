import { describe, it, expect } from 'vitest';
import { DynamicTableEngine } from '../DynamicTableEngine';

describe('DynamicTableEngine', () => {
  describe('static (no dynamicSource)', () => {
    it('resolves variables in cell text but does not change row count', () => {
      const desc = {
        rows: 1,
        cols: 1,
        cells: { '0,0': { text: 'Hello {{name}}' } },
      };
      const out = DynamicTableEngine.expand(desc, { name: 'Budi' });
      expect(out.cells['0,0'].text).toBe('Hello Budi');
      expect(out.rows).toBe(1);
    });

    it('passes through cells with no template variables', () => {
      const desc = {
        rows: 1,
        cols: 1,
        cells: { '0,0': { text: 'Static' } },
      };
      const out = DynamicTableEngine.expand(desc, {});
      expect(out.cells['0,0']).toBe(desc.cells['0,0']);
    });
  });

  describe('attendance source', () => {
    it('expands to header + 4 attendance rows', () => {
      const desc = {
        rows: 1,
        cols: 2,
        cells: {
          '0,0': { text: 'Status' },
          '0,1': { text: 'Jumlah' },
        },
        rowHeights: [40],
        dynamicSource: 'attendance',
      };

      const out = DynamicTableEngine.expand(desc, {
        attendance_hadir: 100,
        attendance_sakit: 1,
        attendance_izin: 2,
        attendance_alpa: 0,
      });

      expect(out.rows).toBe(5);
      expect(out.cells['1,0'].text).toBe('Hadir');
      expect(out.cells['1,1'].text).toBe('100');
      expect(out.cells['4,0'].text).toBe('Alpa');
    });
  });

  describe('grades source', () => {
    it('generates one row per subject with sequential numbering', () => {
      const desc = {
        rows: 2,
        cols: 3,
        cells: {
          '0,0': { text: 'No' },
          '0,1': { text: 'Mata Pelajaran' },
          '0,2': { text: 'Nilai' },
          '1,0': { text: '{{no}}' },
          '1,1': { text: 'X' },
          '1,2': { text: '{{nilai_mtk}}' },
        },
        rowHeights: [40, 40],
        dynamicSource: 'grades',
      };

      const out = DynamicTableEngine.expand(desc, { nilai_mtk: '90', nilai_indo: '85' });
      expect(out.rows).toBe(3); // header + 2 subjects
    });
  });

  describe('extracurricular source', () => {
    it('generates one row per ekskul_name_*', () => {
      const desc = {
        rows: 1,
        cols: 3,
        cells: { '0,0': { text: 'No' }, '0,1': { text: 'Nama' }, '0,2': { text: 'Nilai' } },
        rowHeights: [40],
        dynamicSource: 'extracurricular',
      };

      const out = DynamicTableEngine.expand(desc, {
        ekskul_name_1: 'Pramuka',
        ekskul_grade_1: 'A',
        ekskul_name_2: 'PMR',
        ekskul_grade_2: 'B',
      });

      expect(out.rows).toBe(3);
      expect(out.cells['1,1'].text).toBe('Pramuka');
      expect(out.cells['2,1'].text).toBe('PMR');
    });
  });

  describe('custom source registration', () => {
    it('register() accepts a resolver and uses it via expand()', () => {
      DynamicTableEngine.register('my_custom', () => [['A', '1'], ['B', '2']]);

      const desc = {
        rows: 1,
        cols: 2,
        cells: { '0,0': { text: 'K' }, '0,1': { text: 'V' } },
        rowHeights: [40],
        dynamicSource: 'my_custom',
      };

      const out = DynamicTableEngine.expand(desc, {});
      expect(out.rows).toBe(3);
      expect(out.cells['1,0'].text).toBe('A');
      expect(out.cells['2,1'].text).toBe('2');
    });

    it('register() rejects non-functions', () => {
      expect(() => DynamicTableEngine.register('bad', 'not_a_fn')).toThrow();
    });

    it('has() reports registered sources', () => {
      expect(DynamicTableEngine.has('grades')).toBe(true);
      expect(DynamicTableEngine.has('definitely-missing')).toBe(false);
    });
  });
});
