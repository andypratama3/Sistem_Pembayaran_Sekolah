/**
 * fieldSlice.js — Fields/variables management
 * Handles custom fields and variables
 * 
 * FIXES APPLIED:
 * 1. Added null checks for field operations
 * 2. Added validation for field data
 * 3. Added error handling for mutations
 */

export const fieldSlice = (set, get) => ({
  fields: [],
  clipboard: null,

  setFields: (fields) => {
    // ISSUE #5 FIX: Validate fields array before setting
    if (!Array.isArray(fields)) {
      console.error('setFields: fields must be an array');
      return;
    }
    set({ fields });
  },

  addField: (field) =>
    set((state) => {
      // ISSUE #10 FIX: Added null check
      if (!field) {
        console.error('Field data is required for addField');
        throw new Error('Cannot add: field data is missing');
      }
      
      // ISSUE #5 FIX: Validate fields array
      if (!Array.isArray(state.fields)) {
        console.error('Fields is not an array');
        return state;
      }
      
      return {
        fields: [
          ...state.fields,
          {
            id: `field_${Date.now()}`,
            ...field,
          },
        ],
      };
    }),

  removeField: (fieldId) =>
    set((state) => {
      // ISSUE #10 FIX: Added null check
      if (fieldId === null || fieldId === undefined) {
        console.error('fieldId is required for removeField');
        return state;
      }
      
      // ISSUE #5 FIX: Validate fields array
      if (!Array.isArray(state.fields)) {
        console.error('Fields is not an array');
        return state;
      }
      
      return {
        fields: state.fields.filter((f) => f && f.id !== fieldId),
      };
    }),

  updateField: (fieldId, fieldData) =>
    set((state) => {
      // ISSUE #10 FIX: Added null checks
      if (fieldId === null || fieldId === undefined) {
        console.error('fieldId is required for updateField');
        return state;
      }
      
      if (!fieldData) {
        console.error('fieldData is required for updateField');
        return state;
      }
      
      // ISSUE #5 FIX: Validate fields array
      if (!Array.isArray(state.fields)) {
        console.error('Fields is not an array');
        return state;
      }
      
      const field = state.fields.find((f) => f && f.id === fieldId);
      
      if (!field) {
        console.error('Field not found for update');
        throw new Error('Cannot update: field not found');
      }
      
      return {
        fields: state.fields.map((f) =>
          f && f.id === fieldId ? { ...f, ...fieldData } : f
        ),
      };
    }),

  setClipboard: (clipboard) => {
    // ISSUE #10 FIX: Added null check
    if (clipboard === null || clipboard === undefined) {
      set({ clipboard: null });
    } else {
      set({ clipboard });
    }
  },

  getClipboard: () => {
    const clipboard = get().clipboard;
    // ISSUE #10 FIX: Return null instead of undefined
    return clipboard || null;
  },
});

export default fieldSlice;
