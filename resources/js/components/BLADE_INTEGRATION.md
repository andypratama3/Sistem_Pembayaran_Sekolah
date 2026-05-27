/**
 * BLADE TEMPLATE INTEGRATION GUIDE
 * 
 * This guide shows how to integrate React components into existing Blade templates
 */

// ============================================================================
// OPTION 1: Replace Blade Template with React Component
// ============================================================================

/*
OLD BLADE: /resources/views/dashboard/templates/index.blade.php

<div class="container">
  <h1>Templates</h1>
  <table>
    @foreach($templates as $template)
      <tr>
        <td>{{ $template->name }}</td>
        <td>{{ $template->status }}</td>
      </tr>
    @endforeach
  </table>
</div>

NEW REACT:
*/

// /resources/views/dashboard/templates/index.blade.php
<div id="react-templates-list"></div>

<script type="module">
  import React from 'react';
  import { createRoot } from 'react-dom/client';
  import { TemplatesList } from '@/components/Editor';

  const root = createRoot(document.getElementById('react-templates-list'));
  root.render(
    <TemplatesList
      onEdit={(id) => window.location.href = `/dashboard/templates/${id}/edit`}
      onCreate={() => window.location.href = '/dashboard/templates/create'}
    />
  );
</script>

// ============================================================================
// OPTION 2: Using with Vite + React in Blade
// ============================================================================

/*
In your webpack.mix.js or vite.config.js, make sure React is configured:

export default {
  plugins: [
    react(),
  ],
  // ... other config
}

Then in your Blade:
*/

@viteReactRefresh

<div id="react-app"></div>

@vite('resources/js/app.jsx')

// And in resources/js/app.jsx:
import React from 'react';
import { createRoot } from 'react-dom/client';
import { TemplatesList } from '@/components/Editor';

const templateRoot = document.getElementById('react-templates-list');
if (templateRoot) {
  createRoot(templateRoot).render(
    <TemplatesList
      onEdit={(id) => window.location.href = `/dashboard/templates/${id}/edit`}
      onCreate={() => window.location.href = '/dashboard/templates/create'}
    />
  );
}

// ============================================================================
// OPTION 3: Using with Inertia.js
// ============================================================================

/*
Controller: App/Http/Controllers/TemplateController.php
*/

public function index()
{
    return Inertia::render('Templates/Index', [
        'initialFilters' => request()->query(),
    ]);
}

/*
Component: resources/js/Pages/Templates/Index.jsx
*/

import React from 'react';
import { TemplatesList } from '@/components/Editor';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index() {
  return (
    <AuthenticatedLayout>
      <div className="bg-gray-100 min-h-screen py-12 px-4">
        <TemplatesList
          onEdit={(id) => window.location.href = `/dashboard/templates/${id}/edit`}
          onCreate={() => window.location.href = '/dashboard/templates/create'}
        />
      </div>
    </AuthenticatedLayout>
  );
}

// ============================================================================
// OPTION 4: Multiple React Root Elements
// ============================================================================

/*
If you have multiple React apps on the same page:
*/

// resources/views/dashboard/index.blade.php

<div class="grid grid-cols-2 gap-4">
  <div id="templates-chart"></div>
  <div id="instances-list"></div>
</div>

<script type="module">
  import React from 'react';
  import { createRoot } from 'react-dom/client';
  import { TemplatesList } from '@/components/Editor';
  import { TemplateInstancesList } from '@/components/Editor';

  // Mount first component
  const templatesRoot = createRoot(document.getElementById('templates-chart'));
  templatesRoot.render(
    <TemplatesList
      onEdit={(id) => window.location.href = `/dashboard/templates/${id}/edit`}
      onCreate={() => window.location.href = '/dashboard/templates/create'}
    />
  );

  // Mount second component
  const instancesRoot = createRoot(document.getElementById('instances-list'));
  instancesRoot.render(<TemplateInstancesList />);
</script>

// ============================================================================
// OPTION 5: With Custom Props from Blade
// ============================================================================

/*
Pass data from Blade controller to React component:
*/

// Controller
public function index()
{
    $userRole = auth()->user()->role;
    $departmentId = auth()->user()->department_id;

    return view('templates.index', [
        'initialUserRole' => $userRole,
        'initialDepartmentId' => $departmentId,
    ]);
}

// Blade template
@props(['initialUserRole', 'initialDepartmentId'])

<div id="react-templates"></div>

<script>
  const templateData = @json([
    'userRole' => $initialUserRole,
    'departmentId' => $initialDepartmentId,
  ]);

  window.templateData = templateData;
</script>

<script type="module">
  import React from 'react';
  import { createRoot } from 'react-dom/client';
  import { TemplatesList } from '@/components/Editor';

  const root = createRoot(document.getElementById('react-templates'));
  root.render(
    <TemplatesList
      userRole={window.templateData.userRole}
      departmentId={window.templateData.departmentId}
      onEdit={(id) => window.location.href = `/dashboard/templates/${id}/edit`}
      onCreate={() => window.location.href = '/dashboard/templates/create'}
    />
  );
</script>

// ============================================================================
// OPTION 6: Custom Layout Wrapper
// ============================================================================

/*
Create a reusable wrapper for React apps in Blade:
*/

// resources/views/components/react-app.blade.php

<div id="{{ $id ?? 'react-app' }}" class="{{ $class ?? '' }}"></div>

<script type="module">
  import React from 'react';
  import { createRoot } from 'react-dom/client';

  const Component = @json($component);
  const props = @json($props ?? []);

  const root = createRoot(document.getElementById('{{ $id ?? 'react-app' }}'));
  root.render(
    React.createElement(Component, props)
  );
</script>

// Usage in Blade
<x-react-app
  :component="'TemplatesList'"
  :props="[
    'onEdit' => route('templates.edit'),
    'onCreate' => route('templates.create'),
  ]"
/>

// ============================================================================
// OPTION 7: With CSRF Token in Blade
// ============================================================================

/*
Make sure CSRF token is available in all templates:
*/

// In your main layout
<meta name="csrf-token" content="{{ csrf_token() }}">

// This is automatically picked up by useApi hook
// The hook does: document.querySelector('meta[name="csrf-token"]')?.content

// ============================================================================
// MIGRATION CHECKLIST
// ============================================================================

/*
When migrating from Blade to React:

☐ 1. Identify the Blade template to replace
☐ 2. Analyze the data being passed to the template
☐ 3. Check the API endpoints available
☐ 4. Create/update React component
☐ 5. Create a mount point <div id="react-app"></div>
☐ 6. Import component in a script tag
☐ 7. Test filtering, sorting, pagination
☐ 8. Test bulk actions
☐ 9. Test error handling
☐ 10. Remove old Blade route if applicable
☐ 11. Update navigation/links to new route
☐ 12. Test in different browsers
☐ 13. Verify mobile responsiveness
☐ 14. Check accessibility (ARIA labels, keyboard nav)
*/

// ============================================================================
// API REQUIREMENTS
// ============================================================================

/*
Make sure these API endpoints are available for each component:

For TemplatesList:
- GET /api/templates (with pagination, sorting, filtering)
- GET /api/template-categories
- DELETE /api/templates/{id}
- POST /api/templates/bulk-delete

For TemplateInstancesList:
- GET /api/template-instances (with pagination, sorting, filtering)
- GET /api/templates (for filter dropdown)
- POST /api/template-instances/bulk-action
- POST /api/template-instances/bulk-generate

Each endpoint should return:
{
  "data": [...],
  "total": 100,
  "per_page": 10,
  "current_page": 1,
  "last_page": 10
}
*/

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

/*
The useApi hook automatically includes:
- CSRF token from meta tag
- Content-Type headers
- Accept headers

For authorization, use Laravel's:
- Policy classes (can/cannot in controller)
- Blade directives (@can @cannot) for UI elements
- API should return 403 if unauthorized

If you need to check auth in React:
*/

// Get current user data from Blade
<script>
  window.currentUser = @json(auth()->user());
</script>

// Use in React
const currentUser = window.currentUser;
if (!currentUser || currentUser.role !== 'admin') {
  return <div>You don't have permission</div>;
}

// ============================================================================
// PRODUCTION CONSIDERATIONS
// ============================================================================

/*
1. Code Splitting:
   - Use React.lazy() for lazy-loaded components
   - Implement Suspense boundaries

2. Error Boundaries:
   - Wrap components in error boundaries
   - Show user-friendly error messages

3. Performance:
   - Use React DevTools Profiler
   - Optimize re-renders with useMemo/useCallback
   - Lazy-load large components

4. Security:
   - Validate all API responses
   - Sanitize user inputs
   - Use Content Security Policy headers

5. Monitoring:
   - Log errors to Sentry/similar
   - Track component renders
   - Monitor API performance

6. Testing:
   - Unit tests for components
   - Integration tests for API calls
   - E2E tests for user workflows
*/

// ============================================================================
// COMMON ISSUES & SOLUTIONS
// ============================================================================

/*
Issue: React component not mounting
Solution: Check if React DOM root element exists before creating root

Issue: CSRF token error
Solution: Make sure meta tag exists in layout: <meta name="csrf-token">

Issue: Styles not applying
Solution: Make sure Tailwind CSS is properly configured

Issue: API not returning data
Solution: Check response format, ensure backend returns correct structure

Issue: Navigation not working
Solution: Use window.location.href or route() helper from Laravel

Issue: State not persisting on page reload
Solution: Save to localStorage or use proper API endpoints

Issue: Large tables slow
Solution: Use pagination, virtualization, or backend filtering
*/
