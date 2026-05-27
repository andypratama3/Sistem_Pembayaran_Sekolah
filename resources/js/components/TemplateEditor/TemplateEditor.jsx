/**
 * TemplateEditor.jsx — REFACTORED for Konva.js
 * Simplified entry point with store provider
 *
 * Responsibilities:
 * - Provide store context
 * - Lazy-load the heavy editor shell so the initial bundle stays small
 * - Render fallback states for loading and missing required props
 */

import React, { lazy, Suspense } from 'react';
import PropTypes from 'prop-types';
import { TemplateStoreProvider } from './store/useTemplateStore';

// Lazy-load the editor shell so Suspense fallback is meaningful.
const TemplateEditorContent = lazy(() => import('./TemplateEditorContent'));

const EditorSkeleton = () => (
  <div className="editor-skeleton" role="status" aria-live="polite">
    <div className="editor-skeleton__loader">Loading editor...</div>
  </div>
);

const ConfigError = ({ message }) => (
  <div className="editor-skeleton" role="alert">
    <div className="editor-skeleton__loader">
      <strong>Template Editor cannot start: </strong>
      <span>{message}</span>
    </div>
  </div>
);

ConfigError.propTypes = {
  message: PropTypes.string.isRequired,
};

/**
 * Main TemplateEditor component
 * Wraps content with store provider
 */
function TemplateEditor({
  templateId,
  saveUrl,
  exportUrl,
  existingFields = [],
  canvasLayout = null,
  csrfToken,
  templateName = '',
  templateDescription = '',
  categories = [],
  templateCategoryId = '',
  isPublished = false,
}) {
  // Defensive runtime guard — propTypes only warns in dev. Render a clear
  // error UI when required props are missing so the editor never silently
  // mounts in a broken state.
  if (templateId === null || templateId === undefined || templateId === '') {
    return <ConfigError message="templateId is required" />;
  }
  if (typeof saveUrl !== 'string' || saveUrl.trim() === '') {
    return <ConfigError message="saveUrl is required" />;
  }
  if (typeof csrfToken !== 'string' || csrfToken.trim() === '') {
    return <ConfigError message="csrfToken is required" />;
  }

  return (
    <TemplateStoreProvider templateId={templateId}>
      <Suspense fallback={<EditorSkeleton />}>
        <TemplateEditorContent
          templateId={templateId}
          saveUrl={saveUrl}
          exportUrl={exportUrl}
          existingFields={existingFields}
          canvasLayout={canvasLayout}
          csrfToken={csrfToken}
          templateName={templateName}
          templateDescription={templateDescription}
          categories={categories}
          templateCategoryId={templateCategoryId}
          isPublished={isPublished}
        />
      </Suspense>
    </TemplateStoreProvider>
  );
}

TemplateEditor.propTypes = {
  templateId: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
  saveUrl: PropTypes.string.isRequired,
  exportUrl: PropTypes.string,
  existingFields: PropTypes.arrayOf(PropTypes.object),
  canvasLayout: PropTypes.arrayOf(PropTypes.object),
  csrfToken: PropTypes.string.isRequired,
  templateName: PropTypes.string,
  templateDescription: PropTypes.string,
  categories: PropTypes.arrayOf(PropTypes.shape({
    id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    label: PropTypes.string,
  })),
  templateCategoryId: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
};

export default TemplateEditor;
