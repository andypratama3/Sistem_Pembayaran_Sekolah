/**
 * historySlice.js — Undo/redo history management
 * Handles history stack and navigation
 * 
 * FIXES APPLIED:
 * 1. Added error handling for JSON parse/stringify
 * 2. Added null checks for history access
 * 3. Added bounds checking
 */

const MAX_HISTORY = 50;

export const historySlice = (set, get) => ({
  history: [],
  historyIndex: -1,

  saveState: () =>
    set((state) => {
      try {
        // ISSUE #8 FIX: Added error handling for JSON stringify
        const snapshot = JSON.stringify(state.pages);
        const newHistory = state.history.slice(0, state.historyIndex + 1);
        newHistory.push(snapshot);

        if (newHistory.length > MAX_HISTORY) {
          newHistory.shift();
        }

        return {
          history: newHistory,
          historyIndex: newHistory.length - 1,
          hasUnsavedChanges: true,
        };
      } catch (error) {
        console.error('Error saving state to history:', error);
        return state;
      }
    }),

  undo: () => {
    const { history, historyIndex } = get();
    
    // ISSUE #10 FIX: Added null checks and bounds validation
    if (!Array.isArray(history) || historyIndex === null || historyIndex === undefined) {
      console.warn('Cannot undo: invalid history state');
      return;
    }
    
    if (historyIndex <= 0) return;

    try {
      // ISSUE #8 FIX: Added error handling for JSON parse
      const prevState = JSON.parse(history[historyIndex - 1]);
      set({
        pages: prevState,
        historyIndex: historyIndex - 1,
      });
    } catch (error) {
      console.error('Error parsing history state for undo:', error);
    }
  },

  redo: () => {
    const { history, historyIndex } = get();
    
    // ISSUE #10 FIX: Added null checks and bounds validation
    if (!Array.isArray(history) || historyIndex === null || historyIndex === undefined) {
      console.warn('Cannot redo: invalid history state');
      return;
    }
    
    if (historyIndex >= history.length - 1) return;

    try {
      // ISSUE #8 FIX: Added error handling for JSON parse
      const nextState = JSON.parse(history[historyIndex + 1]);
      set({
        pages: nextState,
        historyIndex: historyIndex + 1,
      });
    } catch (error) {
      console.error('Error parsing history state for redo:', error);
    }
  },

  clearHistory: () =>
    set({
      history: [],
      historyIndex: -1,
    }),
});

export default historySlice;
