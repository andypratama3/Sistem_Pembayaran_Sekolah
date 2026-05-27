/**
 * PreviewModal.jsx — Live document preview within the editor.
 */

import React, { useEffect, useRef, useState } from 'react';
import ApiService from '../services/api';

function PreviewModal({ isOpen, onClose, templateId, canvasLayout, fields }) {
  const [html, setHtml] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const dialogRef = useRef(null);

  const [isExporting, setIsExporting] = useState(false);

  const handleDownloadPdf = async () => {
    if (isExporting) return;
    setIsExporting(true);
    try {
      const url = `/dashboard/api/templates/${templateId}/export-pdf-canvas`;
      const blob = await ApiService.exportPdf(url, {
        canvas_layout: canvasLayout,
        fields: fields,
        filename: `preview_${templateId}.pdf`
      });
      
      const downloadUrl = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = downloadUrl;
      link.download = `preview_${templateId}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(downloadUrl);
    } catch (err) {
      console.error('Export error:', err);
    } finally {
      setIsExporting(false);
    }
  };

  useEffect(() => {
    if (!isOpen) return;

    const fetchPreview = async () => {
      setLoading(true);
      setError(null);
      try {
        // Build preview URL - using the API version that returns JSON
        const url = `/dashboard/api/templates/${templateId}/preview-canvas`;
        const response = await ApiService.preview(url, {
          canvas_layout: canvasLayout,
          fields: fields
        });

        if (response.success) {
          setHtml(response.html);
        } else {
          setError(response.message || 'Gagal memuat preview.');
        }
      } catch (err) {
        setError(err.message || 'Terjadi kesalahan teknis.');
      } finally {
        setLoading(false);
      }
    };

    fetchPreview();

    const handleKey = (e) => {
      if (e.key === 'Escape') onClose();
    };
    window.addEventListener('keydown', handleKey);
    return () => window.removeEventListener('keydown', handleKey);
  }, [isOpen, templateId, canvasLayout, fields, onClose]);

  const [scale, setScale] = useState(1);
  const contentAreaRef = useRef(null);
  const paperRef = useRef(null);

  const numPages = Array.isArray(canvasLayout) ? canvasLayout.length : 1;
  const firstPage = Array.isArray(canvasLayout) ? canvasLayout[0] : canvasLayout;
  const canvasWidth = firstPage?.width || 794;
  const canvasHeight = firstPage?.height || 1123;

  // Auto-scale to fit modal width
  useEffect(() => {
    if (!isOpen || loading || error || !contentAreaRef.current) return;

    const calculateScale = () => {
      const container = contentAreaRef.current;
      const padding = 80; // Total horizontal padding
      const availableWidth = container.clientWidth - padding;
      
      // Fit to width primarily for multi-page flow
      const newScale = Math.min(availableWidth / canvasWidth, 1.1); 
      setScale(newScale);
    };

    calculateScale();
    const observer = new ResizeObserver(calculateScale);
    observer.observe(contentAreaRef.current);
    
    // Scroll to top when preview opens
    contentAreaRef.current.scrollTop = 0;
    
    return () => observer.disconnect();
  }, [isOpen, loading, error, canvasWidth]);

  // Calculate modal max-width based on orientation
  const isLandscape = canvasWidth > canvasHeight;
  const dialogMaxWidth = isLandscape ? 'max-w-[95vw]' : 'max-w-6xl';

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-slate-900/80 backdrop-blur-xl z-[1000] flex items-center justify-center p-2 sm:p-4 md:p-8 animate-in fade-in duration-500" onClick={onClose}>
      <div
        ref={dialogRef}
        className={`bg-white rounded-[2rem] shadow-[0_32px_128px_-16px_rgba(0,0,0,0.3)] w-full ${dialogMaxWidth} h-full max-h-[95vh] flex flex-col overflow-hidden animate-in zoom-in duration-500`}
        onClick={(e) => e.stopPropagation()}
        role="dialog"
        aria-modal="true"
      >
        {/* Header */}
        <div className="px-8 py-6 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white/80 backdrop-blur-md z-10">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
               <i className="feather-eye text-white text-xl" />
            </div>
            <div>
              <h2 className="text-xl font-black text-slate-900 uppercase tracking-tight leading-none mb-1">
                Precision Preview
              </h2>
              <p className="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">
                {numPages} {numPages > 1 ? 'Pages' : 'Page'} • {canvasWidth}x{canvasHeight}px • {Math.round(scale * 100)}% Scale
              </p>
            </div>
          </div>
          
          <div className="flex items-center gap-3">
             <button
               onClick={handleDownloadPdf}
               disabled={isExporting || loading}
               className="h-12 px-6 bg-slate-900 text-white rounded-2xl flex items-center gap-3 text-[11px] font-black uppercase tracking-widest hover:bg-black hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 shadow-xl shadow-slate-200"
               title="Download PDF"
             >
               <i className={isExporting ? 'feather-loader animate-spin' : 'feather-download'} />
               <span>Export Final PDF</span>
             </button>
             
             <div className="w-px h-8 bg-slate-100 mx-2" />
             
             <button
              onClick={() => window.print()}
              className="w-12 h-12 flex items-center justify-center bg-slate-50 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-2xl transition-all"
              title="Print Preview"
            >
              <i className="feather-printer text-lg" />
            </button>
            <button
              onClick={onClose}
              className="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-all"
            >
              <i className="feather-x text-xl" />
            </button>
          </div>
        </div>

        {/* Content Area - SCROLLABLE */}
        <div 
          ref={contentAreaRef} 
          className="flex-1 bg-slate-100/50 overflow-auto relative custom-scrollbar flex flex-col items-center py-16"
        >
          {loading ? (
            <div className="flex flex-col items-center justify-center h-full space-y-8 animate-in fade-in zoom-in duration-500">
               <div className="relative">
                <div className="w-24 h-24 border-[6px] border-indigo-100 border-t-indigo-600 rounded-full animate-spin" />
                <div className="absolute inset-0 flex items-center justify-center">
                  <i className="feather-layers text-indigo-300 text-3xl animate-pulse" />
                </div>
              </div>
              <div className="text-center">
                <h3 className="text-slate-800 font-black text-lg mb-1 uppercase tracking-tight">Orchestrating Layout</h3>
                <p className="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Applying multi-page resolution & scaling</p>
              </div>
            </div>
          ) : error ? (
            <div className="flex flex-col items-center justify-center h-full text-center p-12 bg-white rounded-[3rem] shadow-2xl mx-auto max-w-lg my-auto animate-in zoom-in duration-500">
              <div className="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center mb-8">
                <i className="feather-alert-octagon text-rose-500 text-5xl" />
              </div>
              <h3 className="text-slate-900 font-black text-2xl mb-3 tracking-tight">Sync Disrupted</h3>
              <p className="text-sm text-slate-500 leading-relaxed mb-10 px-6">{error}</p>
              <button 
                onClick={onClose}
                className="w-full py-4 bg-slate-900 text-white rounded-2xl text-[12px] font-black uppercase tracking-[0.2em] hover:bg-black transition-all shadow-2xl shadow-slate-300 active:scale-95"
              >
                Return to Canvas
              </button>
            </div>
          ) : (
            <div 
              style={{ 
                width: `${canvasWidth * scale}px`,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                flexShrink: 0
              }}
            >
              <div 
                ref={paperRef}
                className="transition-all duration-700 ease-out origin-top-left shadow-[0_64px_128px_-24px_rgba(0,0,0,0.18)] bg-white"
                style={{ 
                  width: `${canvasWidth}px`,
                  minHeight: `${canvasHeight}px`,
                  transform: `scale(${scale})`,
                  transformOrigin: 'top left',
                  flexShrink: 0
                }}
              >
                 <div 
                   className="preview-html-content"
                   style={{ position: 'relative', width: '100%', minHeight: '100%' }}
                   dangerouslySetInnerHTML={{ __html: html }} 
                 />
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="px-10 py-6 bg-white border-t border-slate-100 flex items-center justify-between shrink-0">
          <div className="flex items-center gap-6">
            <div className="flex flex-col">
              <span className="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-0.5">Fidelity Status</span>
              <div className="flex items-center gap-2">
                <div className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse" />
                <span className="text-[11px] font-bold text-slate-800 uppercase tracking-tighter">Multi-Page Sync Active</span>
              </div>
            </div>
            <div className="w-px h-8 bg-slate-100" />
            <div className="flex flex-col">
              <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Document Metadata</span>
              <span className="text-[11px] font-bold text-slate-800 uppercase tracking-tighter">{numPages} Pages • {canvasWidth}x{canvasHeight}px</span>
            </div>
          </div>
          <button
            onClick={onClose}
            className="px-10 py-3 bg-slate-50 text-slate-600 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-100 hover:text-slate-900 transition-all active:scale-95"
          >
            Return to Editor
          </button>
        </div>
      </div>
    </div>
  );
}

export default PreviewModal;
