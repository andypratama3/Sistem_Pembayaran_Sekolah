/**
 * TemplateEditorContent.jsx — Editor shell.
 *
 * Composition-only: layout + tabs + modals. Auto-save and context-menu
 * logic live in dedicated hooks (`useAutoSave`, `useContextMenu`).
 */

import React, { useState, useEffect, useCallback, useRef } from 'react';
import ApiService from './services/api';
import { showSuccess, showError } from './services/toast';
import Toolbar from './components/Toolbar';
import CanvasPage from './components/CanvasPage';
import PageNavigator from './components/PageNavigator';
import FieldList from './components/FieldList';
import VariableLibrary from './components/VariableLibrary';
import PropertiesPanel from './components/PropertiesPanel';
import LayersPanel from './components/LayersPanel';
import StarterGalleryModal from './components/StarterGalleryModal';
import PreviewModal from './components/PreviewModal';
import StatusBar from './components/StatusBar';
import ShortcutsHelpModal from './components/ShortcutsHelpModal';
import ContextMenu from './components/ContextMenu';
import useKeyboardShortcuts from './hooks/useKeyboardShortcuts';
import useAutoSave from './hooks/useAutoSave';
import useContextMenu from './hooks/useContextMenu';
import { useTemplateStore, PAGE_SIZES } from './store/useTemplateStore';
import './TemplateEditorContent.css';

class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    console.error('Template Editor Error:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="error-boundary">
          <h2>Template Editor Error</h2>
          <p>{this.state.error?.message}</p>
          <button onClick={() => window.location.reload()}>Reload</button>
        </div>
      );
    }

    return this.props.children;
  }
}

function TemplateEditorContent({
  templateId,
  saveUrl,
  exportUrl,
  existingFields = [],
  canvasLayout = null,
  csrfToken,
  templateName: initialName = '',
  templateDescription: initialDescription = '',
  categories = [],
  templateCategoryId: initialCategoryId = '',
  isPublished: initialPublished = false,
}) {
  const [templateName, setTemplateName] = useState(initialName);
  const [templateDescription, setTemplateDescription] = useState(initialDescription);
  const [categoryId, setCategoryId] = useState(initialCategoryId);
  const [published, setPublished] = useState(initialPublished);
  const [showGallery, setShowGallery] = useState(false);
  const [showPreview, setShowPreview] = useState(false);
  const [leftTab, setLeftTab] = useState('design');
  const [rightTab, setRightTab] = useState('properties');
  const [leftSidebarOpen, setLeftSidebarOpen] = useState(true);
  const [rightSidebarOpen, setRightSidebarOpen] = useState(true);

  const { showHelpModal, setShowHelpModal } = useKeyboardShortcuts();

  // Granular Zustand selectors — each subscribes only to one slice of state.
  const pages = useTemplateStore((s) => s.pages);
  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const fields = useTemplateStore((s) => s.fields);
  const isSaving = useTemplateStore((s) => s.isSaving);
  const setPages = useTemplateStore((s) => s.setPages);
  const addPage = useTemplateStore((s) => s.addPage);
  const setFields = useTemplateStore((s) => s.setFields);
  const setIsSaving = useTemplateStore((s) => s.setIsSaving);
  const saveState = useTemplateStore((s) => s.saveState);
  const markSaved = useTemplateStore((s) => s.markSaved);
  const autoSaveEnabled = useTemplateStore((s) => s.autoSaveEnabled);
  const pageSize = useTemplateStore((s) => s.pageSize);
  const selectedObject = useTemplateStore((s) => s.selectedObject);

  // Refs to read latest title without forcing the auto-save effect to re-run.
  const templateNameRef = useRef(templateName);
  const templateDescriptionRef = useRef(templateDescription);
  const categoryIdRef = useRef(categoryId);
  const saveAbortRef = useRef(null);
  
  useEffect(() => { templateNameRef.current = templateName; }, [templateName]);
  useEffect(() => { templateDescriptionRef.current = templateDescription; }, [templateDescription]);
  useEffect(() => { categoryIdRef.current = categoryId; }, [categoryId]);

  // Cleanup abort controller on unmount
  useEffect(() => {
    return () => {
      saveAbortRef.current?.abort();
    };
  }, []);

  // Initial mount — bootstrap CSRF, fields, pages.
  useEffect(() => {
    try {
      if (csrfToken) ApiService.setCsrfToken(csrfToken);
      if (existingFields.length > 0) setFields(existingFields);

      if (canvasLayout && Array.isArray(canvasLayout) && canvasLayout.length > 0) {
        const preset = PAGE_SIZES[pageSize] || PAGE_SIZES.a4_portrait;
        const sanitized = canvasLayout.map((p) => ({
          ...p,
          width: p.width || preset.width,
          height: p.height || preset.height,
        }));
        setPages(sanitized);
      } else {
        const preset = PAGE_SIZES[pageSize] || PAGE_SIZES.a4_portrait;
        addPage({ width: preset.width, height: preset.height, objects: [] });
        saveState();
      }
    } catch (error) {
      console.error('Failed to initialize template editor:', error);
      showError('Failed to initialize template editor');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Auto-save (extracted hook with AbortController).
  useAutoSave({
    saveUrl,
    enabled: autoSaveEnabled && Boolean(saveUrl),
    getName: () => templateNameRef.current,
    getDescription: () => templateDescriptionRef.current,
    getCategoryId: () => categoryIdRef.current,
  });

  // Manual save — uses store getState() to avoid stale closures from outer state.
  const handleSave = useCallback(async () => {
    if (isSaving) return;

    const name = templateNameRef.current.trim();
    if (!name) {
      showError('Template name is required');
      return;
    }

    setIsSaving(true);
    
    // Cancel any previous save request
    saveAbortRef.current?.abort();
    const controller = new AbortController();
    saveAbortRef.current = controller;
    
    try {
      const state = useTemplateStore.getState();
      await ApiService.saveTemplate(saveUrl, {
        name,
        description: templateDescriptionRef.current,
        category_id: categoryIdRef.current || null,
        is_published: published,
        canvas_layout: state.pages,
        fields: state.fields,
      }, { signal: controller.signal });
      
      // Only mark as saved if request wasn't aborted
      if (!controller.signal.aborted) {
        markSaved();
        showSuccess('Template saved successfully');
      }
    } catch (error) {
      // Don't show error if request was aborted (e.g., due to unmount)
      if (error?.name !== 'AbortError') {
        console.error('Save error:', error);
        showError(error?.message || 'Failed to save template');
      }
    } finally {
      // Only clear isSaving if THIS controller is still active
      if (saveAbortRef.current === controller) {
        setIsSaving(false);
      }
    }
  }, [isSaving, saveUrl, setIsSaving, markSaved, published]);

  const handleExport = useCallback(async () => {
    if (!exportUrl) return;
    let downloadUrl = null;
    let link = null;
    const abortController = new AbortController();
    
    try {
      showSuccess('Generating PDF...');
      const blob = await ApiService.exportPdf(exportUrl, {
        template_id: templateId,
        filename: `${templateName.trim() || 'template'}.pdf`
      }, { signal: abortController.signal });
      
      downloadUrl = window.URL.createObjectURL(blob);
      link = document.createElement('a');
      link.href = downloadUrl;
      link.download = `${templateName.trim() || 'template'}.pdf`;
      document.body.appendChild(link);
      link.click();
      showSuccess('PDF exported successfully');
    } catch (error) {
      if (error?.name !== 'AbortError') {
        showError(error?.message || 'Failed to export PDF');
      }
    } finally {
      // Always cleanup
      if (link && document.body.contains(link)) {
        document.body.removeChild(link);
      }
      if (downloadUrl) {
        window.URL.revokeObjectURL(downloadUrl);
      }
    }
  }, [exportUrl, templateId, templateName]);

  // Context menu (extracted hook).
  const { menu: contextMenu, handleContextMenu, close: closeContextMenu } =
    useContextMenu({ activePageIndex, selectedObject });

  // Cleanup context menu on unmount
  useEffect(() => {
    return () => {
      closeContextMenu();
    };
  }, [closeContextMenu]);

  return (
    <ErrorBoundary>
      <div className="template-editor" onContextMenu={handleContextMenu}>
      {/* Header */}
      <div className="template-editor__header">
        <div className="template-editor__title-section">
          <button
            className="template-editor__sidebar-toggle"
            onClick={() => setLeftSidebarOpen((v) => !v)}
            title="Toggle left sidebar"
          >
            <i className="feather-sidebar" />
          </button>
          <input
            type="text"
            className="template-editor__title-input"
            value={templateName}
            onChange={(e) => setTemplateName(e.target.value)}
            placeholder="Template name..."
          />

          {categories.length > 0 && (
            <div className="template-editor__category-select-wrapper">
              <i className="feather-tag template-editor__category-icon" />
              <select
                className="template-editor__category-select"
                value={categoryId}
                onChange={(e) => setCategoryId(e.target.value)}
              >
                <option value="">Select Category</option>
                {categories.map((cat) => (
                  <option key={cat.id} value={cat.id}>
                    {cat.label}
                  </option>
                ))}
              </select>
            </div>
          )}
        </div>

        <div className="template-editor__actions">
          <button className="template-editor__button" onClick={() => setShowPreview(true)} title="Live Preview">
            <i className="feather-eye" />
            <span>Preview</span>
          </button>

          <button className="template-editor__button" onClick={() => setShowGallery(true)} title="Starter Gallery">
            <i className="feather-layout" />
            <span>Gallery</span>
          </button>

          <button
            className={`template-editor__button ${published ? 'template-editor__button--success' : ''}`}
            onClick={() => {
              const newVal = !published;
              setPublished(newVal);
              // Immediately save the status change to backend
              const name = templateNameRef.current?.trim();
              if (!name) return;
              const state = useTemplateStore.getState();
              ApiService.saveTemplate(saveUrl, {
                name,
                description: templateDescriptionRef.current,
                category_id: categoryIdRef.current || null,
                is_published: newVal,
                canvas_layout: state.pages,
                fields: state.fields,
              }).then(() => {
                markSaved();
                showSuccess(newVal ? 'Template dipublish' : 'Template kembali ke draft');
              }).catch((err) => {
                if (err?.name !== 'AbortError') {
                  showError('Gagal mengubah status');
                  setPublished(!newVal); // revert
                }
              });
            }}
            title={published ? 'Status: Published (klik untuk draft)' : 'Status: Draft (klik untuk publish)'}
          >
            <i className={published ? 'feather-check-circle' : 'feather-circle'} />
            <span>{published ? 'Published' : 'Draft'}</span>
          </button>

          <button
            className="template-editor__button template-editor__button--primary"
            onClick={handleSave}
            disabled={isSaving}
          >
            <i className={isSaving ? 'feather-loader animate-spin' : 'feather-save'} />
            <span>{isSaving ? 'Saving...' : 'Save'}</span>
          </button>

          {exportUrl && (
            <button className="template-editor__button" onClick={handleExport}>
              <i className="feather-download" />
              <span>Export</span>
            </button>
          )}

          <button
            className="template-editor__button"
            onClick={() => setShowHelpModal(true)}
            title="Keyboard Shortcuts"
          >
            <i className="feather-command" />
          </button>

          <button
            className="template-editor__sidebar-toggle"
            onClick={() => setRightSidebarOpen((v) => !v)}
            title="Toggle right sidebar"
          >
            <i className="feather-sliders" />
          </button>
        </div>
      </div>

      {/* Main Layout */}
      <div className="template-editor__main">
        {leftSidebarOpen && (
          <div className="template-editor__sidebar template-editor__sidebar--left">
            <div className="template-editor__tabs">
              <button
                className={`template-editor__tab ${leftTab === 'design' ? 'active' : ''}`}
                onClick={() => setLeftTab('design')}
              >
                <i className="feather-grid" />
                <span>Design</span>
              </button>
              <button
                className={`template-editor__tab ${leftTab === 'fields' ? 'active' : ''}`}
                onClick={() => setLeftTab('fields')}
              >
                <i className="feather-database" />
                <span>Fields</span>
              </button>
            </div>

            <div className="template-editor__tab-content custom-scrollbar">
              {leftTab === 'design' && <Toolbar />}
              {leftTab === 'fields' && <FieldList />}
            </div>
          </div>
        )}

        <div className="template-editor__canvas-area">
          <div className="template-editor__canvas-wrapper custom-scrollbar">
            {pages && pages.length > 0 ? (
              <div className="template-editor__pages-container">
                {pages.map((page, index) => (
                  <CanvasPage
                    key={page.id || index}
                    pageIndex={index}
                    isActive={index === activePageIndex}
                  />
                ))}
              </div>
            ) : (
              <div className="template-editor__empty-canvas">
                <div className="template-editor__empty-canvas-content">
                  <i className="feather-file-plus" />
                  <p>No pages yet</p>
                  <button
                    className="template-editor__button template-editor__button--primary"
                    onClick={() => {
                      const preset = PAGE_SIZES[pageSize] || PAGE_SIZES.a4_portrait;
                      addPage({ width: preset.width, height: preset.height, objects: [] });
                    }}
                  >
                    Add Page
                  </button>
                </div>
              </div>
            )}
          </div>

          <PageNavigator />
        </div>

        {rightSidebarOpen && (
          <div className="template-editor__sidebar template-editor__sidebar--right">
            <div className="template-editor__tabs">
              <button
                className={`template-editor__tab ${rightTab === 'properties' ? 'active' : ''}`}
                onClick={() => setRightTab('properties')}
              >
                <i className="feather-sliders" />
                <span>Properties</span>
              </button>
              <button
                className={`template-editor__tab ${rightTab === 'layers' ? 'active' : ''}`}
                onClick={() => setRightTab('layers')}
              >
                <i className="feather-layers" />
                <span>Layers</span>
              </button>
              <button
                className={`template-editor__tab ${rightTab === 'variables' ? 'active' : ''}`}
                onClick={() => setRightTab('variables')}
              >
                <i className="feather-code" />
                <span>Variables</span>
              </button>
            </div>

            <div className="template-editor__tab-content custom-scrollbar">
              {rightTab === 'properties' && <PropertiesPanel />}
              {rightTab === 'layers' && <LayersPanel />}
              {rightTab === 'variables' && <VariableLibrary />}
            </div>
          </div>
        )}
      </div>

      <StatusBar />

      {contextMenu && (
        <ContextMenu
          x={contextMenu.x}
          y={contextMenu.y}
          items={contextMenu.items}
          onClose={closeContextMenu}
        />
      )}

      {showPreview && (
        <PreviewModal
          isOpen
          onClose={() => setShowPreview(false)}
          templateId={templateId}
          canvasLayout={pages}
          fields={fields}
        />
      )}

      {showHelpModal && <ShortcutsHelpModal isOpen onClose={() => setShowHelpModal(false)} />}
      {showGallery && <StarterGalleryModal isOpen onClose={() => setShowGallery(false)} />}
    </div>
    </ErrorBoundary>
  );
}

export default TemplateEditorContent;
