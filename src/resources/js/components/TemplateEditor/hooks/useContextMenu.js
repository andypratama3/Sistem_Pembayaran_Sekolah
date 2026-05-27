/**
 * useContextMenu.js — Right-click context menu state + handlers.
 *
 * Builds a context-aware menu based on the current selection and clipboard.
 *
 * Implementation notes:
 *  - The auto-close listener is wired on `mousedown` (not `click`), and ignores
 *    events that originate inside the menu itself, so item handlers can fire
 *    on `click` without being clobbered first.
 *  - `handleContextMenu` ignores the very next `mousedown` after opening so
 *    the right-click that opened the menu doesn't immediately close it.
 */

import { useState, useCallback, useEffect, useRef } from 'react';
import Konva from 'konva';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { CanvasSerializer } from '../canvas/CanvasSerializer';
import { CanvasTable } from '../canvas/CanvasTable';
import { LayerManager } from '../canvas/LayerManager';

const uid = () =>
  `${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 9)}`;

const snapshotForClipboard = (node) => {
  if (!node) return null;
  const isCanvasTable = node.className === 'CanvasTable';
  return {
    node: isCanvasTable ? node.toTableJSON() : node.toObject(),
    type: node.className,
    className: node.className,
  };
};

const cloneFromSnapshot = (snapshot) => {
  if (!snapshot?.node) return null;
  try {
    if (snapshot.className === 'CanvasTable') {
      const data = {
        ...snapshot.node,
        id: `tbl_${uid()}`,
        x: (snapshot.node.x ?? 0) + 20,
        y: (snapshot.node.y ?? 0) + 20,
      };
      return CanvasTable.fromJSON(data);
    }
    const descriptor = JSON.parse(JSON.stringify(snapshot.node));
    if (descriptor.attrs) {
      descriptor.attrs.id = `${snapshot.className}_${uid()}`;
      descriptor.attrs.x = (descriptor.attrs.x ?? 0) + 20;
      descriptor.attrs.y = (descriptor.attrs.y ?? 0) + 20;
    }
    return Konva.Node.create(descriptor);
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error('[ContextMenu] Paste failed:', error);
    return null;
  }
};

export function useContextMenu({ activePageIndex, selectedObject }) {
  const [menu, setMenu] = useState(null);
  const justOpenedRef = useRef(false);

  const close = useCallback(() => setMenu(null), []);

  // Auto-close on a mousedown that lands outside the menu.
  // We use mousedown (not click) so menu items receive their click before
  // the close handler fires. The menu element marks itself with .context-menu.
  useEffect(() => {
    const handler = (e) => {
      // Ignore the right-click mousedown that just opened the menu.
      if (justOpenedRef.current) {
        justOpenedRef.current = false;
        return;
      }
      if (e.target && e.target.closest?.('.context-menu')) return;
      setMenu(null);
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  const handleContextMenu = useCallback(
    (e) => {
      if (!e.target.closest('.template-editor__canvas-area')) return;
      e.preventDefault();

      const stageData = stageRegistry.get(activePageIndex);
      if (!stageData) return;
      
      const hasSelection = selectedObject != null;
      const clipboard = useTemplateStore.getState().clipboard;

      const items = [
        {
          label: 'Paste',
          icon: 'feather-clipboard',
          shortcut: 'Ctrl+V',
          disabled: !clipboard,
          onClick: () => {
            const data = stageRegistry.get(activePageIndex);
            const snapshot = useTemplateStore.getState().clipboard;
            if (!data?.contentLayer || !data?.transformer || !snapshot) return;
            const pasted = cloneFromSnapshot(snapshot);
            if (!pasted) return;
            data.contentLayer.add(pasted);
            data.transformer.nodes([pasted]);
            data.contentLayer.batchDraw();
            const serialized = CanvasSerializer.serializeLayer(data.contentLayer);
            useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
            useTemplateStore.getState().saveState();
          },
        },
        ...(hasSelection
          ? [
              {
                label: 'Copy',
                icon: 'feather-copy',
                shortcut: 'Ctrl+C',
                onClick: () => {
                  if (!selectedObject) return;
                  useTemplateStore.getState().setClipboard(snapshotForClipboard(selectedObject));
                },
              },
              {
                label: 'Duplicate',
                icon: 'feather-layers',
                shortcut: 'Ctrl+D',
                onClick: () => {
                  const data = stageRegistry.get(activePageIndex);
                  if (!selectedObject || !data?.contentLayer || !data?.transformer) return;
                  const clone = CanvasSerializer.cloneNode(selectedObject, 20, 20);
                  if (!clone) return;
                  data.contentLayer.add(clone);
                  data.transformer.nodes([clone]);
                  data.contentLayer.batchDraw();
                  const serialized = CanvasSerializer.serializeLayer(data.contentLayer);
                  useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
                  useTemplateStore.getState().saveState();
                },
              },
              {
                label: 'Delete',
                icon: 'feather-trash-2',
                shortcut: 'Del',
                onClick: () => {
                  const currentStageData = stageRegistry.get(activePageIndex);
                  if (!currentStageData?.transformer || !selectedObject) return;
                  currentStageData.transformer.nodes([]);
                  selectedObject.destroy();
                  currentStageData.contentLayer?.batchDraw();
                  const serialized = CanvasSerializer.serializeLayer(currentStageData.contentLayer);
                  useTemplateStore.getState().setSelectedObject(null);
                  useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
                  useTemplateStore.getState().saveState();
                },
              },
              {
                label: 'Bring to Front',
                icon: 'feather-arrow-up',
                onClick: () => {
                  if (!selectedObject) return;
                  LayerManager.moveToFront(selectedObject);
                  const data = stageRegistry.get(activePageIndex);
                  data?.contentLayer?.batchDraw();
                  if (data?.contentLayer) {
                    const serialized = CanvasSerializer.serializeLayer(data.contentLayer);
                    useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
                    useTemplateStore.getState().saveState();
                  }
                },
              },
              {
                label: 'Send to Back',
                icon: 'feather-arrow-down',
                onClick: () => {
                  if (!selectedObject) return;
                  LayerManager.moveToBack(selectedObject);
                  const data = stageRegistry.get(activePageIndex);
                  data?.contentLayer?.batchDraw();
                  if (data?.contentLayer) {
                    const serialized = CanvasSerializer.serializeLayer(data.contentLayer);
                    useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
                    useTemplateStore.getState().saveState();
                  }
                },
              },
              {
                label: selectedObject?.getAttr('locked') ? 'Unlock' : 'Lock',
                icon: 'feather-lock',
                shortcut: 'Ctrl+L',
                onClick: () => {
                  const currentStageData = stageRegistry.get(activePageIndex);
                  if (selectedObject && currentStageData?.transformer) {
                    LayerManager.toggleLock(selectedObject, currentStageData.transformer);
                    if (currentStageData.contentLayer) {
                      const serialized = CanvasSerializer.serializeLayer(currentStageData.contentLayer);
                      useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
                      useTemplateStore.getState().saveState();
                    }
                  }
                },
              },
            ]
          : []),
      ];

      // Suppress the very next mousedown handler so opening the menu doesn't
      // immediately close it.
      justOpenedRef.current = true;
      setMenu({ x: e.clientX, y: e.clientY, items });
    },
    [activePageIndex, selectedObject]
  );

  return { menu, handleContextMenu, close };
}

export default useContextMenu;
