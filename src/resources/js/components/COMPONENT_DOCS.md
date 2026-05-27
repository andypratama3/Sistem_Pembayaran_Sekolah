# React Components Documentation

## Overview

This directory contains modern React components built to replace Laravel Blade templates. The components are designed to be fully customizable, reusable, and follow React best practices with Tailwind CSS styling.

## Directory Structure

```
/src/resources/js/components/
├── Common/
│   ├── Table/
│   │   ├── DataTable.jsx           # Main customizable table component
│   │   ├── DataTableHeader.jsx      # Table header with sorting
│   │   ├── DataTableRow.jsx         # Individual row component
│   │   ├── DataTableCell.jsx        # Cell renderer
│   │   ├── DataTablePagination.jsx  # Pagination controls
│   │   ├── StatusBadge.jsx          # Status badge component
│   │   └── index.js                 # Barrel export
│   └── index.js
├── Editor/
│   ├── TemplatesList.jsx            # Templates management page
│   ├── TemplateInstancesList.jsx    # Template instances management page
│   └── index.js                     # Barrel export
├── hooks/
│   ├── useApi.js                    # API, debounce, and table state hooks
│   └── index.js                     # Barrel export
└── TemplateEditor/                  # Existing template editor
```

## Components

### 1. DataTable Component

A fully customizable table component with all essential features for displaying tabular data.

#### Features
- **Dynamic Columns**: Define columns with custom render functions
- **Sorting**: Click headers to sort (supports ascending/descending)
- **Filtering**: Pass filter values to customize displayed data
- **Pagination**: Built-in pagination with configurable page sizes
- **Row Selection**: Checkbox for selecting rows with "select all" option
- **Bulk Actions**: Execute actions on selected rows
- **Loading States**: Visual feedback while data loads
- **Search**: Optional search input for filtering
- **Custom Cell Renderers**: Render custom content in cells
- **Responsive Design**: Mobile-friendly with Tailwind CSS

#### Usage

```jsx
import { DataTable } from '@/components/Common/Table';

<DataTable
  columns={[
    {
      key: 'name',
      title: 'Name',
      sortable: true,
      render: (value, row) => <strong>{value}</strong>
    },
    {
      key: 'email',
      title: 'Email',
      sortable: true
    },
    {
      key: 'status',
      title: 'Status',
      sortable: true,
      render: (value) => <StatusBadge status={value} />
    }
  ]}
  data={users}
  loading={isLoading}
  totalItems={totalUsers}
  currentPage={page}
  pageSize={10}
  onPageChange={(newPage) => setPage(newPage)}
  onSort={(field, direction) => handleSort(field, direction)}
  onRowSelect={(selectedIds) => setSelected(selectedIds)}
  selectable={true}
  actions={[
    {
      label: 'Delete Selected',
      onClick: (ids) => handleDelete(ids),
      variant: 'danger'
    }
  ]}
/>
```

#### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | `Array<Object>` | Required | Column definitions |
| `data` | `Array<Object>` | `[]` | Table data |
| `loading` | `boolean` | `false` | Loading state |
| `totalItems` | `number` | `0` | Total items across all pages |
| `currentPage` | `number` | `1` | Current page number |
| `pageSize` | `number` | `10` | Items per page |
| `onPageChange` | `function` | `() => {}` | Page change callback |
| `onSort` | `function` | `() => {}` | Sort change callback |
| `onFilter` | `function` | `() => {}` | Filter change callback |
| `onRowSelect` | `function` | `() => {}` | Row selection callback |
| `searchable` | `boolean` | `false` | Show search input |
| `selectable` | `boolean` | `false` | Enable row selection |
| `actions` | `Array<Object>` | `[]` | Bulk actions |
| `emptyMessage` | `string` | `'No data available'` | Empty state message |
| `getRowId` | `function` | `(row, idx) => row.id` | Function to get unique row ID |

#### Column Definition

```javascript
{
  key: 'fieldName',           // Required: Data key to display
  title: 'Column Title',      // Required: Display title
  sortable: true,             // Optional: Enable sorting
  filterable: true,           // Optional: Enable filtering
  render: (value, row) => {   // Optional: Custom renderer
    return <span>{value}</span>;
  }
}
```

#### Bulk Action Definition

```javascript
{
  label: 'Action Name',
  onClick: (selectedIds) => {
    // Handle action
  },
  variant: 'danger' // 'danger', 'warning', 'success', 'primary'
}
```

### 2. StatusBadge Component

Displays status with appropriate color coding.

#### Usage

```jsx
import { StatusBadge } from '@/components/Common/Table';

<StatusBadge status="published" />
<StatusBadge status="draft" />
<StatusBadge status="approved" />
```

#### Supported Statuses

| Status | Color |
|--------|-------|
| `published`, `active`, `success`, `approved` | Green |
| `draft`, `pending`, `warning` | Yellow |
| `global`, `info`, `submitted` | Cyan/Blue |
| `inactive`, `archived` | Gray |
| `error`, `danger`, `rejected` | Red |

### 3. TemplatesList Component

Manages template creation, editing, deletion, and filtering.

#### Features
- View all templates with sorting and filtering
- Filter by category and status
- Create new templates
- Edit existing templates
- Delete single or bulk templates
- See field count for each template
- View created by information
- Status indicators

#### Usage

```jsx
import { TemplatesList } from '@/components/Editor';

<TemplatesList
  onEdit={(templateId) => {
    // Navigate to edit page
    window.location.href = `/dashboard/templates/${templateId}/edit`;
  }}
  onCreate={() => {
    // Navigate to create page
    window.location.href = '/dashboard/templates/create';
  }}
/>
```

#### API Endpoints Required

- `GET /api/templates` - List templates with pagination
- `GET /api/template-categories` - List template categories
- `DELETE /api/templates/{id}` - Delete single template
- `POST /api/templates/bulk-delete` - Bulk delete templates

#### Response Format

```json
{
  "data": [
    {
      "id": 1,
      "name": "Report Card",
      "category_name": "Academic",
      "fields_count": 12,
      "created_by_name": "John Doe",
      "status": "published"
    }
  ],
  "total": 10
}
```

### 4. TemplateInstancesList Component

Manages template instances with bulk operations.

#### Features
- View all template instances with pagination
- Filter by template and status
- See student name and period information
- Relative time display ("2 hours ago", etc.)
- Bulk submit instances
- Bulk generate PDFs
- Bulk delete instances
- Status indicators

#### Usage

```jsx
import { TemplateInstancesList } from '@/components/Editor';

<TemplateInstancesList />
```

#### API Endpoints Required

- `GET /api/template-instances` - List instances with pagination
- `GET /api/templates` - List templates for filter dropdown
- `POST /api/template-instances/bulk-action` - Bulk actions (submit, delete)
- `POST /api/template-instances/bulk-generate` - Generate PDFs for instances

#### Response Format

```json
{
  "data": [
    {
      "id": 1,
      "template_name": "Report Card",
      "student_name": "Alice Johnson",
      "period": "Semester 1",
      "status": "draft",
      "created_by_name": "John Doe",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "total": 25
}
```

## Custom Hooks

### useApi

Hook for making API requests with automatic CSRF token handling.

```jsx
import { useApi } from '@/components/hooks';

const MyComponent = () => {
  const { loading, error, get, post, delete: del } = useApi();

  const fetchData = async () => {
    try {
      const data = await get('/api/templates');
      console.log(data);
    } catch (err) {
      console.error(err);
    }
  };

  return (
    <div>
      {error && <div className="error">{error}</div>}
      <button onClick={fetchData} disabled={loading}>
        {loading ? 'Loading...' : 'Fetch'}
      </button>
    </div>
  );
};
```

### useDebounce

Hook for debouncing values (useful for search inputs).

```jsx
import { useDebounce } from '@/components/hooks';

const SearchComponent = () => {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 500);

  useEffect(() => {
    // API call with debouncedSearch
  }, [debouncedSearch]);

  return <input value={search} onChange={(e) => setSearch(e.target.value)} />;
};
```

### useTableState

Hook for managing table state (pagination, sorting, filtering).

```jsx
import { useTableState } from '@/components/hooks';

const MyTable = () => {
  const {
    currentPage,
    setCurrentPage,
    pageSize,
    sortConfig,
    updateSort,
    filters,
    updateFilters,
    resetPagination
  } = useTableState(10);

  return (
    // Use these values to manage table state
  );
};
```

## Styling

All components use **Tailwind CSS** exclusively. No CSS files are needed. The design follows modern UI principles with:

- Consistent color scheme (blue primary, green success, yellow warning, red danger)
- Responsive layout (mobile-first)
- Proper spacing and typography
- Hover and focus states
- Smooth transitions

## Integration with Blade Templates

### Mounting in Blade

```blade
<!-- In your Blade template -->
<div id="templates-list-app"></div>

<script>
  import React from 'react';
  import ReactDOM from 'react-dom';
  import { TemplatesList } from '@/components/Editor';

  document.addEventListener('DOMContentLoaded', () => {
    ReactDOM.render(
      <TemplatesList
        onEdit={(id) => window.location.href = `/dashboard/templates/${id}/edit`}
        onCreate={() => window.location.href = '/dashboard/templates/create'}
      />,
      document.getElementById('templates-list-app')
    );
  });
</script>
```

### With Inertia.js

```jsx
// In a Route component
import { TemplatesList } from '@/components/Editor';

export default function TemplatesPage() {
  return (
    <div className="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
      <TemplatesList
        onEdit={(id) => route('templates.edit', id)}
        onCreate={() => route('templates.create')}
      />
    </div>
  );
}
```

## Accessibility Features

All components include:

- Proper ARIA labels
- Keyboard navigation support
- Semantic HTML
- Focus management
- Screen reader compatibility
- Color contrast compliance

## Error Handling

API errors are caught and displayed to users:

```jsx
{dataError && (
  <div className="bg-red-50 border border-red-200 rounded-lg p-4">
    <p className="text-sm text-red-800">{dataError}</p>
  </div>
)}
```

## Loading States

Components show loading indicators while fetching data:

```jsx
{loading && (
  <div className="flex justify-center items-center">
    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
  </div>
)}
```

## Empty States

When no data is available:

```jsx
{data.length === 0 && (
  <div className="text-center text-gray-500">
    <svg className="mx-auto h-12 w-12 text-gray-400 mb-3" />
    <p className="text-sm font-medium">No data available</p>
  </div>
)}
```

## Performance Optimization

- Uses `useCallback` for stable function references
- Debounces search/filter inputs
- Pagination reduces DOM elements
- Memoization of components where appropriate

## Best Practices

1. **Always provide a unique `id` property** in your data rows, or use `getRowId` prop
2. **Use custom render functions** for complex cell content
3. **Debounce search inputs** to avoid excessive API calls
4. **Clear selections on page change** to prevent state issues
5. **Handle errors gracefully** with user-friendly messages
6. **Test with different data sizes** to ensure performance

## Extending Components

### Custom DataTable Example

```jsx
import DataTable from '@/components/Common/Table/DataTable';

export const CustomTable = (props) => {
  const columns = [
    { key: 'id', title: 'ID', sortable: true },
    { 
      key: 'name', 
      title: 'Name', 
      sortable: true,
      render: (value) => <strong>{value}</strong>
    },
    // ... more columns
  ];

  return (
    <DataTable
      columns={columns}
      {...props}
    />
  );
};
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

All components are part of the ProductSchool application.

## Contact

For questions or issues, contact the development team.
