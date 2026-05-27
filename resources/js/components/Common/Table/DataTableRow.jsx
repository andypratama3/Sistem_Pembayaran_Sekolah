import DataTableCell from './DataTableCell';

/**
 * DataTableRow Component
 * Renders individual table rows with cells
 * 
 * @component
 * @param {Object} row - Row data
 * @param {Array} columns - Column definitions
 * @param {boolean} selectable - Whether rows are selectable
 * @param {boolean} isSelected - Whether this row is selected
 * @param {function} onSelect - Callback when row is selected
 * @param {string} rowId - Unique identifier for the row
 * @returns {JSX.Element}
 */
const DataTableRow = ({
  row,
  columns,
  selectable,
  isSelected,
  onSelect,
  rowId,
}) => {
  return (
    <tr className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
      {selectable && (
        <td className="px-6 py-4 whitespace-nowrap">
          <input
            type="checkbox"
            checked={isSelected}
            onChange={() => onSelect(rowId)}
            className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer"
            aria-label={`Select row`}
          />
        </td>
      )}
      {columns.map((column) => (
        <td
          key={`${rowId}-${column.key}`}
          className="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
        >
          <DataTableCell value={row[column.key]} column={column} row={row} />
        </td>
      ))}
    </tr>
  );
};

export default DataTableRow;
