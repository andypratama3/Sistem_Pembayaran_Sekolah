/**
 * DynamicTableEngine.js — Schema-driven runtime table population
 *
 * Takes a CanvasTable JSON descriptor with `dynamicSource` and a data context,
 * and returns an updated descriptor with rows expanded from the data source.
 *
 * Mirrors the backend `TemplateGeneratorService::resolveDynamicTableData` so
 * editor previews match PDF output exactly.
 *
 * Built-in sources:
 *   - 'grades'         → one row per { subject, score, predicate }
 *   - 'attendance'     → fixed 4 rows: hadir, sakit, izin, alpa
 *   - 'extracurricular'→ one row per { name, grade }
 *
 * Custom sources can be registered via `DynamicTableEngine.register(name, fn)`.
 */

import { VariableEngine } from './VariableEngine';

const SOURCES = new Map();

// Hard cap to avoid runaway expansion if a data source produces thousands of rows.
const MAX_DYNAMIC_ROWS = 1000;

/** Register built-in resolvers. */

SOURCES.set('grades', (templateRow, vars) => {
  const subjects = new Set();
  if (vars && typeof vars === 'object') {
    for (const key of Object.keys(vars)) {
      const m = /^(grade_|nilai_)(.+)$/.exec(key);
      if (m && m[2] !== 'average') subjects.add(m[2]);
    }
  }

  // Deterministic order so PDF output is stable.
  const sortedSubjects = Array.from(subjects).sort();

  const rows = [];
  let num = 1;
  for (const subject of sortedSubjects) {
    rows.push(
      templateRow.map((cell) => {
        let text = cell ?? '';
        text = text.replace(/\{\{no\}\}/g, String(num));
        text = text.replace(
          new RegExp(`\\{\\{nilai_${subject}\\}\\}`, 'g'),
          vars[`nilai_${subject}`] ?? vars[`grade_${subject}`] ?? '–'
        );
        text = text.replace(
          new RegExp(`\\{\\{capaian_${subject}\\}\\}`, 'g'),
          vars[`capaian_${subject}`] ?? '–'
        );
        text = text.replace(
          new RegExp(`\\{\\{keterangan_${subject}\\}\\}`, 'g'),
          vars[`keterangan_${subject}`] ?? '–'
        );
        if (/^\d+$/.test(cell ?? '')) text = String(num);
        return text;
      })
    );
    num++;
  }
  return rows;
});

SOURCES.set('attendance', (_templateRow, vars) => [
  ['Hadir', String(vars.attendance_hadir ?? vars.hadir ?? '0')],
  ['Sakit', String(vars.attendance_sakit ?? vars.sakit ?? '0')],
  ['Izin', String(vars.attendance_izin ?? vars.izin ?? '0')],
  ['Alpa', String(vars.attendance_alpa ?? vars.alpa ?? '0')],
]);

SOURCES.set('extracurricular', (_templateRow, vars) => {
  const rows = [];
  let num = 1;
  if (!vars || typeof vars !== 'object') return rows;
  // Sort keys for deterministic order.
  const keys = Object.keys(vars).filter((k) => k.startsWith('ekskul_name_')).sort();
  for (const key of keys) {
    const idx = key.slice('ekskul_name_'.length);
    rows.push([String(num), String(vars[key]), String(vars[`ekskul_grade_${idx}`] ?? '–')]);
    num++;
  }
  return rows;
});

export class DynamicTableEngine {
  /**
   * Register a custom dynamic source resolver.
   *
   * @param {string} name
   * @param {(templateRow: string[], context: object) => string[][]} resolver
   */
  static register(name, resolver) {
    if (typeof resolver !== 'function') {
      throw new Error(`DynamicTableEngine: resolver for "${name}" must be a function`);
    }
    SOURCES.set(name, resolver);
  }

  /** Returns true if there is a registered resolver for the given source name. */
  static has(name) {
    return SOURCES.has(name);
  }

  /**
   * Expand a CanvasTable descriptor with dynamic data.
   *
   * @param {object} tableDescriptor — { rows, cols, cells, colWidths, rowHeights, dynamicSource, ... }
   * @param {object} context — variable map
   * @returns {object} new descriptor (original is not mutated)
   */
  static expand(tableDescriptor, context = {}) {
    if (!tableDescriptor || typeof tableDescriptor !== 'object') {
      return tableDescriptor;
    }

    const source = tableDescriptor?.dynamicSource;
    if (!source || !SOURCES.has(source)) {
      // No dynamic source — resolve {{vars}} in cell texts and return.
      return DynamicTableEngine._resolveStaticVariables(tableDescriptor, context);
    }

    const cols = tableDescriptor.cols ?? 0;
    const cells = tableDescriptor.cells ?? {};

    // Extract template row (row 1) — the pattern that data rows are built from.
    const templateRow = [];
    for (let c = 0; c < cols; c++) {
      templateRow.push(cells[`1,${c}`]?.text ?? '');
    }

    const dataRows = SOURCES.get(source)(templateRow, context);
    if (!Array.isArray(dataRows) || dataRows.length === 0) {
      return DynamicTableEngine._resolveStaticVariables(tableDescriptor, context);
    }

    // Cap to prevent runaway expansion (e.g., 50k subjects from a polluted context).
    const cappedRows = dataRows.length > MAX_DYNAMIC_ROWS
      ? dataRows.slice(0, MAX_DYNAMIC_ROWS)
      : dataRows;
    if (dataRows.length > MAX_DYNAMIC_ROWS) {
      console.warn(`[DynamicTableEngine] Dynamic source "${source}" produced ${dataRows.length} rows; capped at ${MAX_DYNAMIC_ROWS}`);
    }

    // Build new cells: header row + data rows.
    const newCells = {};
    for (let c = 0; c < cols; c++) {
      const headerKey = `0,${c}`;
      if (cells[headerKey]) {
        newCells[headerKey] = { ...cells[headerKey] };
        // Resolve any variables in headers.
        if (newCells[headerKey].text) {
          newCells[headerKey].text = VariableEngine.interpolate(newCells[headerKey].text, context);
        }
      }
    }

    const defaultRowHeight = tableDescriptor.rowHeights?.[1] ?? 40;
    const newRowHeights = [tableDescriptor.rowHeights?.[0] ?? 40];

    cappedRows.forEach((rowData, rowIdx) => {
      const r = rowIdx + 1;
      newRowHeights.push(defaultRowHeight);
      if (!Array.isArray(rowData)) return;
      rowData.forEach((text, c) => {
        if (c < cols) {
          newCells[`${r},${c}`] = { text: String(text ?? '') };
        }
      });
    });

    return {
      ...tableDescriptor,
      rows: cappedRows.length + 1,
      cells: newCells,
      rowHeights: newRowHeights,
    };
  }

  static _resolveStaticVariables(tableDescriptor, context) {
    if (!tableDescriptor || typeof tableDescriptor !== 'object') {
      return tableDescriptor;
    }

    const cells = tableDescriptor.cells ?? {};
    if (typeof cells !== 'object') {
      return tableDescriptor;
    }

    const newCells = {};

    for (const [key, cell] of Object.entries(cells)) {
      if (cell && typeof cell.text === 'string' && cell.text.includes('{{')) {
        newCells[key] = {
          ...cell,
          text: VariableEngine.interpolate(cell.text, context),
        };
      } else {
        newCells[key] = cell;
      }
    }

    return { ...tableDescriptor, cells: newCells };
  }
}

export default DynamicTableEngine;
