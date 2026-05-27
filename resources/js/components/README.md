# React Components Library

Complete React component system for the ProductSchool application, replacing Blade templates with modern, reusable React components.

## 📁 Directory Structure

```
components/
├── Common/
│   ├── Table/                          # Reusable table components
│   │   ├── DataTable.jsx               # Main table component (7.4KB)
│   │   ├── DataTableHeader.jsx         # Header with sorting (3.3KB)
│   │   ├── DataTableRow.jsx            # Row renderer (1.4KB)
│   │   ├── DataTableCell.jsx           # Cell renderer (1.1KB)
│   │   ├── DataTablePagination.jsx     # Pagination controls (4.3KB)
│   │   ├── StatusBadge.jsx             # Status badge (1.4KB)
│   │   └── index.js                    # Barrel export
│   └── index.js
├── Editor/
│   ├── TemplatesList.jsx               # Templates management (10.3KB)
│   ├── TemplateInstancesList.jsx       # Instances management (9KB)
│   └── index.js                        # Barrel export
├── hooks/
│   ├── useApi.js                       # API & utility hooks (3.2KB)
│   └── index.js                        # Barrel export
├── TemplateEditor/                     # Existing template editor
├── index.js                            # Main export file
├── COMPONENT_DOCS.md                   # Component documentation
├── EXAMPLES.jsx                        # Usage examples
├── BLADE_INTEGRATION.md                # Blade integration guide
└── README.md                           # This file
```

## 🚀 Quick Start

### Installation

The components are part of the ProjectSchool application. No additional installation needed.

### Basic Usage

```jsx
import { DataTable, StatusBadge } from '@/components';
import { TemplatesList, TemplateInstancesList } from '@/components/Editor';

// Use components
<TemplatesList onEdit={handleEdit} onCreate={handleCreate} />
```

## 📦 Components Overview

### Common Table Components

#### DataTable
The foundation component for displaying tabular data with:
- Dynamic columns with custom renderers
- Sorting and filtering
- Pagination
- Row selection with bulk actions
- Loading and empty states
- Full responsiveness

**File**: `Common/Table/DataTable.jsx` (7.4KB)  
**Dependencies**: React hooks, child components

#### StatusBadge
Displays status with semantic color coding:
- Multiple built-in status types
- Customizable variants
- Tailwind CSS styling

**File**: `Common/Table/StatusBadge.jsx` (1.4KB)  
**Dependencies**: None (pure component)

### Editor Components

#### TemplatesList
Template management interface replacing `/dashboard/templates/index.blade.php`:
- List templates with filtering and sorting
- Filter by category and status
- Create, edit, delete templates
- Bulk delete operations
- Field count display

**File**: `Editor/TemplatesList.jsx` (10.3KB)  
**Dependencies**: DataTable, StatusBadge, useApi hook  
**API Endpoints**:
- `GET /api/templates`
- `GET /api/template-categories`
- `DELETE /api/templates/{id}`
- `POST /api/templates/bulk-delete`

#### TemplateInstancesList
Template instances management interface replacing `/dashboard/templates/instances/index.blade.php`:
- List template instances with pagination
- Filter by template and status
- Submit instances
- Generate PDFs
- Delete instances
- Relative time display

**File**: `Editor/TemplateInstancesList.jsx` (9KB)  
**Dependencies**: DataTable, StatusBadge, useApi hook  
**API Endpoints**:
- `GET /api/template-instances`
- `GET /api/templates`
- `POST /api/template-instances/bulk-action`
- `POST /api/template-instances/bulk-generate`

### Custom Hooks

#### useApi
Handle API requests with automatic CSRF token injection:
- `get()` - GET request
- `post()` - POST request
- `put()` - PUT request
- `delete()` - DELETE request
- Automatic error handling
- Loading state management

**File**: `hooks/useApi.js` (3.2KB)

#### useDebounce
Debounce values to prevent excessive API calls:
- Customizable delay (default: 500ms)
- Perfect for search inputs

**File**: `hooks/useApi.js`

#### useTableState
Manage complex table state:
- Pagination
- Sorting
- Filtering
- State reset functions

**File**: `hooks/useApi.js`

## 📊 Statistics

| Metric | Value |
|--------|-------|
| Total Components | 13 |
| Table Components | 6 |
| Editor Components | 2 |
| Custom Hooks | 3 |
| Documentation Files | 4 |
| Total Size | ~70KB (uncompressed) |
| CSS Framework | Tailwind CSS |
| React Version | 16.8+ (Hooks) |

## 🎨 Styling

All components use **Tailwind CSS** exclusively:
- No CSS files needed
- Responsive design (mobile-first)
- Consistent color scheme
- Professional appearance
- Accessibility features included

### Color Scheme

- **Primary**: Blue (#3B82F6)
- **Success**: Green (#10B981)
- **Warning**: Yellow (#F59E0B)
- **Danger**: Red (#EF4444)
- **Info**: Cyan (#06B6D4)
- **Neutral**: Gray (various shades)

## 🔌 API Integration

All components use the `useApi` hook which:
- Automatically includes CSRF tokens
- Sets proper Content-Type headers
- Handles authentication
- Provides error handling
- Manages loading states

### Required API Response Format

```json
{
  "data": [...],
  "total": 100,
  "per_page": 10,
  "current_page": 1,
  "last_page": 10
}
```

## ♿ Accessibility

All components include:
- Semantic HTML
- ARIA labels
- Keyboard navigation
- Focus management
- Screen reader support
- Color contrast compliance

## 📖 Documentation

- **COMPONENT_DOCS.md**: Detailed component documentation
- **EXAMPLES.jsx**: 10 usage examples
- **BLADE_INTEGRATION.md**: Integration with Blade templates
- **README.md**: This file

## 🛠 Development

### Import Styles

```jsx
// Import individual component
import DataTable from '@/components/Common/Table/DataTable';

// Import from barrel export
import { DataTable, StatusBadge } from '@/components/Common/Table';

// Import from main export
import { TemplatesList, TemplateInstancesList } from '@/components';

// Import hooks
import { useApi, useDebounce } from '@/components/hooks';
```

### Creating New Components

Follow the existing patterns:
1. Use React hooks (useState, useEffect, useCallback)
2. Use Tailwind CSS for styling
3. Export as default
4. Add JSDoc comments
5. Support accessibility features
6. Add to barrel exports

### Testing

Components are designed to be easily testable:
- Pure components where possible
- Hooks are testable with React Testing Library
- Mock API calls with useApi hook

## 🚀 Production Deployment

### Optimization Checklist

- ✅ Code splitting (lazy load components)
- ✅ Tree-shaking (unused code removed)
- ✅ Minification (JavaScript minified)
- ✅ CSS purging (unused Tailwind classes removed)
- ✅ Performance monitoring
- ✅ Error tracking (Sentry integration)

### Performance Tips

1. Use pagination for large datasets (don't load all at once)
2. Implement search/filter debouncing
3. Use React.memo for expensive components
4. Implement code splitting with React.lazy
5. Monitor bundle size with webpack-bundle-analyzer

## 🔒 Security

- CSRF tokens automatically included
- Proper HTTP methods used
- Input sanitization (Tailwind CSS prevents XSS)
- Authorization checks should be server-side
- API validation on backend

## 🤝 Contributing

When adding new components:
1. Follow existing code style
2. Add JSDoc comments
3. Make components reusable
4. Include accessibility features
5. Test in multiple browsers
6. Update documentation

## 📋 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🐛 Known Issues

None currently. Report issues in the development channel.

## 📝 License

Part of the ProductSchool application.

## 👥 Team

ProductSchool Development Team

## 📞 Support

Contact the development team for questions or issues.

---

**Last Updated**: 2024-01-15  
**Version**: 1.0.0  
**Status**: Production Ready ✅
