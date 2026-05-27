/**
 * pageSlice.js — Page management state
 * Handles pages array, active page, and page operations
 * 
 * FIXES APPLIED:
 * 1. Added null checks for page operations
 * 2. Added validation for pages array
 * 3. Added error handling for mutations
 * 4. Added bounds checking for array access
 */

export const pageSlice = (set, get) => ({
  pages: [],
  activePageIndex: 0,

  addPage: (pageData) =>
    set((state) => {
      // ISSUE #5 FIX: Validate pages array
      if (!Array.isArray(state.pages)) {
        console.error('Pages is not an array');
        return state;
      }
      
      const objects = Array.isArray(pageData?.objects) ? pageData.objects : [];
      
      if (pageData?.objects && !Array.isArray(pageData.objects)) {
        console.warn('Invalid objects array in addPage, using empty array');
      }
      
      return {
        pages: [
          ...state.pages,
          {
            id: `page_${Date.now()}`,
            width: 794,
            height: 1123,
            objects: [],
            ...pageData,
            objects,
          },
        ],
      };
    }),

  removePage: (index) =>
    set((state) => {
      // ISSUE #10 FIX: Added null check and bounds validation
      if (index === null || index === undefined || index < 0) {
        console.error('Invalid page index for removal');
        return state;
      }
      
      // ISSUE #5 FIX: Validate pages array
      if (!Array.isArray(state.pages)) {
        console.error('Pages is not an array');
        return state;
      }
      
      if (state.pages.length <= 1) return state;
      const newPages = state.pages.filter((_, i) => i !== index);
      const newIndex = Math.min(state.activePageIndex, newPages.length - 1);
      return { pages: newPages, activePageIndex: newIndex };
    }),

  duplicatePage: (index) =>
    set((state) => {
      // ISSUE #10 FIX: Added null check and bounds validation
      if (index === null || index === undefined || index < 0) {
        console.error('Invalid page index for duplication');
        return state;
      }
      
      // ISSUE #5 FIX: Validate pages array
      if (!Array.isArray(state.pages)) {
        console.error('Pages is not an array');
        return state;
      }
      
      const page = state.pages[index];
      
      if (!page) {
        console.error('Page not found for duplication');
        throw new Error('Cannot duplicate: page not found');
      }
      
      if (!page.objects || !Array.isArray(page.objects)) {
        console.warn('Page has no objects, duplicating empty page');
      }
      
      const duplicate = {
        ...page,
        id: `page_${Date.now()}`,
        objects: Array.isArray(page.objects) ? [...page.objects] : [],
      };
      
      const newPages = [...state.pages];
      newPages.splice(index + 1, 0, duplicate);
      return { pages: newPages };
    }),

  setActivePageIndex: (index) => {
    // ISSUE #10 FIX: Added null check
    if (index === null || index === undefined) {
      console.error('Invalid page index');
      return;
    }
    set({ activePageIndex: index });
  },

  updatePage: (index, pageData) =>
    set((state) => {
      // ISSUE #10 FIX: Added null check and bounds validation
      if (index === null || index === undefined || index < 0) {
        console.error('Invalid page index for update');
        return state;
      }
      
      // ISSUE #5 FIX: Validate pages array
      if (!Array.isArray(state.pages)) {
        console.error('Pages is not an array');
        return state;
      }
      
      const page = state.pages[index];
      
      if (!page) {
        console.error('Page not found for update');
        throw new Error('Cannot update: page not found');
      }
      
      if (pageData.objects && !Array.isArray(pageData.objects)) {
        console.warn('Invalid objects array in page update, skipping');
        const { objects, ...safeData } = pageData;
        return {
          pages: state.pages.map((p, i) =>
            i === index ? { ...p, ...safeData } : p
          ),
        };
      }
      
      return {
        pages: state.pages.map((p, i) =>
          i === index ? { ...p, ...pageData } : p
        ),
      };
    }),

  reorderPage: (fromIndex, toIndex) =>
    set((state) => {
      // ISSUE #10 FIX: Added null checks and bounds validation
      if (
        fromIndex === null || fromIndex === undefined || fromIndex < 0 ||
        toIndex === null || toIndex === undefined || toIndex < 0
      ) {
        console.error('Invalid page indices for reordering');
        return state;
      }
      
      // ISSUE #5 FIX: Validate pages array
      if (!Array.isArray(state.pages)) {
        console.error('Pages is not an array');
        return state;
      }
      
      if (fromIndex >= state.pages.length || toIndex >= state.pages.length) {
        console.error('Page index out of bounds');
        return state;
      }
      
      const newPages = [...state.pages];
      const [moved] = newPages.splice(fromIndex, 1);
      newPages.splice(toIndex, 0, moved);
      return { pages: newPages };
    }),

  setPages: (pages) => {
    // ISSUE #5 FIX: Validate pages array before setting
    if (!Array.isArray(pages)) {
      console.error('setPages: pages must be an array');
      return;
    }
    set({ pages });
  },
});

export default pageSlice;
