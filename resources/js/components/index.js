/**
 * Main components index
 * Provides convenient exports for all components
 */

// Table components
export { default as DataTable } from './Common/Table/DataTable';
export { default as DataTableHeader } from './Common/Table/DataTableHeader';
export { default as DataTableRow } from './Common/Table/DataTableRow';
export { default as DataTableCell } from './Common/Table/DataTableCell';
export { default as DataTablePagination } from './Common/Table/DataTablePagination';
export { default as StatusBadge } from './Common/Table/StatusBadge';

// Editor components
export { default as TemplatesList } from './Editor/TemplatesList';
export { default as TemplateInstancesList } from './Editor/TemplateInstancesList';

// Custom hooks
export { useApi, useDebounce, useTableState } from './hooks/useApi';
