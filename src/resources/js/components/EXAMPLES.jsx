/**
 * EXAMPLE USAGE GUIDE
 * 
 * This file demonstrates how to use the new React components
 */

// ============================================================================
// EXAMPLE 1: Simple DataTable Usage
// ============================================================================

import React, { useState, useEffect } from 'react';
import { DataTable, StatusBadge } from '@/components/Common/Table';
import { useApi } from '@/components/hooks';

export function SimpleTableExample() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const { get } = useApi();

  const columns = [
    { key: 'name', title: 'Name', sortable: true },
    { key: 'email', title: 'Email', sortable: true },
    { 
      key: 'status', 
      title: 'Status', 
      render: (value) => <StatusBadge status={value} />
    },
  ];

  useEffect(() => {
    fetchData();
  }, [currentPage]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const response = await get(`/api/users?page=${currentPage}`);
      setData(response.data);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <DataTable
      columns={columns}
      data={data}
      loading={loading}
      totalItems={100}
      currentPage={currentPage}
      pageSize={10}
      onPageChange={setCurrentPage}
    />
  );
}

// ============================================================================
// EXAMPLE 2: DataTable with Selection and Bulk Actions
// ============================================================================

export function SelectableTableExample() {
  const [data, setData] = useState([]);
  const [selectedIds, setSelectedIds] = useState([]);
  const { post } = useApi();

  const handleBulkDelete = async (ids) => {
    try {
      await post('/api/users/bulk-delete', { ids });
      // Refresh data
      setSelectedIds([]);
    } catch (error) {
      console.error(error);
    }
  };

  const columns = [
    { key: 'name', title: 'Name', sortable: true },
    { key: 'email', title: 'Email', sortable: true },
  ];

  const actions = [
    {
      label: 'Delete Selected',
      onClick: handleBulkDelete,
      variant: 'danger',
    },
  ];

  return (
    <DataTable
      columns={columns}
      data={data}
      selectable={true}
      onRowSelect={setSelectedIds}
      actions={actions}
    />
  );
}

// ============================================================================
// EXAMPLE 3: Custom Cell Renderers
// ============================================================================

export function CustomRenderersExample() {
  const [data, setData] = useState([]);

  const columns = [
    { 
      key: 'id', 
      title: 'ID',
      render: (value) => <strong>#{value}</strong>
    },
    { 
      key: 'name', 
      title: 'Name',
      render: (value) => <span className="font-semibold">{value}</span>
    },
    { 
      key: 'created_at', 
      title: 'Created', 
      render: (value) => new Date(value).toLocaleDateString()
    },
    { 
      key: 'actions',
      title: 'Actions',
      render: (_, row) => (
        <div className="space-x-2">
          <button onClick={() => handleEdit(row.id)} className="text-blue-600">
            Edit
          </button>
          <button onClick={() => handleDelete(row.id)} className="text-red-600">
            Delete
          </button>
        </div>
      )
    },
  ];

  const handleEdit = (id) => console.log('Edit', id);
  const handleDelete = (id) => console.log('Delete', id);

  return <DataTable columns={columns} data={data} />;
}

// ============================================================================
// EXAMPLE 4: Using TemplatesList Component
// ============================================================================

import { TemplatesList } from '@/components/Editor';

export function TemplatesPageExample() {
  const handleEdit = (templateId) => {
    window.location.href = `/dashboard/templates/${templateId}/edit`;
  };

  const handleCreate = () => {
    window.location.href = '/dashboard/templates/create';
  };

  return (
    <div className="bg-gray-100 min-h-screen py-12 px-4">
      <TemplatesList 
        onEdit={handleEdit}
        onCreate={handleCreate}
      />
    </div>
  );
}

// ============================================================================
// EXAMPLE 5: Using TemplateInstancesList Component
// ============================================================================

import { TemplateInstancesList } from '@/components/Editor';

export function TemplateInstancesPageExample() {
  return (
    <div className="bg-gray-100 min-h-screen py-12 px-4">
      <TemplateInstancesList />
    </div>
  );
}

// ============================================================================
// EXAMPLE 6: Advanced Filtering and Sorting
// ============================================================================

export function AdvancedTableExample() {
  const [data, setData] = useState([]);
  const [filters, setFilters] = useState({});
  const [sort, setSort] = useState(null);
  const [page, setPage] = useState(1);

  const { get } = useApi();

  useEffect(() => {
    fetchData();
  }, [filters, sort, page]);

  const fetchData = async () => {
    const params = new URLSearchParams({
      page,
      sort_by: sort?.field || '',
      sort_order: sort?.direction || '',
      ...filters,
    });

    const response = await get(`/api/items?${params}`);
    setData(response.data);
  };

  const handleSort = (field, direction) => {
    setSort({ field, direction });
    setPage(1);
  };

  const handleFilter = (newFilters) => {
    setFilters(newFilters);
    setPage(1);
  };

  const columns = [
    { key: 'name', title: 'Name', sortable: true },
    { key: 'category', title: 'Category', sortable: true },
  ];

  return (
    <div className="space-y-4">
      <div className="bg-white p-4 rounded-lg">
        <input
          type="text"
          placeholder="Filter by name..."
          onChange={(e) => handleFilter({ search: e.target.value })}
          className="w-full px-4 py-2 border rounded-lg"
        />
      </div>
      
      <DataTable
        columns={columns}
        data={data}
        onSort={handleSort}
        onPageChange={setPage}
      />
    </div>
  );
}

// ============================================================================
// EXAMPLE 7: Error Handling
// ============================================================================

export function ErrorHandlingExample() {
  const [data, setData] = useState([]);
  const [error, setError] = useState(null);
  const { loading, error: apiError, get } = useApi();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const response = await get('/api/items');
      setData(response.data);
      setError(null);
    } catch (err) {
      setError(err.message || 'Failed to load data');
    }
  };

  return (
    <div>
      {error && (
        <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p className="text-red-800">{error}</p>
          <button 
            onClick={fetchData}
            className="mt-2 text-red-600 hover:text-red-700 font-medium"
          >
            Try Again
          </button>
        </div>
      )}

      {apiError && (
        <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p className="text-red-800">{apiError}</p>
        </div>
      )}

      {loading && <div>Loading...</div>}

      {data.length > 0 && <DataTable columns={[]} data={data} />}
    </div>
  );
}

// ============================================================================
// EXAMPLE 8: Integration with Blade Template
// ============================================================================

/*
In your Blade file:

<div id="react-root"></div>

<script>
  import React from 'react';
  import ReactDOM from 'react-dom';
  import { TemplatesList } from '@/components/Editor';

  document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('react-root');
    
    ReactDOM.render(
      <TemplatesList
        onEdit={(id) => window.location.href = `/dashboard/templates/${id}/edit`}
        onCreate={() => window.location.href = '/dashboard/templates/create'}
      />,
      root
    );
  });
</script>
*/

// ============================================================================
// EXAMPLE 9: Using Hooks Independently
// ============================================================================

import { useApi, useDebounce, useTableState } from '@/components/hooks';

export function HooksExample() {
  const [searchInput, setSearchInput] = useState('');
  const debouncedSearch = useDebounce(searchInput, 500);
  const tableState = useTableState(10);
  const { loading, error, get } = useApi();

  useEffect(() => {
    // Fetch data whenever debounced search changes
    fetchData();
  }, [debouncedSearch]);

  const fetchData = async () => {
    try {
      const response = await get(`/api/search?q=${debouncedSearch}`);
      // Handle response
    } catch (err) {
      // Handle error
    }
  };

  return (
    <div>
      <input
        value={searchInput}
        onChange={(e) => setSearchInput(e.target.value)}
        placeholder="Search (debounced)..."
      />
      {loading && <div>Searching...</div>}
      {error && <div>Error: {error}</div>}
    </div>
  );
}

// ============================================================================
// EXAMPLE 10: Mounting in React Application
// ============================================================================

import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';

export default function App() {
  return (
    <Router>
      <div className="bg-gray-100 min-h-screen">
        <Switch>
          <Route path="/templates" component={TemplatesPageExample} />
          <Route path="/templates/instances" component={TemplateInstancesPageExample} />
        </Switch>
      </div>
    </Router>
  );
}

/**
 * SUMMARY OF KEY POINTS:
 * 
 * 1. DataTable is the foundation - use it for any tabular data
 * 2. Custom render functions allow flexible cell content
 * 3. useApi hook handles all API calls with CSRF tokens
 * 4. useDebounce prevents excessive API calls
 * 5. useTableState manages complex table state
 * 6. StatusBadge provides consistent status indicators
 * 7. All components are fully responsive
 * 8. Error and loading states are handled gracefully
 * 9. Bulk actions work seamlessly with row selection
 * 10. Tailwind CSS classes provide professional styling
 */
