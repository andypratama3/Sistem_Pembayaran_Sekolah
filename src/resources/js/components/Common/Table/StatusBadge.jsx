/**
 * StatusBadge Component
 * Displays status with appropriate color coding
 * 
 * @component
 * @param {string} status - The status value
 * @param {Object} variants - Custom status-to-color mappings
 * @returns {JSX.Element}
 */
const StatusBadge = ({ status, variants = {} }) => {
  const defaultVariants = {
    published: 'bg-green-100 text-green-800',
    active: 'bg-green-100 text-green-800',
    success: 'bg-green-100 text-green-800',
    approved: 'bg-green-100 text-green-800',
    
    draft: 'bg-yellow-100 text-yellow-800',
    pending: 'bg-yellow-100 text-yellow-800',
    warning: 'bg-yellow-100 text-yellow-800',
    
    global: 'bg-blue-100 text-blue-800',
    info: 'bg-cyan-100 text-cyan-800',
    submitted: 'bg-cyan-100 text-cyan-800',
    light: 'bg-gray-100 text-gray-800',
    
    inactive: 'bg-gray-100 text-gray-800',
    archived: 'bg-gray-100 text-gray-800',
    
    error: 'bg-red-100 text-red-800',
    danger: 'bg-red-100 text-red-800',
    rejected: 'bg-red-100 text-red-800',
  };

  const allVariants = { ...defaultVariants, ...variants };
  const colorClass = allVariants[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';

  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}`}>
      {status}
    </span>
  );
};

export default StatusBadge;
