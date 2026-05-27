import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'path';

/**
 * Vitest config — fast unit tests for the JS engines.
 *
 * Scope: pure logic in `resources/js/components/TemplateEditor/engines` and
 * adjacent helpers. Konva-touching code (CanvasTable rendering, hooks) is NOT
 * targeted here — those are exercised by Vite build + manual smoke + the PHP
 * round-trip tests that already cover serialization.
 */
export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'happy-dom',
    globals: true,
    include: ['resources/js/**/*.test.{js,jsx}'],
    exclude: ['node_modules/**', 'vendor/**', 'public/**'],
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
});
