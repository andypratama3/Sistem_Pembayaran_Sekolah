import React from 'react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';
import TemplateEditor from './components/TemplateEditor/TemplateEditor';

window.React = React;
window.createRoot = createRoot;
window.TemplateEditor = TemplateEditor;

const containers = document.querySelectorAll('[data-react-template-editor]');

containers.forEach(container => {
    const templateId = container.dataset.templateId;
    const saveUrl = container.dataset.saveUrl;
    const exportUrl = container.dataset.exportUrl;
    const existingFields = container.dataset.existingFields ? JSON.parse(container.dataset.existingFields) : [];
    const canvasLayout = container.dataset.canvasLayout ? JSON.parse(container.dataset.canvasLayout) : null;
    const csrfToken = container.dataset.csrfToken;
    const templateName = container.dataset.templateName || '';
    const templateDescription = container.dataset.templateDescription || '';
    const categories = container.dataset.categories ? JSON.parse(container.dataset.categories) : [];
    const templateCategoryId = container.dataset.templateCategoryId || '';
    const isPublished = container.dataset.isPublished === '1';

    const root = createRoot(container);
    root.render(
        <TemplateEditor
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
    );
});