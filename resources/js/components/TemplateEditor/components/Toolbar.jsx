/**
 * Toolbar.jsx — Element palette with improved UI
 * Data-driven element creation with section organization
 * 
 * FIXED: Uses shared syncCanvasToStore utility to ensure consistent
 * sync behavior with debouncing across all canvas operations
 */

import React, { useMemo, useState, useCallback, memo } from 'react';
import Konva from 'konva';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { ElementFactory } from '../services/elementFactory';
import { syncCanvasToStore } from '../utils/canvasSync';
import { showError } from '../services/toast';
import './Toolbar.css';

const SECTION_LABELS = {
  text: { label: 'Text', icon: 'feather-type' },
  shapes: { label: 'Shapes', icon: 'feather-square' },
  media: { label: 'Media', icon: 'feather-image' },
  data: { label: 'Data', icon: 'feather-database' },
  layout: { label: 'Layout', icon: 'feather-layout' },
};

function Toolbar() {
  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const [expandedSections, setExpandedSections] = useState(
    () => new Set(['text', 'shapes', 'media', 'data', 'layout'])
  );

  // Get element sections
  const sections = useMemo(() => ElementFactory.getSections(), []);

  const toggleSection = useCallback((sectionName) => {
    setExpandedSections((prev) => {
      const next = new Set(prev);
      if (next.has(sectionName)) {
        next.delete(sectionName);
      } else {
        next.add(sectionName);
      }
      return next;
    });
  }, []);

  // Handle element creation
  const handleAddElement = useCallback((type) => {
    // Special handling for image — open file picker first
    if (type === 'image') {
      const input = document.createElement('input');
      input.type = 'file';
      input.accept = 'image/png,image/jpeg,image/gif,image/svg+xml,image/webp';
      input.onchange = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
          showError('Image file must be less than 5MB');
          return;
        }

        const reader = new FileReader();
        reader.onload = (evt) => {
          const dataUrl = evt.target.result;
          const img = new window.Image();
          img.onload = () => {
            const stageData = stageRegistry.get(activePageIndex);
            if (!stageData?.contentLayer) {
              showError('Canvas is not ready');
              return;
            }

            const stage = stageData.stage;
            const scale = stage.scaleX() || 1;
            const stagePos = stage.position();
            const centerX = (stage.width() / 2 - stagePos.x) / scale;
            const centerY = (stage.height() / 2 - stagePos.y) / scale;

            // Scale image to fit within 300px max dimension
            let w = img.width;
            let h = img.height;
            const maxDim = 300;
            if (w > maxDim || h > maxDim) {
              const ratio = Math.min(maxDim / w, maxDim / h);
              w = Math.round(w * ratio);
              h = Math.round(h * ratio);
            }

            const konvaImage = new Konva.Image({
              x: Math.max(50, centerX - w / 2),
              y: Math.max(50, centerY - h / 2),
              image: img,
              width: w,
              height: h,
              draggable: true,
              id: `image_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 7)}`,
              name: file.name || 'Image',
              src: dataUrl,
            });

            stageData.contentLayer.add(konvaImage);
            stageData.contentLayer.batchDraw();
            stageData.transformer.nodes([konvaImage]);

            konvaImage.on('click tap', (ev) => {
              ev.cancelBubble = true;
              stageData.transformer.nodes([konvaImage]);
              useTemplateStore.getState().setSelectedObject(konvaImage);
              stageData.contentLayer.batchDraw();
            });

            konvaImage.on('dragend transformend', () => {
              const currentPageIndex = useTemplateStore.getState().activePageIndex;
              const currentStageData = stageRegistry.get(currentPageIndex);
              if (currentStageData?.contentLayer) {
                syncCanvasToStore(currentStageData.contentLayer, currentPageIndex, {
                  updatePage: useTemplateStore.getState().updatePage,
                  saveState: useTemplateStore.getState().saveState,
                });
              }
            });

            syncCanvasToStore(stageData.contentLayer, activePageIndex, {
              updatePage: useTemplateStore.getState().updatePage,
              saveState: useTemplateStore.getState().saveState,
            });
            useTemplateStore.getState().setSelectedObject(konvaImage);
          };
          img.onerror = () => showError('Failed to load image');
          img.src = dataUrl;
        };
        reader.onerror = () => showError('Failed to read file');
        reader.readAsDataURL(file);
      };
      input.click();
      return;
    }

    const stageData = stageRegistry.get(activePageIndex);
    if (!stageData?.contentLayer) {
      console.error('Canvas not available');
      showError('Canvas is not ready yet. Please wait for the page to load.');
      return;
    }

    try {
      const stage = stageData.stage;
      // Place at center of visible canvas area
      const scale = stage.scaleX() || 1;
      const stagePos = stage.position();
      const centerX = (stage.width() / 2 - stagePos.x) / scale;
      const centerY = (stage.height() / 2 - stagePos.y) / scale;

      const node = ElementFactory.create(type, {
        x: Math.max(50, centerX - 50),
        y: Math.max(50, centerY - 50),
      });

      // Add to layer
      stageData.contentLayer.add(node);
      stageData.contentLayer.batchDraw();

      // Select the new node
      stageData.transformer.nodes([node]);

      // Setup selection events on the new node
      node.on('click tap', (e) => {
        e.cancelBubble = true;
        // Touch ('tap') events don't carry shiftKey on e.evt; guard for it.
        const isShift = Boolean(e?.evt?.shiftKey);
        if (isShift) {
          const nodes = stageData.transformer.nodes().concat([node]);
          stageData.transformer.nodes(nodes);
        } else {
          stageData.transformer.nodes([node]);
        }
        useTemplateStore.getState().setSelectedObject(node);
        stageData.contentLayer.batchDraw();
      });

      node.on('dragend transformend', () => {
        // Read current page index from store to avoid stale closure
        const currentPageIndex = useTemplateStore.getState().activePageIndex;
        const currentStageData = stageRegistry.get(currentPageIndex);
        if (currentStageData?.contentLayer) {
          syncCanvasToStore(currentStageData.contentLayer, currentPageIndex, {
            updatePage: useTemplateStore.getState().updatePage,
            saveState: useTemplateStore.getState().saveState,
          });
        }
      });

      // Use shared sync utility for consistent behavior (no debounce for initial add)
      syncCanvasToStore(stageData.contentLayer, activePageIndex, {
        updatePage: useTemplateStore.getState().updatePage,
        saveState: useTemplateStore.getState().saveState,
      });
      
      useTemplateStore.getState().setSelectedObject(node);
    } catch (error) {
      console.error('Error adding element:', error);
      showError('Failed to add element to canvas');
    }
  }, [activePageIndex]);

  return (
    <div className="toolbar">
      <div className="toolbar__header">
        <h3 className="toolbar__title">Elements</h3>
        <span className="toolbar__hint">Click to add to canvas</span>
      </div>

      <div className="toolbar__sections" role="region" aria-label="Element palette">
        {sections && Object.entries(sections).length > 0 ? (
          Object.entries(sections).map(([sectionName, elements]) => {
            const sectionInfo = SECTION_LABELS[sectionName] || { label: sectionName, icon: 'feather-box' };
            const isExpanded = expandedSections.has(sectionName);
            const panelId = `toolbar-section-${sectionName}`;

            return (
              <div key={sectionName} className="toolbar__section">
                <button
                  type="button"
                  className="toolbar__section-header"
                  onClick={() => toggleSection(sectionName)}
                  aria-expanded={isExpanded}
                  aria-controls={panelId}
                >
                  <div className="toolbar__section-header-left">
                    <i className={sectionInfo.icon} aria-hidden="true" />
                    <span>{sectionInfo.label}</span>
                  </div>
                  <i
                    className={`feather-chevron-${isExpanded ? 'up' : 'down'} toolbar__section-chevron`}
                    aria-hidden="true"
                  />
                </button>

                {isExpanded && (
                  <div id={panelId} className="toolbar__grid" role="group" aria-label={sectionInfo.label}>
                    {elements.map((config) => (
                      <button
                        key={config.type}
                        type="button"
                        className="toolbar__button"
                        onClick={() => handleAddElement(config.type)}
                        title={config.label}
                        aria-label={`Add ${config.label}`}
                      >
                        <div className="icon-box">
                          <i className={`icon ${config.icon}`} aria-hidden="true" />
                        </div>
                        <span className="toolbar__button-label">{config.label}</span>
                      </button>
                    ))}
                  </div>
                )}
              </div>
            );
          })
        ) : (
          <div className="toolbar__empty">
            <i className="feather-inbox" aria-hidden="true" />
            <span>No elements available</span>
          </div>
        )}
      </div>
    </div>
  );
}

export default memo(Toolbar);
