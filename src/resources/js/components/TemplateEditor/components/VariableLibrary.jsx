/**
 * VariableLibrary.jsx — Dynamic variable insertion
 * IMPROVED: Better grouping, search, visual feedback on insert
 */

import React, { useMemo, useState, useCallback, memo } from 'react';
import { useTemplateStore, stageRegistry } from '../store/useTemplateStore';
import { ElementFactory } from '../services/elementFactory';
import { CanvasSerializer } from '../canvas/CanvasSerializer';
import { showSuccess, showError } from '../services/toast';
import './VariableLibrary.css';

const SYSTEM_VARIABLES = [
  { id: 'student_name', name: 'Nama Siswa', description: 'Full student name', category: 'student' },
  { id: 'nisn', name: 'NISN', description: 'Student NISN number', category: 'student' },
  { id: 'classroom_name', name: 'Kelas', description: 'Classroom name', category: 'student' },
  { id: 'date', name: 'Tanggal', description: 'Current date', category: 'system' },
  { id: 'time', name: 'Waktu', description: 'Current time', category: 'system' },
  { id: 'page_number', name: 'Hal.', description: 'Current page number', category: 'system' },
  { id: 'total_pages', name: 'Total Hal.', description: 'Total page count', category: 'system' },
  { id: 'school_name', name: 'Nama Sekolah', description: 'School name', category: 'school' },
  { id: 'school_address', name: 'Alamat Sekolah', description: 'School address', category: 'school' },
  { id: 'tahun_ajaran', name: 'Tahun Ajaran', description: 'Academic year', category: 'school' },
  { id: 'nama_kepsek', name: 'Kepala Sekolah', description: 'Principal name', category: 'school' },
  { id: 'nip_kepsek', name: 'NIP Kepsek', description: 'Principal NIP', category: 'school' },
];

const CATEGORY_LABELS = {
  student: { label: 'Student', icon: 'feather-user' },
  school: { label: 'School', icon: 'feather-home' },
  system: { label: 'System', icon: 'feather-settings' },
  custom: { label: 'Custom Fields', icon: 'feather-tag' },
};

function VariableLibrary() {
  const [search, setSearch] = useState('');

  const activePageIndex = useTemplateStore((s) => s.activePageIndex);
  const fields = useTemplateStore((s) => s.fields);

  const handleSearchChange = useCallback((e) => {
    setSearch(e.target.value);
  }, []);

  // Combine and group variables
  const groupedVariables = useMemo(() => {
    const customVars = (fields || []).map((f) => ({
      id: f.name || f.id,
      name: f.name,
      description: `Custom ${f.type} field`,
      category: 'custom',
      isCustom: true,
    }));

    const all = [...SYSTEM_VARIABLES, ...customVars];

    // Filter by search
    const filtered = search
      ? all.filter((v) =>
          v.name.toLowerCase().includes(search.toLowerCase()) ||
          v.id.toLowerCase().includes(search.toLowerCase())
        )
      : all;

    // Group by category
    const groups = {};
    filtered.forEach((v) => {
      if (!groups[v.category]) groups[v.category] = [];
      groups[v.category].push(v);
    });

    return groups;
  }, [fields, search]);

  // Insert variable into canvas
  const handleInsertVariable = useCallback((variable) => {
    const stageData = stageRegistry.get(activePageIndex);
    if (!stageData?.contentLayer) {
      showError('Canvas is not ready yet. Please wait for the page to load.');
      return;
    }

    try {
      const stage = stageData.stage;
      const scale = stage.scaleX() || 1;
      const stagePos = stage.position();
      const centerX = (stage.width() / 2 - stagePos.x) / scale;
      const centerY = (stage.height() / 2 - stagePos.y) / scale;

      const node = ElementFactory.create('text', {
        x: Math.max(50, centerX - 50),
        y: Math.max(50, centerY - 10),
      });

      node.text(`{{${variable.id}}}`);
      node.setAttr('variableId', variable.id);

      stageData.contentLayer.add(node);
      stageData.contentLayer.batchDraw();
      stageData.transformer.nodes([node]);

      // Setup events — guard e.evt for tap/touch events
      node.on('click tap', (e) => {
        if (e) e.cancelBubble = true;
        stageData.transformer.nodes([node]);
        useTemplateStore.getState().setSelectedObject(node);
        stageData.contentLayer.batchDraw();
      });

      node.on('dragend transformend', () => {
        const currentPageIndex = useTemplateStore.getState().activePageIndex;
        const serialized = CanvasSerializer.serializeLayer(stageData.contentLayer);
        useTemplateStore.getState().updatePage(currentPageIndex, { objects: serialized });
        useTemplateStore.getState().saveState();
      });

      // Sync
      const serialized = CanvasSerializer.serializeLayer(stageData.contentLayer);
      useTemplateStore.getState().updatePage(activePageIndex, { objects: serialized });
      useTemplateStore.getState().saveState();
      useTemplateStore.getState().setSelectedObject(node);

      showSuccess(`Inserted {{${variable.id}}}`);
    } catch (error) {
      console.error('Error inserting variable:', error);
      showError('Failed to insert variable into canvas');
    }
  }, [activePageIndex]);

  return (
    <div className="variable-library">
      <div className="variable-library__header">
        <h3 className="variable-library__title">Variables</h3>
      </div>

      {/* Search */}
      <div className="variable-library__search">
        <i className="feather-search" aria-hidden="true" />
        <input
          type="text"
          placeholder="Search variables..."
          value={search}
          onChange={handleSearchChange}
          aria-label="Search variables"
        />
      </div>

      <div className="variable-library__list">
        {Object.keys(groupedVariables).length === 0 ? (
          <div className="variable-library__empty">
            <i className="feather-search" aria-hidden="true" />
            <span>No matching variables</span>
          </div>
        ) : (
          Object.entries(groupedVariables).map(([category, vars]) => {
            const catInfo = CATEGORY_LABELS[category] || { label: category, icon: 'feather-tag' };
            return (
              <div key={category} className="variable-library__group">
                <div className="variable-library__group-header">
                  <i className={catInfo.icon} aria-hidden="true" />
                  <span>{catInfo.label}</span>
                  <span className="variable-library__group-count">{vars.length}</span>
                </div>
                <div className="variable-library__group-items" role="group" aria-label={catInfo.label}>
                  {vars.map((variable) => (
                    <button
                      key={`${variable.category}-${variable.id}`}
                      type="button"
                      className="variable-library__item"
                      onClick={() => handleInsertVariable(variable)}
                      title={`Insert {{${variable.id}}} into canvas`}
                      aria-label={`Insert ${variable.name} variable into canvas`}
                    >
                      <div className="variable-library__item-info">
                        <code className="variable-library__item-code">{`{{${variable.id}}}`}</code>
                        <span className="variable-library__item-desc">{variable.description}</span>
                      </div>
                      <i className="feather-plus-circle variable-library__item-add" aria-hidden="true" />
                    </button>
                  ))}
                </div>
              </div>
            );
          })
        )}
      </div>
    </div>
  );
}

export default memo(VariableLibrary);
