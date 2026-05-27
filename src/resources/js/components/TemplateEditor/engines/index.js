/**
 * Dynamic engines barrel export.
 *
 * Each engine is independently testable and replaceable:
 *   - VariableEngine     — {{var}} resolution (simple, nested, sections, conditionals)
 *   - DynamicTableEngine — runtime table expansion from data sources
 *   - SchemaRenderer     — JSON schema → Konva nodes (uses the engines above)
 *
 * REMOVED DEAD CODE:
 *   - FormulaEngine      — Never used in rendering pipeline (moved to backend/PHP)
 *   - PaginationEngine   — Never used in rendering pipeline (for future feature)
 */

export { VariableEngine } from './VariableEngine';
export { DynamicTableEngine } from './DynamicTableEngine';
export { SchemaRenderer } from './SchemaRenderer';
