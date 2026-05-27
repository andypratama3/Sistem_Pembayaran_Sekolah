# Quick Reference Guide

## 🎯 Components at a Glance

### DataTable (Most Powerful Component)
```jsx
<DataTable
  columns={[
    { key: 'name', title: 'Name', sortable: true },
    { key: 'status', title: 'Status', render: (v) => <StatusBadge status={v} /> },
  ]}
  data={items}
  loading={loading}
  totalItems={total}
  currentPage={page}
  pageSize={10}
  onPageChange={setPage}
  onSort={(field, dir) => handleSort(field, dir)}
  selectable={true}
  onRowSelect={setSelected}
  actions={[{ label: 'Delete', onClick: handleDelete, variant: 'danger' }]}
/>
```

### TemplatesList (Template Management)
```jsx
<TemplatesList
  onEdit={(id) => navigate(`/templates/${id}/edit`)}
  onCreate={() => navigate('/templates/create')}
/>
```

### TemplateInstancesList (Instance Management)
```jsx
<TemplateInstancesList />
```

### StatusBadge (Status Indicator)
```jsx
<StatusBadge status="published" />  // Green
<StatusBadge status="draft" />      // Yellow
<StatusBadge status="approved" />   // Green
<StatusBadge status="submitted" />  // Cyan
```

## 🔧 Custom Hooks

### useApi (API Calls)
```jsx
const { loading, error, get, post, delete: del } = useApi();

// GET
const data = await get('/api/templates');

// POST
await post('/api/templates', { name: 'New' });

// DELETE
await del('/api/templates/1');
```

### useDebounce (Debounce Values)
```jsx
const debouncedSearch = useDebounce(searchInput, 500);

useEffect(() => {
  // This runs after 500ms of no typing
  fetchData(debouncedSearch);
}, [debouncedSearch]);
```

### useTableState (Manage Table State)
```jsx
const { currentPage, setCurrentPage, sortConfig, filters, updateSort } = useTableState(10);
```

## 📥 Import Patterns

### Import from Main File
```jsx
import {
  DataTable,
  StatusBadge,
  TemplatesList,
  TemplateInstancesList,
  useApi,
  useDebounce,
  useTableState
} from '@/components';
```

### Import from Specific Modules
```jsx
// Table components
import { DataTable, StatusBadge } from '@/components/Common/Table';

// Editor components
import { TemplatesList, TemplateInstancesList } from '@/components/Editor';

// Hooks
import { useApi, useDebounce, useTableState } from '@/components/hooks';
```

## 🚀 Common Patterns

### Basic Table with API
```jsx
const [data, setData] = useState([]);
const [page, setPage] = useState(1);
const { loading, get } = useApi();

useEffect(() => {
  const fetchData = async () => {
    const response = await get(`/api/items?page=${page}`);
    setData(response.data);
  };
  fetchData();
}, [page]);

return <DataTable data={data} currentPage={page} onPageChange={setPage} />;
```

### Table with Filters
```jsx
const [filters, setFilters] = useState({});

const handleFilter = (newFilters) => {
  setFilters(newFilters);
  setPage(1); // Reset to page 1
};

const columns = [
  { key: 'name', title: 'Name', sortable: true },
  { key: 'category', title: 'Category', sortable: true },
];

return (
  <>
    <select onChange={(e) => handleFilter({ category: e.target.value })}>
      <option value="">All</option>
    </select>
    <DataTable columns={columns} data={data} />
  </>
);
```

### Table with Bulk Actions
```jsx
const [selected, setSelected] = useState([]);

const handleBulkDelete = async (ids) => {
  await post('/api/items/bulk-delete', { ids });
  // Refresh data
};

const actions = [
  {
    label: 'Delete Selected',
    onClick: handleBulkDelete,
    variant: 'danger'
  }
];

return (
  <DataTable
    data={data}
    selectable={true}
    onRowSelect={setSelected}
    actions={actions}
  />
);
```

## 🎨 Styling Classes Used

### Buttons
- `bg-blue-600 hover:bg-blue-700` - Primary
- `bg-red-600 hover:bg-red-700` - Danger
- `bg-green-600 hover:bg-green-700` - Success
- `bg-yellow-600 hover:bg-yellow-700` - Warning

### Badges
- `bg-green-100 text-green-800` - Success
- `bg-red-100 text-red-800` - Danger
- `bg-yellow-100 text-yellow-800` - Warning
- `bg-blue-100 text-blue-800` - Info

### Layout
- `px-6 py-4` - Standard padding
- `rounded-lg` - Border radius
- `shadow` - Shadow effect
- `border-gray-200` - Border color

## 📱 Responsive Breakpoints

All components are mobile-responsive:
- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px

## ♿ Accessibility

All components include:
- ARIA labels on interactive elements
- Semantic HTML (buttons, tables, forms)
- Keyboard navigation
- Focus indicators
- Screen reader support

## 🔍 Column Definition Options

```javascript
{
  key: 'fieldName',              // Required: Data key
  title: 'Display Title',        // Required: Header text
  sortable: true,                // Optional: Enable sorting
  filterable: true,              // Optional: Enable filtering
  render: (value, row) => {      // Optional: Custom renderer
    return <strong>{value}</strong>;
  }
}
```

## 📊 API Response Format

All endpoints should return:

```json
{
  "data": [
    { "id": 1, "name": "Item 1" },
    { "id": 2, "name": "Item 2" }
  ],
  "total": 100,
  "per_page": 10,
  "current_page": 1,
  "last_page": 10
}
```

## ❌ Error Handling

```jsx
const { error, get } = useApi();

return (
  <>
    {error && (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <p className="text-red-800">{error}</p>
      </div>
    )}
    {/* Component */}
  </>
);
```

## 🎬 Loading States

```jsx
const { loading, get } = useApi();

return loading ? (
  <div className="flex justify-center">
    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" />
  </div>
) : (
  <DataTable data={data} />
);
```

## 🔄 Pagination Props

```jsx
<DataTable
  totalItems={100}      // Total items across all pages
  currentPage={1}       // Current page (1-indexed)
  pageSize={10}         // Items per page
  onPageChange={setPage} // Callback when page changes
/>
```

## 🎯 Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| Data not sorting | Add `sortable: true` to column |
| Pagination not working | Make sure `totalItems` is correct |
| CSRF error | Add `<meta name="csrf-token" content="{{ csrf_token() }}">` to layout |
| Styling not applying | Check Tailwind CSS is configured in tailwind.config.js |
| Components not importing | Use correct import path from `@/components` |
| Selection not working | Add `selectable={true}` to DataTable |
| API not called | Check endpoint exists and returns correct format |

## 📚 Documentation Reference

- **Main Docs**: `README.md`
- **Component Details**: `COMPONENT_DOCS.md`
- **Usage Examples**: `EXAMPLES.jsx`
- **Blade Integration**: `BLADE_INTEGRATION.md`
- **File Manifest**: `FILE_MANIFEST.md`

## 🚀 Quick Deploy Checklist

- [ ] Implement all required API endpoints
- [ ] Add CSRF token meta tag to layout
- [ ] Replace Blade templates with React components
- [ ] Test filtering and sorting
- [ ] Test bulk actions
- [ ] Test pagination with large datasets
- [ ] Test on mobile
- [ ] Test keyboard navigation
- [ ] Set up error monitoring
- [ ] Deploy to production

---

**Ready to use!** Start with one component and expand from there.
