/**
 * CanvasSaveService
 * 
 * Service untuk menyimpan canvas layout ke backend.
 * Memastikan semua perubahan canvas disimpan ke database, bukan hanya di frontend.
 */

class CanvasSaveService {
  constructor(templateId, baseUrl = '/dashboard/templates') {
    this.templateId = templateId;
    this.baseUrl = baseUrl;
    this.autoSaveInterval = null;
    this.autoSaveDelay = 5000; // 5 seconds
    this.isSaving = false;
    this.lastSavedData = null;
  }

  /**
   * Save canvas layout to backend
   * @param {Object} canvasLayout - Canvas layout data
   * @returns {Promise}
   */
  async saveCanvasLayout(canvasLayout) {
    if (!canvasLayout) {
      console.error('Canvas layout is required');
      return { success: false, message: 'Canvas layout is required' };
    }

    if (this.isSaving) {
      console.warn('Save already in progress');
      return { success: false, message: 'Save already in progress' };
    }

    this.isSaving = true;

    try {
      const response = await fetch(
        `${this.baseUrl}/${this.templateId}/canvas/save`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken(),
          },
          body: JSON.stringify({
            canvas_layout: canvasLayout,
          }),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        console.error('Save failed:', data);
        return { success: false, message: data.message || 'Save failed' };
      }

      this.lastSavedData = canvasLayout;
      return { success: true, message: data.message };
    } catch (error) {
      console.error('Error saving canvas:', error);
      return { success: false, message: error.message };
    } finally {
      this.isSaving = false;
    }
  }

  /**
   * Get canvas layout from backend
   * @returns {Promise}
   */
  async getCanvasLayout() {
    try {
      const response = await fetch(
        `${this.baseUrl}/${this.templateId}/canvas/get`,
        {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken(),
          },
        }
      );

      const data = await response.json();

      if (!response.ok) {
        console.error('Get failed:', data);
        return { success: false, message: data.message || 'Get failed' };
      }

      return { success: true, canvas_layout: data.canvas_layout };
    } catch (error) {
      console.error('Error getting canvas:', error);
      return { success: false, message: error.message };
    }
  }

  /**
   * Auto-save canvas layout
   * @param {Object} canvasLayout - Canvas layout data
   * @returns {Promise}
   */
  async autoSaveCanvasLayout(canvasLayout) {
    if (!canvasLayout) {
      return { success: false, message: 'Canvas layout is required' };
    }

    // Check if data has changed
    if (JSON.stringify(this.lastSavedData) === JSON.stringify(canvasLayout)) {
      return { success: true, message: 'No changes to save' };
    }

    try {
      const response = await fetch(
        `${this.baseUrl}/${this.templateId}/canvas/auto-save`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken(),
          },
          body: JSON.stringify({
            canvas_layout: canvasLayout,
          }),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        console.warn('Auto-save failed:', data);
        return { success: false, message: data.message || 'Auto-save failed' };
      }

      this.lastSavedData = canvasLayout;
      return { success: true, message: data.message };
    } catch (error) {
      console.error('Error auto-saving canvas:', error);
      return { success: false, message: error.message };
    }
  }

  /**
   * Start auto-save interval
   * @param {Object} canvasLayout - Canvas layout data
   * @param {number} interval - Interval in milliseconds (default: 5000)
   */
  startAutoSave(canvasLayout, interval = 5000) {
    this.autoSaveDelay = interval;

    if (this.autoSaveInterval) {
      clearInterval(this.autoSaveInterval);
    }

    this.autoSaveInterval = setInterval(() => {
      this.autoSaveCanvasLayout(canvasLayout);
    }, this.autoSaveDelay);

    console.log(`Auto-save started with interval: ${this.autoSaveDelay}ms`);
  }

  /**
   * Stop auto-save interval
   */
  stopAutoSave() {
    if (this.autoSaveInterval) {
      clearInterval(this.autoSaveInterval);
      this.autoSaveInterval = null;
      console.log('Auto-save stopped');
    }
  }

  /**
   * Download canvas as PDF
   * @param {string} filename - Filename for download
   * @returns {Promise}
   */
  async downloadCanvasPdf(filename = 'canvas.pdf') {
    try {
      const response = await fetch(
        `${this.baseUrl}/${this.templateId}/canvas/download-pdf`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken(),
          },
          body: JSON.stringify({
            filename: filename,
          }),
        }
      );

      if (!response.ok) {
        const data = await response.json();
        console.error('PDF download failed:', data);
        return { success: false, message: data.message || 'PDF download failed' };
      }

      // Download the PDF
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      return { success: true, message: 'PDF downloaded successfully' };
    } catch (error) {
      console.error('Error downloading PDF:', error);
      return { success: false, message: error.message };
    }
  }

  /**
   * Download canvas as image
   * @param {string} format - Image format (png, jpg, webp)
   * @param {string} filename - Filename for download
   * @returns {Promise}
   */
  async downloadCanvasImage(format = 'png', filename = null) {
    if (!filename) {
      filename = `canvas.${format}`;
    }

    try {
      const response = await fetch(
        `${this.baseUrl}/${this.templateId}/canvas/download-image`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken(),
          },
          body: JSON.stringify({
            format: format,
            filename: filename,
          }),
        }
      );

      if (!response.ok) {
        const data = await response.json();
        console.error('Image download failed:', data);
        return { success: false, message: data.message || 'Image download failed' };
      }

      // Download the image
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      return { success: true, message: 'Image downloaded successfully' };
    } catch (error) {
      console.error('Error downloading image:', error);
      return { success: false, message: error.message };
    }
  }

  /**
   * Get CSRF token from meta tag
   * @returns {string}
   */
  getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
  }

  /**
   * Cleanup
   */
  destroy() {
    this.stopAutoSave();
    this.lastSavedData = null;
  }
}

export default CanvasSaveService;
