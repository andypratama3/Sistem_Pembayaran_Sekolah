/**
 * DataTableCell Component
 * Renders individual table cells with custom renderers
 * 
 * @component
 * @param {*} value - The cell value
 * @param {Object} column - Column definition
 * @param {Object} row - The entire row data
 * @returns {JSX.Element}
 */
const DataTableCell = ({ value, column, row }) => {
  // If custom render function provided, use it
  if (column.render) {
    return <>{column.render(value, row)}</>;
  }

  // Handle null/undefined values
  if (value === null || value === undefined) {
    return <span className="text-gray-400">—</span>;
  }

  // Handle boolean values
  if (typeof value === 'boolean') {
    return (
      <span className={value ? 'text-green-600 font-medium' : 'text-gray-400'}>
        {value ? 'Yes' : 'No'}
      </span>
    );
  }

  // Handle arrays (join with comma)
  if (Array.isArray(value)) {
    return <span>{value.join(', ')}</span>;
  }

  // Handle objects (stringify)
  if (typeof value === 'object') {
    return <span>{JSON.stringify(value)}</span>;
  }

  // Default: return as string
  return <span>{String(value)}</span>;
};

export default DataTableCell;
