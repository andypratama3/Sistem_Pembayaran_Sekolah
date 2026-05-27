/**
 * DataTableHeader Component
 * Table header with sorting capabilities
 * 
 * @component
 * @param {Array} columns - Column definitions
 * @param {Object} sortConfig - Current sort configuration
 * @param {boolean} selectable - Whether rows are selectable
 * @param {number} selectedCount - Number of selected rows
 * @param {number} totalItems - Total number of items
 * @param {function} onSort - Callback when sort changes
 * @param {function} onSelectAll - Callback when select all is clicked
 * @returns {JSX.Element}
 */
const DataTableHeader = ({
  columns,
  sortConfig,
  selectable,
  selectedCount,
  totalItems,
  onSort,
  onSelectAll,
}) => {
  const getSortIcon = (columnKey) => {
    if (sortConfig?.key !== columnKey) {
      return (
        <svg className="w-4 h-4 ml-1 text-gray-400 group-hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z" />
        </svg>
      );
    }

    return sortConfig?.direction === 'asc' ? (
      <svg className="w-4 h-4 ml-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
        <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM15.354 15.354a1 1 0 00-1.414-1.414l-2-2a1 1 0 111.414-1.414L14 11.586V6a1 1 0 112 0v5.586l.646-.646a1 1 0 111.414 1.414l-2 2z" />
      </svg>
    ) : (
      <svg className="w-4 h-4 ml-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
        <path d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0-5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0-5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
      </svg>
    );
  };

  const handleSort = (column) => {
    if (!column.sortable) return;

    let direction = 'asc';
    if (sortConfig?.key === column.key && sortConfig?.direction === 'asc') {
      direction = 'desc';
    }

    onSort(column.key, direction);
  };

  return (
    <thead className="bg-gray-50 border-b border-gray-200">
      <tr>
        {selectable && (
          <th className="px-6 py-3 text-left">
            <input
              type="checkbox"
              checked={selectedCount > 0 && selectedCount === totalItems}
              indeterminate={selectedCount > 0 && selectedCount < totalItems}
              onChange={onSelectAll}
              className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer"
              aria-label="Select all rows"
            />
          </th>
        )}
        {columns.map((column) => (
          <th
            key={column.key}
            className={`px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider ${
              column.sortable ? 'cursor-pointer group hover:bg-gray-100' : ''
            }`}
            onClick={() => handleSort(column)}
            scope="col"
          >
            <div className="flex items-center">
              {column.title}
              {column.sortable && getSortIcon(column.key)}
            </div>
          </th>
        ))}
      </tr>
    </thead>
  );
};

export default DataTableHeader;
