import { useState, useEffect, useCallback } from 'react';
import DataTable from '../Common/Table/DataTable';
import StatusBadge from '../Common/Table/StatusBadge';
import { useApi } from '../hooks/useApi';

/**
 * TemplatesList Component
 * Displays a list of templates with filtering, sorting, and bulk actions
 * Replaces /dashboard/templates/index.blade.php
 */
const TemplatesList = ({ onEdit, onCreate }) => {
  const [templates, setTemplates] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize] = useState(10);
  const [sortConfig, setSortConfig] = useState(null);
  const [filters, setFilters] = useState({});
  const [categories, setCategories] = useState([]);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [selectedIds, setSelectedIds] = useState([]);

  const { loading: dataLoading, error: dataError, get, post, del } = useApi();
  const { loading: categoryLoading, get: getCategories } = useApi();

  // Fetch templates
  const fetchTemplates = useCallback(async () => {
    try {
      const params = new URLSearchParams({
        page: currentPage,
        per_page: pageSize,
        ...(sortConfig && { sort_by: sortConfig.field, sort_order: sortConfig.direction }),
        ...filters,
      });

      const response = await get(`/api/templates?${params.toString()}`);
      setTemplates(response.data || []);
      setTotalItems(response.total || 0);
    } catch (error) {
      console.error('Failed to fetch templates:', error);
      // Error is handled by the hook
    }
  }, [currentPage, pageSize, sortConfig, filters, get]);

  // Fetch categories
  const fetchCategories = useCallback(async () => {
    try {
      const response = await getCategories('/api/template-categories');
      setCategories(response.data || []);
    } catch (error) {
      console.error('Failed to fetch categories:', error);
    }
  }, [getCategories]);

  // Initial fetch
  useEffect(() => {
    fetchCategories();
  }, [fetchCategories]);

  useEffect(() => {
    fetchTemplates();
  }, [fetchTemplates]);

  // Handle delete single template
  const handleDeleteClick = (template) => {
    setDeleteTarget(template);
    setShowDeleteConfirm(true);
  };

  const confirmDelete = async () => {
    if (!deleteTarget) return;

    try {
      await del(`/api/templates/${deleteTarget.id}`);
      setShowDeleteConfirm(false);
      setDeleteTarget(null);
      fetchTemplates();
      showNotification('Template deleted successfully', 'success');
    } catch (error) {
      showNotification('Failed to delete template', 'error');
    }
  };

  // Handle bulk delete
  const handleBulkDelete = async (ids) => {
    if (!confirm(`Delete ${ids.length} template(s)?`)) return;

    try {
      await post('/api/templates/bulk-delete', { ids });
      setSelectedIds([]);
      fetchTemplates();
      showNotification(`${ids.length} template(s) deleted successfully`, 'success');
    } catch (error) {
      showNotification('Failed to delete templates', 'error');
    }
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

  // Show notification (you can integrate with toast library)
  const showNotification = (message, type) => {
    // TODO: Integrate with toast notification system
    console.log(`[${type}] ${message}`);
  };

  // Column definitions
  const columns = [
    {
      key: 'name',
      title: 'Name',
      sortable: true,
      render: (value, row) => (
        <div className="text-sm font-medium text-gray-900">{value}</div>
      ),
    },
    {
      key: 'category_name',
      title: 'Category',
      sortable: true,
      render: (value) => (
        <span className="text-sm text-gray-600">{value || '—'}</span>
      ),
    },
    {
      key: 'fields_count',
      title: 'Fields',
      sortable: true,
      render: (value) => (
        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          {value || 0}
        </span>
      ),
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
      key: 'status',
      title: 'Status',
      sortable: true,
      render: (value) => <StatusBadge status={value} />,
    },
    {
      key: 'actions',
      title: 'Actions',
      sortable: false,
      render: (_, row) => (
        <div className="flex gap-2">
          <button
            onClick={() => onEdit?.(row.id)}
            className="text-blue-600 hover:text-blue-700 text-sm font-medium"
            title="Edit template"
          >
            Edit
          </button>
          <button
            onClick={() => {
              // Preview functionality
              window.open(`/dashboard/templates/${row.id}/preview`, '_blank');
            }}
            className="text-green-600 hover:text-green-700 text-sm font-medium"
            title="Preview template"
          >
            Preview
          </button>
          <button
            onClick={() => handleDeleteClick(row)}
            className="text-red-600 hover:text-red-700 text-sm font-medium"
            title="Delete template"
          >
            Delete
          </button>
        </div>
      ),
    },
  ];

  // Bulk actions
  const actions = [
    {
      label: 'Delete Selected',
      onClick: handleBulkDelete,
      variant: 'danger',
    },
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Templates</h1>
          <p className="mt-1 text-sm text-gray-600">Manage your document templates</p>
        </div>
        <button
          onClick={onCreate}
          className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg className="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
          Create Template
        </button>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-lg shadow p-6 space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Category Filter */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Category
            </label>
            <select
              value={filters.category || ''}
              onChange={(e) => handleFilter({ ...filters, category: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            >
              <option value="">All Categories</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.name}
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
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="global">Global</option>
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
          data={templates}
          loading={dataLoading}
          totalItems={totalItems}
          currentPage={currentPage}
          pageSize={pageSize}
          onPageChange={setCurrentPage}
          onSort={handleSort}
          selectable={true}
          actions={actions}
          onRowSelect={setSelectedIds}
          emptyMessage="No templates found"
          getRowId={(row) => row.id}
        />
      </div>

      {/* Delete Confirmation Modal */}
      {showDeleteConfirm && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-lg p-6 max-w-sm mx-auto">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Delete Template</h3>
            <p className="text-sm text-gray-600 mb-6">
              Are you sure you want to delete "<strong>{deleteTarget?.name}</strong>"? This action cannot be undone.
            </p>
            <div className="flex gap-3 justify-end">
              <button
                onClick={() => setShowDeleteConfirm(false)}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Cancel
              </button>
              <button
                onClick={confirmDelete}
                className="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TemplatesList;
