import { useState, useEffect, useCallback } from 'react';
import DataTableHeader from './DataTableHeader';
import DataTableRow from './DataTableRow';
import DataTablePagination from './DataTablePagination';

/**
 * DataTable Component
 * A fully customizable React table component with sorting, filtering, pagination, and row selection
 * 
 * @component
 * @param {Array<Object>} columns - Column definitions with keys, titles, and render functions
 * @param {Array<Object>} data - Table data
 * @param {boolean} loading - Whether data is loading
 * @param {number} totalItems - Total number of items across all pages
 * @param {number} currentPage - Current page number (1-indexed)
 * @param {number} pageSize - Number of items per page
 * @param {function} onPageChange - Callback when page changes
 * @param {function} onSort - Callback when sort changes
 * @param {function} onFilter - Callback when filter changes
 * @param {function} onRowSelect - Callback when rows are selected
 * @param {boolean} searchable - Whether search is enabled
 * @param {boolean} selectable - Whether rows are selectable
 * @param {Array<Object>} actions - Bulk action definitions
 * @param {string} emptyMessage - Message to show when no data
 * @param {function} getRowId - Function to get unique ID for each row (default: index)
 * @returns {JSX.Element}
 */
const DataTable = ({
  columns,
  data = [],
  loading = false,
  totalItems = 0,
  currentPage = 1,
  pageSize = 10,
  onPageChange = () => {},
  onSort = () => {},
  onFilter = () => {},
  onRowSelect = () => {},
  searchable = false,
  selectable = false,
  actions = [],
  emptyMessage = 'No data available',
  getRowId = (row, index) => row.id || index,
}) => {
  const [selectedIds, setSelectedIds] = useState(new Set());
  const [sortConfig, setSortConfig] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  // Calculate total pages
  const totalPages = Math.ceil(totalItems / pageSize);

  // Handle sort
  const handleSort = useCallback((field, direction) => {
    setSortConfig({ key: field, direction });
    onSort(field, direction);
  }, [onSort]);

  // Handle row selection
  const handleRowSelect = useCallback((rowId) => {
    const newSelectedIds = new Set(selectedIds);
    if (newSelectedIds.has(rowId)) {
      newSelectedIds.delete(rowId);
    } else {
      newSelectedIds.add(rowId);
    }
    setSelectedIds(newSelectedIds);
    onRowSelect(Array.from(newSelectedIds));
  }, [selectedIds, onRowSelect]);

  // Handle select all
  const handleSelectAll = useCallback((e) => {
    if (e.target.checked) {
      const allIds = new Set(data.map((row, idx) => getRowId(row, idx)));
      setSelectedIds(allIds);
      onRowSelect(Array.from(allIds));
    } else {
      setSelectedIds(new Set());
      onRowSelect([]);
    }
  }, [data, getRowId, onRowSelect]);

  // Handle page change
  const handlePageChange = useCallback((page) => {
    onPageChange(page);
    setSelectedIds(new Set()); // Clear selection on page change
  }, [onPageChange]);

  // Handle search
  const handleSearch = useCallback((term) => {
    setSearchTerm(term);
    onFilter({ search: term });
  }, [onFilter]);

  return (
    <div className="flex flex-col">
      {/* Search Bar */}
      {searchable && (
        <div className="px-6 py-4 border-b border-gray-200 bg-white">
          <input
            type="text"
            placeholder="Search..."
            value={searchTerm}
            onChange={(e) => handleSearch(e.target.value)}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            aria-label="Search table"
          />
        </div>
      )}

      {/* Bulk Actions */}
      {selectable && selectedIds.size > 0 && actions.length > 0 && (
        <div className="px-6 py-3 bg-blue-50 border-b border-gray-200 flex items-center justify-between">
          <span className="text-sm text-gray-700">
            {selectedIds.size} row{selectedIds.size !== 1 ? 's' : ''} selected
          </span>
          <div className="flex gap-2">
            {actions.map((action, idx) => (
              <button
                key={idx}
                onClick={() => action.onClick(Array.from(selectedIds))}
                className={`px-3 py-1 text-sm font-medium rounded transition-colors ${
                  action.variant === 'danger'
                    ? 'bg-red-600 text-white hover:bg-red-700'
                    : action.variant === 'warning'
                    ? 'bg-yellow-600 text-white hover:bg-yellow-700'
                    : action.variant === 'success'
                    ? 'bg-green-600 text-white hover:bg-green-700'
                    : 'bg-blue-600 text-white hover:bg-blue-700'
                }`}
                aria-label={action.label}
              >
                {action.label}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Table */}
      <div className="overflow-x-auto">
        <table className="w-full border-collapse bg-white">
          <DataTableHeader
            columns={columns}
            sortConfig={sortConfig}
            selectable={selectable}
            selectedCount={selectedIds.size}
            totalItems={data.length}
            onSort={handleSort}
            onSelectAll={handleSelectAll}
          />
          <tbody>
            {loading ? (
              <tr>
                <td colSpan={columns.length + (selectable ? 1 : 0)} className="px-6 py-12 text-center">
                  <div className="flex justify-center items-center">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                  </div>
                </td>
              </tr>
            ) : data.length === 0 ? (
              <tr>
                <td colSpan={columns.length + (selectable ? 1 : 0)} className="px-6 py-12 text-center">
                  <div className="text-gray-500">
                    <svg className="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p className="text-sm font-medium">{emptyMessage}</p>
                  </div>
                </td>
              </tr>
            ) : (
              data.map((row, index) => {
                const rowId = getRowId(row, index);
                return (
                  <DataTableRow
                    key={rowId}
                    row={row}
                    columns={columns}
                    selectable={selectable}
                    isSelected={selectedIds.has(rowId)}
                    onSelect={handleRowSelect}
                    rowId={rowId}
                  />
                );
              })
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      <DataTablePagination
        currentPage={currentPage}
        totalPages={totalPages}
        totalItems={totalItems}
        pageSize={pageSize}
        onPageChange={handlePageChange}
      />
    </div>
  );
};

export default DataTable;
