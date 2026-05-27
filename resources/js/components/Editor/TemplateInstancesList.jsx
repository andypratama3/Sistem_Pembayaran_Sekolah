import { useState, useEffect, useCallback } from 'react';
import DataTable from '../Common/Table/DataTable';
import StatusBadge from '../Common/Table/StatusBadge';
import { useApi } from '../hooks/useApi';

/**
 * TemplateInstancesList Component
 * Displays a list of template instances with filtering, sorting, and bulk actions
 * Replaces /dashboard/templates/instances/index.blade.php
 */
const TemplateInstancesList = () => {
  const [instances, setInstances] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize] = useState(10);
  const [sortConfig, setSortConfig] = useState(null);
  const [filters, setFilters] = useState({});
  const [templates, setTemplates] = useState([]);
  const [selectedIds, setSelectedIds] = useState([]);

  const { loading: dataLoading, error: dataError, get, post } = useApi();
  const { loading: templateLoading, get: getTemplates } = useApi();

  // Fetch template instances
  const fetchInstances = useCallback(async () => {
    try {
      const params = new URLSearchParams({
        page: currentPage,
        per_page: pageSize,
        ...(sortConfig && { sort_by: sortConfig.field, sort_order: sortConfig.direction }),
        ...filters,
      });

      const response = await get(`/api/template-instances?${params.toString()}`);
      setInstances(response.data || []);
      setTotalItems(response.total || 0);
    } catch (error) {
      console.error('Failed to fetch template instances:', error);
    }
  }, [currentPage, pageSize, sortConfig, filters, get]);

  // Fetch templates for filter dropdown
  const fetchTemplates = useCallback(async () => {
    try {
      const response = await getTemplates('/api/templates?per_page=100');
      setTemplates(response.data || []);
    } catch (error) {
      console.error('Failed to fetch templates:', error);
    }
  }, [getTemplates]);

  // Initial fetch
  useEffect(() => {
    fetchTemplates();
  }, [fetchTemplates]);

  useEffect(() => {
    fetchInstances();
  }, [fetchInstances]);

  // Format relative time
  const formatRelativeTime = (dateString) => {
    if (!dateString) return '—';
    
    const date = new Date(dateString);
    const now = new Date();
    const secondsAgo = Math.floor((now - date) / 1000);

    if (secondsAgo < 60) return 'just now';
    if (secondsAgo < 3600) return `${Math.floor(secondsAgo / 60)}m ago`;
    if (secondsAgo < 86400) return `${Math.floor(secondsAgo / 3600)}h ago`;
    if (secondsAgo < 604800) return `${Math.floor(secondsAgo / 86400)}d ago`;

    return date.toLocaleDateString();
  };

  // Handle sort
  const handleSort = (field, direction) => {
    setSortConfig({ field, direction });
    setCurrentPage(1);
  };

  // Handle filter
  const handleFilter = (newFilters) => {
    setFilters(newFilters);
    setCurrentPage(1);
  };

  // Handle bulk action
  const handleBulkAction = async (action, ids) => {
    try {
      if (action === 'submit') {
        await post('/api/template-instances/bulk-action', {
          action: 'submit',
          ids,
        });
        showNotification(`${ids.length} instance(s) submitted successfully`, 'success');
      } else if (action === 'generate-pdf') {
        const response = await post('/api/template-instances/bulk-generate', { ids });
        // Trigger download
        if (response.download_url) {
          window.location.href = response.download_url;
        }
        showNotification(`Generated PDF(s) for ${ids.length} instance(s)`, 'success');
      } else if (action === 'delete') {
        if (!confirm(`Delete ${ids.length} instance(s)?`)) return;
        await post('/api/template-instances/bulk-action', {
          action: 'delete',
          ids,
        });
        showNotification(`${ids.length} instance(s) deleted successfully`, 'success');
      }

      setSelectedIds([]);
      fetchInstances();
    } catch (error) {
      showNotification(`Failed to perform action: ${error.message}`, 'error');
    }
  };

  // Show notification
  const showNotification = (message, type) => {
    console.log(`[${type}] ${message}`);
  };

  // Column definitions
  const columns = [
    {
      key: 'template_name',
      title: 'Template',
      sortable: true,
      render: (value) => (
        <div className="text-sm font-medium text-gray-900">{value}</div>
      ),
    },
    {
      key: 'student_name',
      title: 'Student',
      sortable: true,
      render: (value) => (
        <span className="text-sm text-gray-600">{value || '—'}</span>
      ),
    },
    {
      key: 'period',
      title: 'Period',
      sortable: true,
      render: (value) => (
        <span className="text-sm text-gray-600">{value || '—'}</span>
      ),
    },
    {
      key: 'status',
      title: 'Status',
      sortable: true,
      render: (value) => {
        const statusMap = {
          draft: 'light',
          submitted: 'info',
          approved: 'success',
        };
        return <StatusBadge status={value} variants={statusMap} />;
      },
    },
    {
      key: 'created_by_name',
      title: 'Created By',
      sortable: true,
      render: (value) => (
        <span className="text-sm text-gray-600">{value || '—'}</span>
      ),
    },
    {
      key: 'created_at',
      title: 'Date',
      sortable: true,
      render: (value) => (
        <span className="text-sm text-gray-600">{formatRelativeTime(value)}</span>
      ),
    },
  ];

  // Bulk actions
  const actions = [
    {
      label: 'Submit',
      onClick: (ids) => handleBulkAction('submit', ids),
      variant: 'success',
    },
    {
      label: 'Generate PDFs',
      onClick: (ids) => handleBulkAction('generate-pdf', ids),
      variant: 'primary',
    },
    {
      label: 'Delete',
      onClick: (ids) => handleBulkAction('delete', ids),
      variant: 'danger',
    },
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Template Instances</h1>
        <p className="mt-1 text-sm text-gray-600">Manage template instances and their status</p>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-lg shadow p-6 space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Template Filter */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Template
            </label>
            <select
              value={filters.template_id || ''}
              onChange={(e) => handleFilter({ ...filters, template_id: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            >
              <option value="">All Templates</option>
              {templates.map((template) => (
                <option key={template.id} value={template.id}>
                  {template.name}
                </option>
              ))}
            </select>
          </div>

          {/* Status Filter */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Status
            </label>
            <select
              value={filters.status || ''}
              onChange={(e) => handleFilter({ ...filters, status: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            >
              <option value="">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="submitted">Submitted</option>
              <option value="approved">Approved</option>
            </select>
          </div>
        </div>
      </div>

      {/* Error Message */}
      {dataError && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <p className="text-sm text-red-800">{dataError}</p>
        </div>
      )}

      {/* Data Table */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        <DataTable
          columns={columns}
          data={instances}
          loading={dataLoading}
          totalItems={totalItems}
          currentPage={currentPage}
          pageSize={pageSize}
          onPageChange={setCurrentPage}
          onSort={handleSort}
          selectable={true}
          actions={actions}
          onRowSelect={setSelectedIds}
          emptyMessage="No template instances found"
          getRowId={(row) => row.id}
        />
      </div>

      {/* Info Box */}
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p className="text-sm text-blue-800">
          <strong>Tip:</strong> Select instances to perform bulk actions like submitting, generating PDFs, or deleting.
        </p>
      </div>
    </div>
  );
};

export default TemplateInstancesList;
