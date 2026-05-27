/**
 * PageNavigator.jsx — Enhanced page navigation with controls
 * Add page, duplicate page, delete page, reorder
 */

import React, { useCallback, memo } from 'react';
import { useTemplateStore, PAGE_SIZES } from '../store/useTemplateStore';
import './PageNavigator.css';

function PageNavigator() {
  const pages = useTemplateStore((s) => s.pages) || [];
  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const setActivePageIndex = useTemplateStore((s) => s.setActivePageIndex);
  const addPage = useTemplateStore((s) => s.addPage);
  const removePage = useTemplateStore((s) => s.removePage);
  const duplicatePage = useTemplateStore((s) => s.duplicatePage);
  const pageSize = useTemplateStore((s) => s.pageSize);
  const saveState = useTemplateStore((s) => s.saveState);

  const handleAddPage = useCallback(() => {
    const preset = PAGE_SIZES[pageSize] || PAGE_SIZES.a4_portrait;
    addPage({
      width: preset.width,
      height: preset.height,
      objects: [],
    });
    saveState();
    // Auto-navigate to new page using fresh store state to avoid stale closure
    // when the user adds multiple pages in rapid succession.
    const freshPages = useTemplateStore.getState().pages;
    setActivePageIndex(freshPages.length - 1);
  }, [addPage, saveState, setActivePageIndex, pageSize]);

  const handleDuplicatePage = useCallback((index) => {
    duplicatePage(index);
    saveState();
    setActivePageIndex(index + 1);
  }, [duplicatePage, saveState, setActivePageIndex]);

  const handleDeletePage = useCallback((index) => {
    if (pages.length <= 1) return;
    removePage(index);
    saveState();
    // Clamp active page index if we deleted the last page or the active one.
    const freshPages = useTemplateStore.getState().pages;
    const currentActive = useTemplateStore.getState().activePageIndex;
    if (currentActive >= freshPages.length) {
      setActivePageIndex(Math.max(0, freshPages.length - 1));
    }
  }, [pages.length, removePage, saveState, setActivePageIndex]);

  return (
    <div className="page-navigator">
      <div className="page-navigator__controls-left">
        <span className="page-navigator__info">
          {pages.length} {pages.length === 1 ? 'page' : 'pages'}
        </span>
      </div>

      <div className="page-navigator__list">
        {pages.map((page, index) => (
          <button
            key={page.id || index}
            className={`page-navigator__button ${
              index === activePageIndex ? 'page-navigator__button--active' : ''
            }`}
            onClick={() => setActivePageIndex(index)}
            onDoubleClick={() => handleDuplicatePage(index)}
            title={`Page ${index + 1} (double-click to duplicate)`}
          >
            <span className="page-navigator__number">{index + 1}</span>
          </button>
        ))}

        <button
          className="page-navigator__button page-navigator__button--add"
          onClick={handleAddPage}
          title="Add new page"
        >
          <i className="feather-plus" />
        </button>
      </div>

      <div className="page-navigator__controls-right">
        {pages.length > 1 && (
          <button
            className="page-navigator__action-btn page-navigator__action-btn--danger"
            onClick={() => handleDeletePage(activePageIndex)}
            title="Delete current page"
          >
            <i className="feather-trash-2" />
          </button>
        )}
        <button
          className="page-navigator__action-btn"
          onClick={() => handleDuplicatePage(activePageIndex)}
          title="Duplicate current page"
        >
          <i className="feather-copy" />
        </button>
      </div>
    </div>
  );
}

export default memo(PageNavigator);
