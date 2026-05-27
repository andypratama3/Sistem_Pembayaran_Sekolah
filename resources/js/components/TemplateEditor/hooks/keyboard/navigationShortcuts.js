/**
 * navigationShortcuts.js — Arrow-key nudge for selected node(s).
 *
 * Splitting this out keeps the editing dispatch table focused; arrow handling
 * needs the raw event (for Shift detection on step size) so it gets its own
 * tiny dispatcher.
 */

import { oneOf } from './keyMatchers';

const NUDGE_STEP = 1;
const NUDGE_STEP_SHIFT = 10;

const DIRS = {
  ArrowUp:    [0, -1],
  ArrowDown:  [0,  1],
  ArrowLeft:  [-1, 0],
  ArrowRight: [ 1, 0],
};

export const navigationShortcuts = [
  {
    match: oneOf('ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'),
    requireSelection: true,
    run: ({ e, selectedNodes, contentLayer, syncStore }) => {
      const step = e.shiftKey ? NUDGE_STEP_SHIFT : NUDGE_STEP;
      const [dx, dy] = DIRS[e.key];

      selectedNodes.forEach((node) => {
        node.x(node.x() + dx * step);
        node.y(node.y() + dy * step);
      });

      contentLayer?.batchDraw();
      syncStore(contentLayer);
    },
  },
];
